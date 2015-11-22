<?php

namespace app\controllers\api;

use Yii;
use yii\filters\VerbFilter;
use app\models\forms\ContactForm;
use app\models\forms\LoginForm;
use app\models\User;

class PublicController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors["verbs"] = [
            "class" => VerbFilter::className(),
            "actions" => [
                "contact" => ["post", "options"],
                "login" => ["post", "options"],
                "logout" => ["post", "options"],
                "register" => ["post", "options"],
            ],
        ];

        unset($behaviors["jwtAuth"]);
        return $behaviors;
    }

    /**
     * Get JwtAuth component
     * @return \app\components\JwtAuth
     */
    protected function getJwtAuth()
    {
        return Yii::$app->jwtAuth;
    }
    
    /**
     * Contact
     */
    public function actionContact()
    {
        $model = new ContactForm();
        $toEmail = Yii::$app->params["adminEmail"];
        $model->load(Yii::$app->request->post(), "");
        if ($model->contact($toEmail)) {
            return ["success" => true];
        }
        return ["errors" => $model->errors];
    }

    /**
     * Login
     */
    public function actionLogin()
    {
        // notice that we set the second parameter $formName = ""
        $request = Yii::$app->request;
        $loginForm = new LoginForm();
        $loginForm->load($request->post(), "");
        if ($loginForm->validate()) {
            $userAttributes = $loginForm->getUser()->toArray();
            $rememberMe = $request->post("rememberMe", true);
            $jwtCookie = $request->post("jwtCookie", true);
            $authJwtData = $this->generateAuthOutput($userAttributes, $rememberMe, $jwtCookie);
            return ["success" => $authJwtData];
        }
        return ["errors" => $loginForm->errors];
    }

    /**
     * Logout
     */
    public function actionLogout()
    {
        $jwtAuth = $this->getJwtAuth();
        $jwtAuth->removeCookieToken();
        $jwtAuth->removeRefreshCookieToken();
        return ["success" => true];
    }

    /**
     * Register
     */
    public function actionRegister()
    {
        // attempt to register user
        /** @var User $user */
        $request = Yii::$app->request;
        $user = User::register($request->post());

        if (!is_array($user)) {
            $userAttributes = $user->toArray();
            $rememberMe = $request->post("rememberMe", true);
            $jwtCookie = $request->post("jwtCookie", true);
            return ["success" => $this->generateAuthOutput($userAttributes, $rememberMe, $jwtCookie)];
        }
        return ["errors" => $user];
    }

    /**
     * Renew token
     */
    public function actionRenewToken()
    {
        // attempt to renew token using regular token in $_GET, cookie, or header
        $jwtAuth = $this->getJwtAuth();
        $payload = $jwtAuth->getTokenPayload();
        if ($payload) {
            return ["success" => $this->generateAuthOutput($payload->user, $payload->rememberMe, $payload->jwtCookie)];
        }

        // attempt to renew token using refresh token
        return $this->actionUseRefreshToken();
    }

    /**
     * Get refresh token
     * Note: PERMANENT. You should have some way to revoke these access tokens
     */
    public function actionRequestRefreshToken()
    {
        /** @var User $user */
        
        $jwtAuth = $this->getJwtAuth();
        $payload = $jwtAuth->getTokenPayload();
        if (!$payload) {
            return ["error" => Yii::t("app", "Invalid token")];
        }

        // get user based off of id and get access token
        $user = User::findIdentity($payload->sub);

        // generate refresh token
        // note that we use $user->id here, but it can also be the id of your token table
        $id = $user->id;
        $token = $user->accessToken;
        return ["success" => $jwtAuth->generateRefreshToken($id, $token, $payload->jwtCookie)];
    }

    /**
     * Remove refresh token
     */
    public function actionRemoveRefreshToken()
    {
        $this->getJwtAuth()->removeRefreshCookieToken();
        return ["success" => true];
    }

    /**
     * Use refreshToken to refresh the regular token
     * @return array
     */
    public function actionUseRefreshToken()
    {
        /** @var User $user */

        // get token/payload
        $jwtAuth = $this->getJwtAuth();
        $payload = $jwtAuth->getRefreshTokenPayload();
        if (!$payload) {
            return ["error" => Yii::t("app", "Invalid token")];
        }

        // find user and generate auth data
        // note: we don't need rememberMe when using refresh tokens
        $rememberMe = false;
        $user = User::findIdentityByAccessToken($payload->accessToken);
        return ["success" => $this->generateAuthOutput($user->toArray(), $rememberMe, $payload->jwtCookie)];
    }

    /**
     * Generate auth data (for sending back to client)
     * @param array|object $userAttributes
     * @param bool $rememberMe
     * @param bool $jwtCookie
     * @return boolean
     */
    protected function generateAuthOutput($userAttributes, $rememberMe, $jwtCookie)
    {
        $jwtAuth = $this->getJwtAuth();
        $token = $jwtAuth->generateUserToken($userAttributes, $rememberMe, $jwtCookie);
        return [
            "user" => $userAttributes,
            "token" => $token,
        ];
    }
}

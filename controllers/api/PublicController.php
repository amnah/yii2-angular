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
            $useCookie = $request->post("useCookie", true);
            $authJwtData = $this->generateAuthJwtData($userAttributes, $rememberMe, $useCookie);
            return ["success" => $authJwtData];
        }
        return ["errors" => $loginForm->errors];
    }

    /**
     * Logout
     */
    public function actionLogout()
    {
        /** @var \app\components\JwtAuth $jwtAuth */
        $jwtAuth = Yii::$app->jwtAuth;
        return ["success" => $jwtAuth->removeCookieToken() && Yii::$app->user->logout()];
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
            $useCookie = $request->post("useCookie", true);
            return ["success" => $this->generateAuthJwtData($userAttributes, $rememberMe, $useCookie)];
        }
        return ["errors" => $user];
    }

    /**
     * Renew token
     */
    public function actionRenewToken()
    {
        $payload = $this->getPayloadFromAllSources();
        if (!$payload) {
            return ["error" => Yii::t("app", "Invalid token")];
        }

        return ["success" => $this->generateAuthJwtData($payload->user, $payload->rememberMe, $payload->useCookie)];
    }

    /**
     * Get refresh token
     * Note: PERMANENT. You should have some way to revoke these access tokens
     */
    public function actionRequestRefreshToken()
    {
        /** @var User $user */
        /** @var \app\components\JwtAuth $jwtAuth */
        $jwtAuth = Yii::$app->jwtAuth;

        $payload = $this->getPayloadFromAllSources();
        if (!$payload) {
            return ["error" => Yii::t("app", "Invalid token")];
        }

        // get user based off of id and get access token
        $user = User::findIdentity($payload->sub);

        // generate refresh token
        // note that we use $user->id here, but it can also be the id of your token table
        $id = $user->id;
        $token = $user->accessToken;
        return ["success" => $jwtAuth->generateRefreshToken($id, $token, $payload->rememberMe, $payload->useCookie)];
    }

    public function actionRefreshToken()
    {
        /** @var User $user */
        /** @var \app\components\JwtAuth $jwtAuth */
        $jwtAuth = Yii::$app->jwtAuth;

        $token = Yii::$app->request->get("refreshToken");
        $payload = $token ? $jwtAuth->decode($token) : false;
        if (!$payload) {
            return ["error" => Yii::t("app", "Invalid token")];
        }

        $user = User::findIdentityByAccessToken($payload->accessToken);
        return ["success" => $this->generateAuthJwtData($user->toArray(), $payload->rememberMe, $payload->useCookie)];
    }

    /**
     * Get payload from various sources - GET, cookie, or header (in that order)
     * @param string $getParam
     * @return object
     */
    protected function getPayloadFromAllSources($getParam = "token")
    {
        /** @var \app\components\JwtAuth $jwtAuth */
        $jwtAuth = Yii::$app->jwtAuth;

        $token = Yii::$app->request->get($getParam);
        if ($token) {
            $payload = $jwtAuth->decode($token);
        } else {
            $payload = $jwtAuth->getCookieHeaderPayload();
        }
        return $payload;
    }

    /**
     * Generate auth data (for sending back to client)
     * @param array|object $userAttributes
     * @param bool $rememberMe
     * @param bool $useCookie
     * @return boolean
     */
    protected function generateAuthJwtData($userAttributes, $rememberMe, $useCookie)
    {
        /** @var \app\components\JwtAuth $jwtAuth */
        $jwtAuth = Yii::$app->jwtAuth;

        $token = $jwtAuth->generateUserToken($userAttributes, $rememberMe, $useCookie);
        return [
            "user" => $userAttributes,
            "token" => $token,
        ];
    }
}

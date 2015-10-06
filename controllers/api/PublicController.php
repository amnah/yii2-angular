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
        $loginForm = new LoginForm();
        $loginForm->load(Yii::$app->request->post(), "");
        if ($loginForm->validate()) {
            $user = $loginForm->getUser();
            $authJwtData = $this->generateAuthJwtData($user->toArray(), $loginForm->rememberMe);
            return ["success" => $authJwtData];
        }
        return ["errors" => $loginForm->errors];
    }

    /**
     * Logout
     */
    public function actionLogout()
    {
        return ["success" => Yii::$app->user->logout()];
    }

    /**
     * Register
     */
    public function actionRegister()
    {
        // attempt to register user
        /** @var User $user */
        $user = User::register(Yii::$app->request->post());
        $rememberMe = true;
        if (!is_array($user)) {
            return ["success" => $this->generateAuthJwtData($user->toArray(), $rememberMe)];
        }
        return ["errors" => $user];
    }

    /**
     * Refresh jwt token based off of refresh token or regular header payload
     */
    public function actionRefreshJwt()
    {
        /** @var \app\components\JwtAuth $jwtAuth */
        /** @var User $user */
        $jwtAuth = Yii::$app->jwtAuth;

        // decode jwt request token sent in post or get
        $jwtRefresh = Yii::$app->request->get("jwtRefresh");
        $payload    = $jwtRefresh ? $jwtAuth->decode($jwtRefresh) : false;
        if ($payload) {
            $user = User::findIdentityByAccessToken($payload->accessToken);

            // set rememberMe = false so that the token ttl is shorter
            // because the refresh token is permanent
            $rememberMe = false;
            return ["success" => $this->generateAuthJwtData($user->toArray(), $rememberMe)];
        }

        // decode jwt from cookie or header
        $payload = $jwtAuth->getPayload();
        if ($payload) {
            $jwt = $jwtAuth->regenerateToken($payload);
            return ["success" => $this->generateAuthJwtData($payload->user, $payload->rememberMe, $jwt)];
        }

        return ["success" => null];
    }

    /**
     * Generate auth data (user and jwt tokens)
     * @param array|object $userAttributes
     * @param bool $rememberMe
     * @param string $jwt
     * @return boolean
     */
    protected function generateAuthJwtData($userAttributes, $rememberMe = true, $jwt = "")
    {
        /** @var \app\components\JwtAuth $jwtAuth */
        $jwtAuth = Yii::$app->jwtAuth;

        // use $jwt if set, otherwise generate
        if (!$jwt) {
            $jwt = $jwtAuth->generateUserToken($userAttributes, $rememberMe);;
        }

        // add cookie
        $jwtAuth->setCookieJwt($jwt);

        return [
            "user" => $userAttributes,
            "jwt" => $jwt,
        ];
    }

    /**
     * Get jwt refresh token
     * Note: PERMANENT. You should have some way to revoke these access tokens
     */
    public function actionGetRefreshToken()
    {
        /** @var User $user */
        /** @var \app\components\JwtAuth $jwtAuth */
        $jwtAuth = Yii::$app->jwtAuth;

        $payload = $jwtAuth->getHeaderPayload();
        if (!$payload) {
            return ["success" => null];
        }

        // get user based off of id and get access token
        $user = User::findIdentity($payload->sub);
        return ["success" => $jwtAuth->generateUserAccessToken($user->accessToken)];
    }
}

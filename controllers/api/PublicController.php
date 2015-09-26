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
        if ($model->load(Yii::$app->request->post(), "") && $model->contact($toEmail)) {
            return ["success" => true];
        }
        return ["errors" => $model->errors];
    }

    /**
     * User
     */
    public function actionUser()
    {
        /** @var \app\components\JwtAuth $jwtAuth */
        $jwtAuth = Yii::$app->jwtAuth;

        $payload = $jwtAuth->getHeaderPayload();
        if (!$payload) {
            return ["success" => null];
        }
        return ["success" => $payload->user];
    }

    /**
     * Refresh jwt token based off of jwtRefresh
     */
    public function actionJwtRefresh()
    {
        /** @var \app\components\JwtAuth $jwtAuth */
        /** @var User $user */
        $jwtAuth = Yii::$app->jwtAuth;

        // decode jwt
        $failure = ["success" => null];
        $jwtRefresh = Yii::$app->request->post("jwtRefresh");
        $payload = $jwtAuth->decode($jwtRefresh);
        if (!$payload) {
            return $failure;
        }

        // attempt to find user and generate auth data
        $user = User::findIdentityByAccessToken($payload->token);
        if ($user) {
            return ["success" => $user->generateAuthJwtData($payload->rememberMe)];
        }
        return $failure;
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
            $authJwtData = $loginForm->getUser()->generateAuthJwtData($loginForm->rememberMe);
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
            return ["success" => $user->generateAuthJwtData($rememberMe)];
        }
        return ["errors" => $user];
    }
}

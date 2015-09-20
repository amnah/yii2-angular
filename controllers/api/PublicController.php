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
        return ["success" => $payload->data];
    }

    /**
     * Refresh jwt token based off of jwtRefresh
     */
    public function actionJwtRefresh()
    {
        /** @var \app\components\JwtAuth $jwtAuth */
        $jwtAuth = Yii::$app->jwtAuth;

        $jwtExpire = Yii::$app->params["jwtExpire"];
        $jwtRefreshExpire = Yii::$app->params["jwtRefreshExpire"];

        $failure = ["success" => null];
        $jwtRefresh = Yii::$app->request->post("jwtRefresh");
        $payload = $jwtAuth->decode($jwtRefresh);
        if (!$payload) {
            return $failure;
        }

        $user = User::findIdentityByAccessToken($payload->data);
        if ($user) {
            $model = new LoginForm();
            return ["success" => $model->generateJwt($jwtExpire, $jwtRefreshExpire, $user)];
        }
        return $failure;
    }

    /**
     * Login
     */
    public function actionLogin()
    {
        $jwtExpire = Yii::$app->params["jwtExpire"];
        $jwtRefreshExpire = Yii::$app->params["jwtRefreshExpire"];

        // notice that we set the second parameter $formName = ""
        $model = new LoginForm();
        $model->load(Yii::$app->request->post(), "");
        if ($model->validate()) {
            return ["success" => $model->generateJwt($jwtExpire, $jwtRefreshExpire)];
        }
        return ["errors" => $model->errors];
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
        // notice that we set the second parameter $formName = ""
        $user = new User(["scenario" => "register"]);
        $profile = new Profile();
        $user->load(Yii::$app->request->post(), "");
        $profile->load(Yii::$app->request->post(), "");
        if ($user->validate() && $profile->validate()) {
            $user->register(Role::ROLE_USER, Yii::$app->request->userIP, User::STATUS_ACTIVE);
            $profile->register($user->id);
            $this->afterRegister($user, $profile);
            return ["success" => $user];
        }
        return ["errors" => $user->errors];
    }

    /**
     * After Register
     * @param User $user
     * @param Profile $profile
     */
    protected function afterRegister($user, $profile)
    {
        // create user token
    }
}

<?php

namespace app\controllers\api;

use Yii;
use yii\filters\VerbFilter;
use app\models\forms\ContactForm;
use app\models\forms\LoginForm;
use app\models\User;

class PublicController extends BaseController
{
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

    public function actionUser()
    {
        /** @var \app\components\JwtAuth $jwtAuth */
        $jwtAuth = Yii::$app->jwtAuth;

        $payload = $jwtAuth->getPayload();
        if (!$payload) {
            return [ "success" => null ];
        }
        return ["success" => $payload->data];
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
     * Login
     */
    public function actionLogin()
    {
        $exp = 60*10; // 10 min
        $refreshExp = 60*60*24*30; // 1 month

        // notice that we set the second parameter $formName = ""
        $model = new LoginForm();
        $model->load(Yii::$app->request->post(), "");
        list($user, $jwt, $refresh) = $model->login($exp, $refreshExp);
        if ($jwt) {
            return [
                "success" => [ "user" => $user->toArray(), "jwt" => $jwt, "refresh" => $refresh ]
            ];
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

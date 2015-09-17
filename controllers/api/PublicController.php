<?php

namespace app\controllers\api;

use Yii;
use yii\filters\VerbFilter;
use app\models\forms\ContactForm;
use app\models\forms\LoginForm;

class PublicController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors["verbs"] = [
            "class" => VerbFilter::className(),
            "actions" => [
                "contact" => ["post", "options"],
                "register" => ["post", "options"],
                "login" => ["post", "options"],
                "logout" => ["post", "options"],
            ],
        ];

        return $behaviors;
    }

    public function actionUser()
    {
        return ["user" => null];
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
        $loginDuration = 60*60*24*30; // 1 month

        // notice that we set the second parameter $formName = ""
        $model = new LoginForm();
        $model->load(Yii::$app->request->post(), "");
        if ($model->login($loginDuration)) {
            return ["success" => $model->getUser()];
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

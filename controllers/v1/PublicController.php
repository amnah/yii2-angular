<?php

namespace app\controllers\v1;

use Yii;
use app\controllers\BaseApiController;
use app\models\forms\ContactForm;

class PublicController extends BaseApiController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
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
}

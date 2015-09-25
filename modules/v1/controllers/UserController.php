<?php

namespace app\modules\v1\controllers;

use Yii;
use app\models\User;

class UserController extends \app\controllers\api\BaseController
{
    public function actionIndex()
    {
        /** @var \app\components\JwtAuth $jwtAuth */
        $jwtAuth = Yii::$app->jwtAuth;

        $payload = $jwtAuth->getHeaderPayload();
        if (!$payload) {
            return ["success" => null];
        }
        return ["success" => $payload->user];
    }
}

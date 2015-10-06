<?php

namespace app\modules\v1\controllers;

use Yii;

class UserController extends \app\controllers\api\BaseController
{
    public function actionIndex()
    {
        /** @var \app\components\JwtAuth $jwtAuth */
        $jwtAuth = Yii::$app->jwtAuth;

        $payload = $jwtAuth->getPayload();
        if (!$payload) {
            return ["success" => null];
        }
        return ["success" => $payload->user];
    }
}

<?php

namespace app\controllers\v1;

use Yii;
use app\controllers\BaseApiController;

class UserController extends BaseApiController
{
    public function actionIndex()
    {
        /** @var \app\components\JwtAuth $jwtAuth */
        $jwtAuth = Yii::$app->jwtAuth;

        $payload = $jwtAuth->getTokenPayload();
        if (!$payload) {
            return ["success" => null];
        }
        return ["success" => $payload->user];
    }
}

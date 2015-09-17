<?php

namespace app\modules\v1\controllers;

class PublicController extends \app\controllers\api\PublicController
{
    public function actionIndex()
    {
        return ["message" => "hello world - api v1"];
    }
}

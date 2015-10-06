<?php

namespace app\controllers\api;

use Yii;
use yii\filters\Cors;
use yii\rest\Controller;

class BaseController extends Controller
{
    /**
     * @inheritdoc
     *
     * @link https://github.com/yiisoft/yii2/pull/8626/files
     * @link https://github.com/yiisoft/yii2/issues/6254
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // check for CORS preflight options
        if (Yii::$app->request->method == "OPTIONS") {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors["corsFilter"] = [
            "class" => Cors::className(),
            "cors" => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true, // allow cookies
                'Access-Control-Max-Age' => 1800, // 30 minutes
                'Access-Control-Expose-Headers' => [],
            ],
        ];
        $behaviors["jwtAuth"] = Yii::$app->jwtAuth;
        return $behaviors;
    }
}
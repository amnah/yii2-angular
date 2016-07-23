<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;

class BaseApiController extends Controller
{
    /**
     * @var \app\components\JwtAuth
     */
    public $jwtAuth;

    /**
     * @var \yii\web\Response
     */
    public $response;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->jwtAuth = Yii::$app->get("jwtAuth");
        $this->response = Yii::$app->get("response");

        // set json output and use "pretty" output in debug mode
        $this->response->format = 'json';
        $this->response->formatters['json'] = [
            'class' => 'yii\web\JsonResponseFormatter',
            'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // check for CORS preflight OPTIONS. if so, then return false so that it doesn't run
        // the controller action
        // @link https://github.com/yiisoft/yii2/pull/8626/files
        // @link https://github.com/yiisoft/yii2/issues/6254
        if (Yii::$app->request->isOptions) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [

            // cors filter - should be before authentication
            /*
            'corsFilter' => [
                "class" => Cors::className(),
                "cors" => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => true, // allow cookies
                    'Access-Control-Max-Age' => 1800, // 30 minutes
                    'Access-Control-Expose-Headers' => [],
                ],
            ],
            */

            'jwtAuth' => $this->jwtAuth,

            // rate limiter - should be after authentication
            /*
            'rateLimiter' => [
                'class' => RateLimiter::className(),
            ],
            */
        ];
    }
}
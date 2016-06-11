<?php

namespace app\controllers;

use Yii;
use yii\filters\Cors;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\rest\Controller;
use yii\web\Response;

class BaseApiController extends Controller
{
    /**
     * @var \app\components\JwtAuth
     */
    public $jwtAuth;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->jwtAuth) {
            $this->jwtAuth = Yii::$app->jwtAuth;
        }
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
            'jwtAuth' => $this->jwtAuth,
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML, // easier to view if you visit page in browser
                ],
            ],
            'rateLimiter' => [
                'class' => RateLimiter::className(),
            ],
        ];
    }
}
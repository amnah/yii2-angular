<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UnauthorizedHttpException;

class BaseApiController extends Controller
{
    /**
     * @var bool
     */
    protected $checkAuth = true;

    /**
     * @var \yii\web\Request
     */
    protected $request;

    /**
     * @var \yii\web\Response
     */
    protected $response;

    /**
     * @var \yii\web\User
     */
    protected $user;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->request = Yii::$app->get("request");
        $this->response = Yii::$app->get("response");
        $this->user = Yii::$app->get("user");

        // set json output and use "pretty" output in debug mode
        $this->response->format = 'json';
        $this->response->formatters['json'] = [
            'class' => 'yii\web\JsonResponseFormatter',
            'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
        ];

        // check auth
        if ($this->checkAuth && !$this->user->identity) {
            throw new UnauthorizedHttpException;
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

            // cors filter
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

            // rate limiter
            /*
            'rateLimiter' => [
                'class' => RateLimiter::className(),
            ],
            */
        ];
    }
}
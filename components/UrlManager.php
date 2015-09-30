<?php

namespace app\components;

use Yii;
use yii\web\UrlManager as YiiUrlManager;

class UrlManager extends YiiUrlManager
{
    /**
     * @var array Regex routes that should be processed through Yii 2 instead of angular
     */
    public $yiiRoutes = [
        'v\d*\/',       // api calls - v1, v2, etc
        'debug\/*',     // debug module
        'gii\/*',       // gii module
    ];

    /**
     * @var string Default route to fall back on (will be processed by angular)
     */
    public $defaultRoute = "site/index";

    /**
     * @inheritdoc
     */
    public function parseRequest($request)
    {
        /** @var \yii\web\Request $request */
        $pathInfo = $request->getPathInfo();
        $params = $request->getQueryParams();

        // check for empty
        if (!$pathInfo) {
            return [$this->defaultRoute, []];
        }

        // check if we're calling a yii route
        foreach ($this->yiiRoutes as $yiiRoute) {
            if (preg_match("/{$yiiRoute}/i", $pathInfo)) {
                return [$pathInfo, $params];
            }
        }

        // use default route
        return [$this->defaultRoute, []];
    }

}
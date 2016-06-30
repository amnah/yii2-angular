<?php

namespace app\components;

class UrlManager extends \yii\web\UrlManager
{
    /**
     * @var array Routes that should be processed through Yii 2 instead of angular
     *            These must appear at the beginning of the string
     */
    public $yiiRoutes = [
        "v1/",      // api v1
        "debug/",   // debug module
        "gii/",     // gii module
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

        // check if we're calling a route that should be processed by yii (and not angular)
        foreach ($this->yiiRoutes as $yiiRoute) {
            if (strpos($pathInfo, $yiiRoute) === 0) {
                return [$pathInfo, $params];
            }
        }

        // use default route
        return [$this->defaultRoute, []];
    }
}
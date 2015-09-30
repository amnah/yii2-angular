<?php

namespace app\components;

use Yii;
use yii\web\UrlManager as YiiUrlManager;

class UrlManager extends YiiUrlManager
{
    /**
     * @inheritdoc
     */

    public function parseRequest($request)
    {
        $validControllers = ["v1", "debug", "gii"];

        /** @var \yii\web\Request $request */
        $pathInfo = $request->getPathInfo();
        $params = $request->getQueryParams();
        list($controller) = explode("/", $pathInfo);

        // check if we're calling a valid controller
        if (in_array($controller, $validControllers)) {
            return [$pathInfo, $params];
        }

        // otherwise reroute to angular
        return ["site/index", []];
    }

}
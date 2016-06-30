<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\View;
use amnah\yii2\debug\Module as DebugModule;

class SiteController extends Controller
{
    public $layout = false;

    public function actionIndex()
    {
        // get intended content
        $view = $this->render("index");

        // disable debug module from view
        // @link http://stackoverflow.com/a/28903986
        if (Yii::$app->getModule("debug")) {
            $this->view->off(View::EVENT_END_BODY, [DebugModule::getInstance(), 'renderToolbar']);
            $cacheView = $this->render("index");
        } else {
            $cacheView = $view;
        }

        // write cache file
        $filePath = Yii::getAlias("@runtime") . "/viewCache.php";
        @file_put_contents($filePath, $cacheView);

        // return intended content
        return $view;
    }
}

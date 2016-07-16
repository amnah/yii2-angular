<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class SiteController extends Controller
{
    public $layout = false;

    public function actionIndex()
    {
        // use versioned assets for non-dev environments
        $date = null;
        if (!YII_ENV_DEV) {
            $prefix = Yii::getAlias("@webroot") . "/compiled/";
            $dirs = glob("$prefix*", GLOB_ONLYDIR);
            if ($dirs) {
                $date = str_replace($prefix, "", end($dirs));
            }
        }

        return $this->render("index", compact("date"));
    }
}

<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * This command builds a static html file for the main site/index
 */
class BuildController extends Controller
{
    public $layout = false;

    public function actionIndex()
    {
        // get web path and set aliases (need to do this for console commands)
        // we have to use @app/web because console commands don't have access to @webroot by default
        $webPath = Yii::getAlias('@app/web');
        if (Yii::$app->request->isConsoleRequest) {
            Yii::setAlias('@web', $webPath);
            Yii::setAlias('@webroot', $webPath);
        }

        // disable debug module from view
        // @link http://stackoverflow.com/questions/23560278/how-can-i-disable-yii-debug-toolbar-on-a-specific-view/28903986#28903986
        $debugModule = Yii::$app->getModule("debug");
        if ($debugModule) {
            $view = $this->view;
            $view->off($view::EVENT_END_BODY, [$debugModule, 'renderToolbar']);
        }

        // write view file
        $filePath = "$webPath/index.html";
        $html = $this->render("//site/index");
        @file_put_contents($filePath, $html);
        if (Yii::$app->request->isConsoleRequest) {
            $this->stdout("----------------------------------------------\n", Console::FG_YELLOW);
            $this->stdout("Success - [ $filePath ]\n", Console::FG_YELLOW);
            $this->stdout("----------------------------------------------\n", Console::FG_YELLOW);
        }
    }
}

<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\FileHelper;
use yii\web\View;
use amnah\yii2\debug\Module as DebugModule;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BuildController extends Controller
{
    public $layout = false;

    public function actionIndex()
    {
        // get web path
        $webPath = Yii::getAlias('@app/web');
        Yii::setAlias('@web', $webPath);
        Yii::setAlias('@webroot', $webPath);

        // set compiled dir and output dir
        $compiledDir = "$webPath/compiled";
        $outputDir = "$webPath/build";

        // disable debug module from view
        // @link http://stackoverflow.com/a/28903986
        $this->view->off(View::EVENT_END_BODY, [DebugModule::getInstance(), 'renderToolbar']);

        // purge output dir
        $this->purgeDir($outputDir);

        // create index.html
        $indexFile = "index.html";
        $indexContent = $this->render("//site/index", ["mobileAppMode" => true]);
        $this->save("$outputDir/$indexFile", $indexContent);

        // copy compiled and views folders
        $compiledOptions = [
            "filter" => function($file) {
                // skip revision files and non-min files
                $filename = pathinfo($file, PATHINFO_BASENAME);
                $isRevision = strpos($filename, "-") !== false;
                $isCompiledMin = strpos($filename, ".compiled.min.") !== false;
                if ($isRevision || !$isCompiledMin) {
                    return false;
                }
                return true;
            },
        ];
        FileHelper::copyDirectory("$webPath/compiled", "$webPath/build/compiled", $compiledOptions);
        FileHelper::copyDirectory("$webPath/views", "$webPath/build/views");
        //FileHelper::copyDirectory("$webPath/img", "$webPath/build/img");
    }

    /**
     * Delete all files and directories in specified $dir, except for files beginning with "."
     * @param $dir
     * @throws \yii\base\ErrorException
     */
    protected function purgeDir($dir)
    {
        $files = FileHelper::findFiles($dir);
        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_BASENAME);
            $firstChar = substr($filename, 0, 1);

            // remove child dirs completely
            // remove files that don't begin with "."
            // this is to
            if (is_dir($file)) {
                FileHelper::removeDirectory($file);
            } elseif ($firstChar != ".") {
                unlink($file);
            }
        }
    }

    /**
     * Saves the code into the file specified by [[path]].
     * Taken/modified from yii\gii\CodeFile
     * @param string $path
     * @param string $content
     * @return string|boolean the error occurred while saving the code file, or true if no error.
     */
    protected function save($path, $content)
    {
        $newDirMode = 0755;
        $newFileMode = 0644;

        $dir = dirname($path);
        if (!is_dir($dir)) {
            $mask = @umask(0);
            $result = @mkdir($dir, $newDirMode, true);
            @umask($mask);
            if (!$result) {
                return "Unable to create the directory '$dir'.";
            }
        }
        if (@file_put_contents($path, $content) === false) {
            return "Unable to write the file '{$path}'.";
        } else {
            $mask = @umask(0);
            @chmod($path, $newFileMode);
            @umask($mask);
        }

        return true;
    }
}

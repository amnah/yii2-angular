<?php

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../functions.php');

defined('YII_ENV') or define('YII_ENV', env('YII_ENV'));
defined('YII_DEBUG') or define('YII_DEBUG', env('YII_DEBUG'));

require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();

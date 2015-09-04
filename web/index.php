<?php

require(__DIR__ . '/../vendor/autoload.php');
(new josegonzalez\Dotenv\Loader(__DIR__ . '/../.env'))->parse()->putenv();

defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV'));
defined('YII_DEBUG') or define('YII_DEBUG', getenv('YII_DEBUG'));

require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();

<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$config = require_once __DIR__ . '/web.php';

$config['id'] .= '-console';
$config['controllerNamespace'] = 'app\commands';

$config['bootstrap'][] = 'gii';
$config['modules']['gii'] = [
    'class' => 'yii\gii\Module',
];

unset($config['components']['request']);
unset($config['components']['log']['traceLevel']);
unset($config['components']['user']);
unset($config['components']['errorHandler']);

return $config;

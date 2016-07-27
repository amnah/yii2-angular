<?php

$config = require_once __DIR__ . '/web.php';

$config['id'] .= '-console';
$config['controllerNamespace'] = 'app\commands';

/*
$config['controllerMap'] = [
    'fixture' => [ // Fixture generation command line.
        'class' => 'yii\faker\FixtureController',
    ],
];
*/

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

unset($config['components']['request']);
unset($config['components']['log']['traceLevel']);
unset($config['components']['user']);
unset($config['components']['errorHandler']);

return $config;

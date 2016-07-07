<?php

// ------------------------------------------------------------------------
// Main config
// ------------------------------------------------------------------------
$config = [
    'id' => 'yii2angular',
    'name' => 'Yii 2 Angular',
    'basePath' => dirname(__DIR__),
    'timeZone' => 'UTC',
    'language' => 'en-US',
    'params' => require __DIR__ . '/params.php',
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'cookieValidationKey' => env('YII_KEY'),
            'parsers' => [
                'application/json' => 'yii\web\JsonParser', // required for POST input via `php://input`
            ]
        ],
        'jwtAuth' => [
            'class' => 'app\components\JwtAuth',
            'key' => env('YII_KEY'),
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
        ],
        'user' => [
            'class' => 'amnah\yii2\user\components\User',
            'identityClass' => 'app\models\User',
            'enableSession' => false,
            'enableAutoLogin' => false,
            'loginUrl' => null,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => env('MAIL_FILE_TRANSPORT'),
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => env('MAIL_HOST'),
                'port' => env('MAIL_PORT'),
                'username' => env('MAIL_USER'),
                'password' => env('MAIL_PASS'),
                'encryption' => env('MAIL_ENCRYPTION'),
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => env('DB_DSN'),
            'username' => env('DB_USER'),
            'password' => env('DB_PASS'),
            'tablePrefix' => env('DB_PREFIX'),
            'charset' => 'utf8',
            'enableSchemaCache' => YII_ENV_PROD,
        ],
        'urlManager' => [
            'class' => 'app\components\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules'          => [],
        ],
    ],
    'modules' => [
        'user' => [
            'class' => 'amnah\yii2\user\Module',
            'emailViewPath' => '@app/mail/user', // example: @app/mail/user/confirmEmail.php
        ],
    ],
];

// ------------------------------------------------------------------------
// Dev
// ------------------------------------------------------------------------
$debugModule = 'amnah\yii2\debug\Module';
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => $debugModule,
        'allowedIPs' => ['*'],
        'limitToCurrentRequest' =>  false,
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'],
    ];
}

// ------------------------------------------------------------------------
// Prod
// ------------------------------------------------------------------------
if (YII_ENV_PROD) {
    if (isForceDebug()) {
        // enable debug for current ip
        $userIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        $config['bootstrap'][] = 'debug';
        $config['modules']['debug'] = [
            'class' => $debugModule,
            'allowedIPs' => [$userIp],
            'limitToCurrentRequest' =>  false,
        ];
    }
}




return $config;

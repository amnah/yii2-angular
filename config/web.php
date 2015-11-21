<?php

// ------------------------------------------------------------------------
// Main config
// ------------------------------------------------------------------------
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'timeZone' => 'UTC',
    'language' => 'en-US',
    'params' => require __DIR__ . '/params.php',
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'cookieValidationKey' => getenv('YII_KEY'),
            'parsers' => [
                'application/json' => 'yii\web\JsonParser', // required for POST input via `php://input`
            ]
        ],
        'jwtAuth' => [
            'class' => 'app\components\JwtAuth',
            'key' => getenv('YII_KEY'),
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableSession' => false,
            'enableAutoLogin' => false,
            'loginUrl' => null,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => getenv('MAIL_FILE_TRANSPORT'),
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => getenv('MAIL_HOST'),
                'port' => getenv('MAIL_PORT'),
                'username' => getenv('MAIL_USER'),
                'password' => getenv('MAIL_PASS'),
                'encryption' => getenv('MAIL_ENCRYPTION'),
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
            'dsn' => getenv('DB_DSN'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
            'charset' => 'utf8',
        ],
        'urlManager' => [
            'class' => 'app\components\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules'          => [],
        ],
        'assetManager' => [
            'class' => 'app\components\AssetManager',
            'useManifest' => !YII_ENV_DEV,
        ],
        'security' => [
            'passwordHashStrategy' => 'password_hash',
        ],
    ],
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
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

    // force debug module using $_GET param
    // enable this by manually entering the url "http://example.com?qwe"
    $debugPassword = getenv('DEBUG_PASSWORD');
    $cookieName    = '_forceDebug';
    $cookieExpire  = 60*5; // 5 minutes

    // check $_GET and $_COOKIE
    $isGetSet = isset($_GET[$debugPassword]);
    $isCookieSet = (isset($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] === $debugPassword);
    if ($isGetSet || $isCookieSet) {

        // set/refresh cookie
        setcookie($cookieName, $debugPassword, time() + $cookieExpire);

        // enable debug for current ip
        $userIp = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        $config['bootstrap'][] = 'debug';
        $config['modules']['debug'] = [
            'class' => $debugModule,
            'allowedIPs' => [$userIp],
        ];
    }
}




return $config;

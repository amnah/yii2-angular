<?php

$config = require(__DIR__ . '/web.php');
unset($config['components']['mailer']['transport']);
$config['components']['db']['dsn'] .= "_test";
$config['components']['assetManager']['basePath'] = __DIR__ . '/../web/assets';
return $config;
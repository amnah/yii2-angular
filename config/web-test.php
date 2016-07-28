<?php

$config = require(__DIR__ . '/web.php');
unset($config['components']['mailer']['transport']);
$config['components']['db']['dsn'] .= "_test";
return $config;
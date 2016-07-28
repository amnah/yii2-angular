<?php

$config = require(__DIR__ . '/console.php');
unset($config['components']['mailer']['transport']);
$config['components']['db']['dsn'] .= "_test";
return $config;
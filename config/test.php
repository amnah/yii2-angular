<?php

$config = require(__DIR__ . '/web.php');
unset($config['components']['mailer']['transport']);
return $config;
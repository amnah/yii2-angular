<?php

return [

    // yii
    "YII_ENV" => "dev",
    "YII_DEBUG" => true,
    "YII_KEY" => "RANDOM_STRING_HERE", // cookie validation key

    // force debug module using this $_GET param (for prod environment)
    // leave empty to disable
    "DEBUG_PASSWORD" => "qwe",

    // view cache - load the site/index page without going through yii (eg, in production)
    "VIEW_CACHE" => false,
    "VIEW_CACHE_EXPIRE" => 3, // seconds

    // api
    "API_URL" => "/v1/",
    "MOBILE_APP_API_URL" => "",     // api url for mobile apps, eg, "http://domain.com/v1/"
    "JWT_COOKIE" => true,           // store jwt tokens in cookie. if false, it will use local storage instead

    // database
    "DB_DSN" => "mysql:host=localhost;dbname=basic",
    "DB_USER" => "",
    "DB_PASS" => "",
    "DB_PREFIX" => "",

    // mail
    "MAIL_FILE_TRANSPORT" => true,
    "MAIL_ENCRYPTION" => "tls",
    "MAIL_HOST" => "smtp.mandrillapp.com",
    "MAIL_PORT" => "587",
    "MAIL_USER" => "",
    "MAIL_PASS" => "",

    // recaptcha
    // leave empty to disable
    "RECAPTCHA_SITEKEY" => "",
    "RECAPTCHA_SECRET" => "",
];


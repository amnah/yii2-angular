<?php

use yii\helpers\Url;



// set environment
$env = require_once("env.php");
setEnv($env);

/**
 * Set env
 * @param array $env
 * @param bool $overwrite
 */
function setEnv($env, $overwrite = false) {
    foreach ($env as $key => $value) {
        if (!$overwrite && getenv($key) !== false) {
            continue;
        }

        // set bool/null
        if ($value === true) {
            $value = "true";
        } elseif ($value === false) {
            $value = "false";
        } elseif ($value === null) {
            $value = "null";
        }
        putenv("$key=$value");
    }
}

/**
 * Get env
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env($key, $default = null)
{
    // check if $key is not set
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }

    // return bool/null/value
    if ($value == "true") {
        return true;
    } elseif ($value == "false") {
        return false;
    } elseif ($value == "null") {
        return null;
    } else {
        return $value;
    }
}

/**
 * Check if we force enable yii debug module
 * @return bool
 */
function isForceDebug()
{
    // store/return result
    static $result;
    if ($result !== null) {
        return $result;
    }

    // force debug module using $_GET param
    // enable this by manually entering the url "http://example.com?qwe"
    $debugPassword = env('DEBUG_PASSWORD');
    $cookieName    = '_forceDebug';
    $cookieExpire  = 60*5; // 5 minutes

    // check $_GET and $_COOKIE
    $isGetSet = isset($_GET[$debugPassword]);
    $isCookieSet = (isset($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] === $debugPassword);
    if ($debugPassword && ($isGetSet || $isCookieSet)) {
        // set/refresh cookie
        setcookie($cookieName, $debugPassword, time() + $cookieExpire);
        $result = true;
    } else {
        $result = false;
    }
    return $result;
}

/**
 * Get url
 * @param array|string $url
 * @param bool|string $scheme
 * @return string
 */
function url($url = '', $scheme = false)
{
    return Url::to($url, $scheme);
}

/**
 * Get param
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function param($name, $default = null)
{
    return array_key_exists($name, Yii::$app->params) ? Yii::$app->params[$name] : $default;
}

/**
 * Get/set cache
 * @param string $key
 * @param mixed $value
 * @param int $duration
 * @param yii\caching\Dependency $dependency
 * @return mixed
 */
function cache($key, $value = false, $duration = 0, $dependency = null)
{
    if ($value === false) {
        return Yii::$app->cache->get($key);
    }
    return Yii::$app->cache->set($key, $value, $duration, $dependency);
}


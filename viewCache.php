<?php

// skip view cache if it is disabled
if (!env("VIEW_CACHE")) {
    return;
}
// skip view cache if we are forcing debug
if (env("YII_ENV") != "dev" && isForceDebug()) {
    return;
}

// skip if we are going through a yii route
require_once('components/UrlManager.php');
$urlManager = new \app\components\UrlManager();
$currentUrl = ltrim($_SERVER["REQUEST_URI"], '/');
foreach ($urlManager->yiiRoutes as $route) {
    if (strpos($currentUrl, $route) === 0) {
        return;
    }
}

// check if file exists
$filename = __DIR__ . '/runtime/viewCache.php';
if (!file_exists($filename)) {
    return;
}

// check if filemtime is past the cache expire time (in seconds)
$cacheExpire = env("VIEW_CACHE_EXPIRE");
$timeModified = filemtime($filename);
$diff = time() - $timeModified;
if ($diff > $cacheExpire) {
    return;
}

// get contents of view cache file and exit
// (if we get this far, it means we can use the cache)
require($filename);
exit;
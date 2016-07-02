<?php

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $assetManager \app\components\AssetManager */

$appName = "Yii 2 Angular";
$assetManager = Yii::$app->assetManager;
$min = !YII_ENV_DEV ? ".min" : "";  // use min version unless in dev

// set specific configuration for mobile app mode
$mobileAppMode = !empty($mobileAppMode);
$html5Mode = !$mobileAppMode;
$linkPrefix = $html5Mode ? "/" : "#/";
if ($mobileAppMode) {
    $min = ".min";
    if (substr($assetManager->webDir, 0, 1) == "/") {
        $assetManager->webDir = substr($assetManager->webDir, 1);
    }
}
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $appName ?></title>
    <?php $this->head() ?>

    <link rel="stylesheet" type="text/css" href="<?= $assetManager->getFile("vendor.compiled{$min}.css") ?>">
    <link rel="stylesheet" type="text/css" href="<?= $assetManager->getFile("site.compiled{$min}.css") ?>">

    <?php if ($html5Mode): ?>
    <base href="/">
    <?php endif; ?>
</head>
<body ng-app="app" ng-strict-di>
<?php $this->beginBody() ?>

<div class="wrap">
    <nav id="w0" class="navbar-inverse navbar-fixed-top navbar" role="navigation" ng-controller="NavCtrl as vm">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" ng-click="vm.isCollapsed=!vm.isCollapsed">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?= $linkPrefix ?>" ng-click="vm.isCollapsed=true"><?= $appName ?></a>
            </div>
            <div class="collapse navbar-collapse" collapse="vm.isCollapsed">
                <ul id="w1" class="navbar-nav navbar-right ng-cloak nav">
                    <li><a href="<?= $linkPrefix ?>about" ng-click="vm.isCollapsed=true">About</a></li>
                    <li><a href="<?= $linkPrefix ?>contact" ng-click="vm.isCollapsed=true">Contact</a></li>
                    <li><a href="<?= $linkPrefix ?>account" ng-click="vm.isCollapsed=true">Account</a></li>
                    <li><a href="<?= $linkPrefix ?>profile" ng-click="vm.isCollapsed=true">Profile</a></li>
                    <li ng-show="!vm.Auth.isLoggedIn()"><a href="<?= $linkPrefix ?>login" ng-click="vm.isCollapsed=true">Login</a></li>
                    <li ng-show="!vm.Auth.isLoggedIn()"><a href="<?= $linkPrefix ?>login-email" ng-click="vm.isCollapsed=true">Login via Email</a></li>
                    <li ng-show="!vm.Auth.isLoggedIn()"><a href="<?= $linkPrefix ?>register" ng-click="vm.isCollapsed=true">Register</a></li>
                    <li ng-show="vm.Auth.isLoggedIn()">
                        <a ng-click="vm.Auth.logout()">
                            Logout ({{ vm.Auth.getAttribute('email') }})
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div ng-view></div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= $appName ?> <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<script type="text/javascript">
    var AppConfig = {
        apiUrl: '<?= $mobileAppMode ? env("MOBILE_APP_API_URL") : env("API_URL") ?>',
        jwtCookie: <?= (int) env("JWT_COOKIE") ?>,
        jwtIntervalTime: 60*1000*25, // 25 minutes. make sure this is less than JwtAuth::$ttl (30 min by default)
        recaptchaSitekey: '<?= env("RECAPTCHA_SITEKEY") ?>',
        html5Mode: <?= $html5Mode ? 1 : 0 ?>
    };
</script>

<script src="<?= $assetManager->getFile("vendor.compiled{$min}.js") ?>"></script>
<?php if ($mobileAppMode): ?>
    <script src="cordova.js"></script>
<?php endif; ?>
<script src="<?= $assetManager->getFile("app.compiled{$min}.js") ?>"></script>

<?php if (env("RECAPTCHA_SITEKEY")): ?>
    <script src="https://www.google.com/recaptcha/api.js?onload=recaptchaLoaded&render=explicit" async defer></script>
<?php endif; ?>

<?php $this->endBody() ?>
</body>
</html>
<?php

/* @var $this \yii\web\View */
/* @var $content string */
/* @var $assetManager \app\components\AssetManager */

$appName = "Yii 2 Angular";
$assetManager = Yii::$app->assetManager;
$min = !YII_ENV_DEV ? ".min" : "";  // use min version unless in dev

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $appName ?></title>
    <?php $this->head() ?>

    <!-- add the asset files individually or use the compiled one built from gulp -->
    <!--<link rel="stylesheet" type="text/css" href="<?= $assetManager->getFile("vendor.compiled{$min}.css") ?>">-->

    <link rel="stylesheet" type="text/css" href="/vendor/bootstrap/3.3.5/bootstrap<?= $min ?>.css">
    <link rel="stylesheet" type="text/css" href="<?= $assetManager->getFile("site.compiled{$min}.css") ?>">

    <base href="/">
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
                <a class="navbar-brand" href="/" ng-click="vm.isCollapsed=true"><?= $appName ?></a>
            </div>
            <div class="collapse navbar-collapse" collapse="vm.isCollapsed">
                <ul id="w1" class="navbar-nav navbar-right ng-cloak nav">
                    <li><a href="/about" ng-click="vm.isCollapsed=true">About</a></li>
                    <li><a href="/contact" ng-click="vm.isCollapsed=true">Contact</a></li>
                    <li><a href="/profile" ng-click="vm.isCollapsed=true">Profile</a></li>
                    <li ng-show="!vm.User.isLoggedIn()"><a href="/login" ng-click="vm.isCollapsed=true">Login</a></li>
                    <li ng-show="!vm.User.isLoggedIn()"><a href="/register" ng-click="vm.isCollapsed=true">Register</a></li>
                    <li ng-show="vm.User.isLoggedIn()">
                        <a ng-click="vm.User.logout()">
                            Logout ({{ vm.User.getAttribute('email') }})
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <?= $content ?>
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
        apiUrl: '<?= rtrim(getenv("API_URL"), "/") . "/" ?>',
        recaptchaSitekey: '<?= getenv("RECAPTCHA_SITEKEY") ?>',
        useCookie: true, // fallback to local storage if this is false
        tokenRenewInterval: 60*60*1000 // 1 hr
    };
</script>

<!-- add the asset files individually or use the compiled one built from gulp -->
<!--<script src="<?= $assetManager->getFile("vendor.compiled{$min}.js") ?>"></script>-->

<script src="/vendor/angular/1.4.6/angular<?= $min ?>.js"></script>
<script src="/vendor/angular/1.4.6/angular-animate<?= $min ?>.js"></script>
<script src="/vendor/angular/1.4.6/angular-route<?= $min ?>.js"></script>
<script src="/vendor/ngStorage/0.3.9/ngStorage<?= $min ?>.js"></script>
<script src="/vendor/ui-bootstrap/ui-bootstrap-tpls-0.13.4<?= $min ?>.js"></script>
<script src="<?= $assetManager->getFile("app.compiled{$min}.js") ?>"></script>

<?php if (getenv("RECAPTCHA_SITEKEY")): ?>
    <script src="https://www.google.com/recaptcha/api.js?onload=recaptchaLoaded&render=explicit" async defer></script>
<?php endif; ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

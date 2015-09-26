<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Url;

$min = YII_ENV_PROD ? ".min" : "";
$appName = "Yii 2 Angular";

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $appName ?></title>
    <?php $this->head() ?>
    <link rel="stylesheet" type="text/css" href="/vendor/bootstrap/3.3.5/bootstrap<?= $min ?>.css" />
    <link rel="stylesheet" type="text/css" href="/css/site.css" />
</head>
<body ng-app="App">
<?php $this->beginBody() ?>

<div class="wrap">
    <nav id="w0" class="navbar-inverse navbar-fixed-top navbar" role="navigation" ng-controller="NavController">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" ng-click="isCollapsed=!isCollapsed">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/#/" ng-click="isCollapsed=true"><?= $appName ?></a>
            </div>
            <div class="collapse navbar-collapse" collapse="isCollapsed">
                <ul id="w1" class="navbar-nav navbar-right ng-cloak nav">
                    <li><a href="/#/about" ng-click="isCollapsed=true">About</a></li>
                    <li><a href="/#/contact" ng-click="isCollapsed=true">Contact</a></li>
                    <li ng-show="!User.isLoggedIn()"><a href="/#/login" ng-click="isCollapsed=true">Login</a></li>
                    <li ng-show="!User.isLoggedIn()"><a href="/#/register" ng-click="isCollapsed=true">Register</a></li>
                    <li ng-show="User.isLoggedIn()">
                        <a ng-click="logout()">
                            Logout ({{ User.getAttribute('email') }})
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
    var API_URL = '<?= rtrim(getenv("API_URL"), "/") . "/" ?>';
    var RECAPTCHA_SITEKEY= '<?= getenv("RECAPTCHA_SITEKEY") ?>';

    // set jwt ttl to one minute less than jwtExpire
    // note: multiply by 1000 because $interval takes milliseconds
    var JWT_REFRESH_TIME = <?= ((int) Yii::$app->params["jwtExpire"] - 60) * 1000 ?>;
</script>

<script src="/vendor/angular/1.4.6/angular<?= $min ?>.js"></script>
<script src="/vendor/angular/1.4.6/angular-animate<?= $min ?>.js"></script>
<script src="/vendor/angular/1.4.6/angular-route<?= $min ?>.js"></script>
<script src="/vendor/ngStorage/0.3.9/ngStorage<?= $min ?>.js"></script>
<script src="/vendor/ui-bootstrap/ui-bootstrap-tpls-0.13.4<?= $min ?>.js"></script>
<script src="/js/app.js"></script>

<?php if (getenv("RECAPTCHA_SITEKEY")): ?>
    <script src="https://www.google.com/recaptcha/api.js?onload=recaptchaLoaded&render=explicit" async defer></script>
<?php endif; ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

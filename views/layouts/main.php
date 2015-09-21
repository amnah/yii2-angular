<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Url;

$min = YII_ENV_PROD ? ".min" : "";

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Yii::$app->name ?></title>
    <?php $this->head() ?>
    <link rel="stylesheet" type="text/css" href="/vendor/bootstrap/3.3.5/bootstrap<?= $min ?>.css" />
    <link rel="stylesheet" type="text/css" href="/css/site.css" />
</head>
<body ng-app="App">
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'My Company',
        'brandUrl' => '/#',
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right ng-cloak', 'ng-controller' => 'NavController'],
        'items' => [
            ['label' => 'About', 'url' => '/#/about'],
            ['label' => 'Contact', 'url' => '/#/contact'],
            [
                'label' => 'Login',
                'url' => '/#login',
                'options' => ['ng-if' => '!User.isLoggedIn()']
            ],
            [
                'label' => 'Logout ({{ User.getAttribute("username") }})',
                'logout' => '/#logout',
                'options' => ['ng-if' => 'User.isLoggedIn()'],
                'linkOptions' => ['ng-click' => 'logout()'],
            ],
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; My Company <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<script type="text/javascript">
    <?php 
        // set api url 
        $apiUrl = "/v1";  
        if (YII_ENV_PROD) {
            // get absolute url and add "api" subdomain
            $apiUrl = Url::to($apiUrl, true);
            $apiUrl = str_replace("://", "://api.", $apiUrl);
        }
        $apiUrl .= "/";
    ?>
    var API_URL = '<?= $apiUrl ?>';
    var RECAPTCHA_SITEKEY= '<?= getenv("RECAPTCHA_SITEKEY") ?>';

    // set jwt ttl to one minute less than jwtExpire
    // note: multiply by 1000 because $interval takes milliseconds
    var JWT_REFRESH_TIME = <?= ((int) Yii::$app->params["jwtExpire"] - 60) * 1000 ?>;
</script>

<script src="/vendor/angular/1.4.5/angular<?= $min ?>.js"></script>
<script src="/vendor/angular/1.4.5/angular-route<?= $min ?>.js"></script>
<script src="/vendor/angular-jwt/0.0.9/angular-jwt<?= $min ?>.js"></script>
<script src="/js/app.js"></script>

<?php if (getenv("RECAPTCHA_SITEKEY")): ?>
    <script src="https://www.google.com/recaptcha/api.js?onload=recaptchaLoaded&render=explicit" async defer></script>
<?php endif; ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

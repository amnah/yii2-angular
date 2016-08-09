<?php

/* @var $this \yii\web\View */
/* @var $content string */

$appName = Yii::$app->name;

$date = !empty($date) ? $date : null;
$assetPath = $date ? "/compiled-$date" : "/compiled";
$min = $date ? ".min" : "";

?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $appName ?></title>
    <?php $this->head() ?>

    <link rel="stylesheet" type="text/css" href="<?= "$assetPath/vendor{$min}.css" ?>">
    <link rel="stylesheet" type="text/css" href="<?= "$assetPath/app{$min}.css" ?>">
</head>
<body>
<?php $this->beginBody() ?>

<div id="app">
    <div class="wrap">
        <nav class="navbar-inverse navbar-fixed-top navbar">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <router-link to="/" class="navbar-brand"><?= $appName ?></router-link>
                </div>
                <div id="navbar-collapse" class="collapse navbar-collapse">
                    <navbar-links></navbar-links>
                </div>
            </div>
        </nav>

        <div class="container">
            <router-view></router-view>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="pull-left">&copy; <?= $appName ?> <?= date('Y') ?></p>
            <p class="pull-right"><?= Yii::powered() ?></p>
        </div>
    </footer>
</div>

<script type="text/javascript">
    var AppConfig = {
        apiUrl: '<?= env("API_URL") ?>',
        jwtCookie: <?= (int) env("JWT_COOKIE") ?>,
        jwtIntervalTime: 60*1000*28, // 28 minutes. make sure this is less than JwtAuth::$ttl (30 min by default)
        recaptchaSitekey: '<?= env("RECAPTCHA_SITEKEY") ?>',
    };
</script>

<script src="<?= "$assetPath/vendor{$min}.js" ?>"></script>
<script src="<?= "$assetPath/app{$min}.js" ?>"></script>

<?php if (getenv("RECAPTCHA_SITEKEY")): ?>
    <script src="https://www.google.com/recaptcha/api.js?onload=recaptchaLoaded&render=explicit" async defer></script>
<?php endif; ?>

<?php $this->endBody() ?>
</body>
</html>
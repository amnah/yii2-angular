Yii 2 Vue
============================

Yii 2 Vue is a boilerplate for Yii 2 + Vue.js. It is based on
[Yii 2 Basic Application](https://github.com/yiisoft/yii2-app-basic)

DEMO
------------

* [Demo](http://vue.amnahdev.com)

INSTALLATION
------------

* [Download this branch](https://github.com/amnah/yii2-angular/archive/vue.zip)
* Copy *env.php.example* file to *env.php* and modify as needed 
* Install packages and run migration

```
php composer.phar global require "fxp/composer-asset-plugin:~1.1.1" --prefer-dist
php composer.phar update --prefer-dist
npm install
php yii migrate --migrationPath=@vendor/amnah/yii2-user/migrations
```

* Build assets

```
gulp build
gulp watch # for development
```

* Set up apache/nginx vhost and visit site
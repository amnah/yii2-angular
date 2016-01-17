<?php

namespace app\models;

use Yii;
use app\components\ModelTrait;
use amnah\yii2\user\models\User as BaseUser;

class User extends BaseUser
{
    use ModelTrait;

    public function fields()
    {
        return ["id", "email", "username"];
    }
}

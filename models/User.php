<?php

namespace app\models;

use Yii;
use amnah\yii2\user\models\User as BaseUser;

class User extends BaseUser
{
    public function fields()
    {
        return ["id", "email", "username"];
    }
}

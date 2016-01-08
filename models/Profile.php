<?php

namespace app\models;

use Yii;
use amnah\yii2\user\models\Profile as BaseProfile;

class Profile extends BaseProfile
{
    public function fields()
    {
        return ["id", "user_id", "full_name"];
    }
}

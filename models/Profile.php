<?php

namespace app\models;

use Yii;
use app\components\ModelTrait;
use amnah\yii2\user\models\Profile as BaseProfile;

class Profile extends BaseProfile
{
    use ModelTrait;

    public function fields()
    {
        return ["id", "user_id", "full_name"];
    }
}

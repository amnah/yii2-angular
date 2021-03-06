<?php

namespace app\models\forms;

use Yii;
use app\components\ModelTrait;
use amnah\yii2\user\models\forms\LoginForm as BaseLoginForm;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends BaseLoginForm
{
    use ModelTrait;
}

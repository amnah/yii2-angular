<?php

namespace app\models\forms;

use Yii;
use app\components\ModelTrait;
use amnah\yii2\user\models\forms\ForgotForm as BaseForgotForm;

/**
 * Forgot password form
 */
class ForgotForm extends BaseForgotForm
{
    use ModelTrait;
}

<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use himiklab\yii2\recaptcha\ReCaptchaValidator;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;
    public $captcha;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        $rules = [
            // required fields
            [['name', 'email', 'subject', 'body'], 'required'],
            ['email', 'email'],
        ];

        // captcha
        $recaptchaSecret = env("RECAPTCHA_SECRET");
        if ($recaptchaSecret) {
            $rules[] = [
                'captcha', ReCaptchaValidator::className(),
                'secret' => $recaptchaSecret, 'message' => Yii::t('app', 'Invalid captcha'),
                'when' => function($model) { return !$model->hasErrors(); }
            ];
        }

        return $rules;
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'captcha' => 'Captcha',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     * @param  string  $email the target email address
     * @return boolean whether the model passes validation
     */
    public function contact($email)
    {
        if ($this->validate()) {
            Yii::$app->mailer->compose()
                ->setTo($email)
                ->setFrom([$this->email => $this->name])
                ->setSubject($this->subject)
                ->setTextBody($this->body)
                ->send();

            return true;
        }
        return false;
    }
}

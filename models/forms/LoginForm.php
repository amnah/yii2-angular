<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\User;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $email;
    public $password;
    public $rememberMe;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // email and password are both required
            [['email', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Generate jwt tokens for user
     * @param int $jwtExpire jwt expiration
     * @param int $jwtRefreshExpire jwt refresh expiration
     * @param User $user optional user model
     * @return boolean whether the user is logged in successfully
     */
    public function generateJwt($jwtExpire, $jwtRefreshExpire, $user = null)
    {
        /** @var \app\components\JwtAuth $jwtAuth */
        $jwtAuth = Yii::$app->jwtAuth;

        // get user data
        $user = $user ?: $this->getUser();
        if (!$user) {
            return null;
        }

        // generate jwt
        $data = $user->toArray();
        $jwt = $jwtAuth->encode($data, $jwtExpire);
        $jwtRefresh = $jwtAuth->encode($user->accessToken, $jwtRefreshExpire);
        return [
            "user" => $data,
            "jwt" => $jwt,
            "jwtRefresh" => $jwtRefresh,
        ];
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByEmail($this->email);
        }

        return $this->_user;
    }
}

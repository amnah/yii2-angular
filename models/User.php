<?php

namespace app\models;

use Yii;

class User extends \yii\base\Object implements \yii\web\IdentityInterface
{
    public $id;
    public $email;
    public $password;
    public $authKey;
    public $accessToken;

    private static $users = [
        '100' => [
            'id' => '100',
            'email' => 'admin',
            'password' => 'admin',
            'authKey' => 'test100key',
            'accessToken' => '100-token',
        ],
        '101' => [
            'id' => '101',
            'email' => 'demo',
            'password' => 'demo',
            'authKey' => 'test101key',
            'accessToken' => '101-token',
        ],
    ];

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        foreach (self::$users as $user) {
            if (strcasecmp($user['email'], $email) === 0) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }

    /**
     * Export public attributes to array
     */
    public function toArray()
    {
        return [
            "id" => $this->id,
            "email" => $this->email,
        ];
    }

    /**
     * Register user (note: doesn't really create any user)
     * @param array $input
     * @return static|array
     */
    public static function register($input)
    {
        // check for data
        $errors = [];
        $email    = !empty($input["email"])    ? trim($input["email"]) : "";
        $password = !empty($input["password"]) ? $input["password"]    : "";
        if (!$email) {
            $errors["email"] = ["Email is required"];
        } elseif (self::findByEmail($email)) {
            $errors["email"] = ["Email [ $email ] is already taken"];
        }
        if (!$password) {
            $errors["password"] = ["Password is required"];
        } elseif (strlen($password) < 3) {
            $errors["password"] = ["Password must be at least 3 characters"];
        }
        if ($errors) {
            return $errors;
        }

        // create user
        return new static([
            "id" => 102,
            "email" => $email,
            "password" => $password,
            "accessToken" => '102-token',
        ]);
    }
}

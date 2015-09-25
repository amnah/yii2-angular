<?php

namespace app\components;

use Exception;

use Yii;
use yii\base\InvalidConfigException;
use yii\filters\auth\HttpBearerAuth;
use yii\web\IdentityInterface;
use yii\web\Request;
use Firebase\JWT\JWT;

class JwtAuth extends HttpBearerAuth
{
    /**
     * @var string Secret key
     */
    public $key;

    /**
     * @var string Jwt algorithm
     */
    public $algorithm = 'HS256';

    /**
     * @var int Expiration
     */
    public $expire = 604800; // 1 week

    /**
     * @var int Jwt expiration leeway
     * @link https://github.com/firebase/php-jwt#example
     */
    public $leeway = 0; // 30 seconds

    /**
     * @var object Payload data in Authorization Bearer
     */
    private $headerPayload = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->key)) {
            throw new InvalidConfigException(get_class($this) . '::key must be configured with a secret key.');
        }
    }

    /**
     * Encode data into jwt string
     * @param array $data
     * @param null|int $expire seconds from current time
     * @return string
     * @link http://websec.io/2014/08/04/Securing-Requests-with-JWT.html
     */
    public function encode($data, $expire = null)
    {
        // build token data
        $time = time();
        $tokenArray = [
            "iss" => Yii::$app->id,
            "iat" => $time,
            "nbf" => $time,
        ];
        $tokenArray = array_merge($tokenArray, $data);

        // add in expire time if set
        $expire = $expire === null ? $this->expire : $expire;
        if ($expire) {
            $tokenArray["exp"] = $time + $expire;
        }

        return JWT::encode($tokenArray, $this->key, $this->algorithm);
    }

    /**
     * Decode jwt string
     * @param string $jwt
     * @return object
     * @throws Exception
     */
    public function decode($jwt)
    {
        JWT::$leeway = $this->leeway;
        try {
            return JWT::decode($jwt, $this->key, [$this->algorithm]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $payload = $this->getHeaderPayload($request);
        if (!$payload) {
            return null;
        }

        /* @var $class IdentityInterface */
        $class = $user->identityClass;
        return $class::findIdentity($payload->user->id);
    }

    /**
     * Get payload from request headers
     * @param Request $request
     * @return bool|object
     */
    public function getHeaderPayload($request = null)
    {
        if ($this->headerPayload === null) {
            $this->headerPayload = false;
            $request = $request ?: Yii::$app->request;
            $authHeader = $request->getHeaders()->get('Authorization');
            if ($authHeader !== null && preg_match("/^Bearer\\s+(.*?)$/", $authHeader, $matches)) {
                $this->headerPayload = $this->decode($matches[1]);
            }
        }

        return $this->headerPayload;
    }
}
<?php

namespace app\components;

use Exception;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\base\InvalidConfigException;
use Firebase\JWT\JWT;

class JwtAuth extends HttpBearerAuth
{
    /**
     * @var string Secret key
     */
    public $key;

    /**
     * @var string jwt algorithm
     */
    public $algorithm = 'HS256';

    /**
     * @var int jwt expiration (from current time)
     */
    public $expire = 600; // 10 minutes

    /**
     * @var int jwt expiration leeway
     * @link https://github.com/firebase/php-jwt#example
     */
    public $leeway = 30; // 30 seconds

    /**
     * @var mixed payload data
     */
    private $payload = null;

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
     * @param mixed $data
     * @param int $exp
     * @return string
     */
    public function encode($data, $exp = null)
    {
        // http://websec.io/2014/08/04/Securing-Requests-with-JWT.html
        $time = time();
        $tokenArray = [
            "iss" => Yii::$app->id,
            "iat" => $time,
            "nbf" => $time,
            "exp" => $time + $exp ?: ($this->expire),
            "data" => $data,
        ];
        return JWT::encode($tokenArray, $this->key, $this->algorithm);
    }

    /**
     * Decode jwt string
     * @param string $jwt
     * @param bool $verify
     * @return object
     * @throws Exception
     */
    public function decode($jwt, $verify = true)
    {
        // decode only
        if (!$verify) {
            return $this->decodeNoVerify($jwt);
        }

        // decode and verify
        JWT::$leeway = $this->leeway;
        try {
            return JWT::decode($jwt, $this->key, [$this->algorithm]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Decode jwt string without verify signature
     * @param string $jwt
     * @return object
     */
    public function decodeNoVerify($jwt)
    {
        $tks = explode('.', $jwt);
        list($headb64, $bodyb64, $cryptob64) = $tks;
        return JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
    }

    /**
     * Get payload from request headers
     * @param \yii\web\Request $request
     * @return bool|object
     */
    public function getHeaderPayload($request = null)
    {
        if ($this->payload === null) {
            $this->payload = false;
            $request = $request ?: Yii::$app->request;
            $authHeader = $request->getHeaders()->get('Authorization');
            if ($authHeader !== null && preg_match("/^Bearer\\s+(.*?)$/", $authHeader, $matches)) {
                $this->payload = $this->decode($matches[1]);
            }
        }

        return $this->payload;
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
        return $payload->data;
    }
}
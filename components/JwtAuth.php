<?php

namespace app\components;

use Exception;

use Yii;
use yii\base\InvalidConfigException;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Request;
use Firebase\JWT\JWT;
use app\models\User;

class JwtAuth extends HttpBearerAuth
{
    /**
     * @var string Secret key
     */
    public $key;

    /**
     * @var string Jwt algorithm
     */
    public $algorithm = "HS256";

    /**
     * @var int|string Token expiration - integer = seconds, string = strtotime()
     *                 Example: "+5 minutes" = 300
     */
    public $exp = "+5 minutes";

    /**
     * @var int|string Refresh token expiration
     */
    public $expRefresh = "+1 week";

    /**
     * @var int|string Refresh token when user doesn't use "remember me"
     */
    public $expRefreshNoRemember = "+2 hrs";

    /**
     * @var int Jwt expiration leeway (in seconds)
     * @link https://github.com/firebase/php-jwt#example
     */
    public $leeway = 30;

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
            throw new InvalidConfigException(get_class($this) . "::key must be configured with a secret key.");
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
        return User::findIdentity($payload->user->id);
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
            $authHeader = $request->getHeaders()->get("Authorization");
            if ($authHeader !== null && preg_match("/^Bearer\\s+(.*?)$/", $authHeader, $matches)) {
                $this->headerPayload = $this->decode($matches[1]);
            }
        }

        return $this->headerPayload;
    }

    /**
     * Encode data into jwt string
     * @param array $data
     * @param int|string $exp seconds from current time
     * @return string
     * @link http://websec.io/2014/08/04/Securing-Requests-with-JWT.html
     */
    public function encode($data, $exp = null)
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
        $exp = $exp === null ? $this->exp : $exp;
        if ($exp) {
            $tokenArray["exp"] = is_string($exp) ? strtotime($exp) : $time + $exp;
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
     * Get exp time in seconds (convert string)
     * @param int|string $exp
     * @return int
     */
    public function getExpInSeconds($exp = null)
    {
        $exp = $exp === null ? $this->exp : $exp;
        if (is_numeric($exp)) {
            return $exp;
        } elseif (is_string($exp)) {
            return strtotime($exp) - time();
        }
        return 0;
    }

    /**
     * Generate a jwt token for user
     * @param User $user
     * @return string
     */
    public function generateUserToken($user)
    {
        return $this->encode([
            "sub" => $user->getId(),
            "user" => $user->toArray(),
        ]);
    }

    /**
     * Generate a jwt refresh token
     * @param string $token
     * @param bool $rememberMe
     * @return string
     */
    public function generateRefreshToken($token, $rememberMe)
    {
        $exp = $rememberMe ? $this->expRefresh : $this->expRefreshNoRemember;
        return $this->encode([
            "token" => $token,
            "rememberMe" => (int) $rememberMe,
        ], $exp);
    }
}
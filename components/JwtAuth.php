<?php

namespace app\components;

use Exception;

use Yii;
use yii\base\InvalidConfigException;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Cookie;
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
     * @var int|string Default token expiration. Integer = seconds, string = strtotime()
     *                 Example: 300 = "+5 minutes"
     */
    public $ttl = "+2 hours";

    /**
     * @var int|string Token expiration when user sets "remember me"
     * @link http://stackoverflow.com/a/26834685
     */
    public $ttlRememberMe = "+1 week";

    /**
     * @var int Jwt expiration leeway (in seconds)
     * @link https://github.com/firebase/php-jwt#example
     */
    public $leeway = 60;

    /**
     * @var string Name for cookie to store jwt data in
     */
    public $cookieName = "jwt";

    /**
     * @var object Payload from cookie or header auth
     */
    protected $payload;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->key)) {
            throw new InvalidConfigException(get_class($this) . "::key must be configured with a secret key.");
        }

        $this->request = Yii::$app->request;
        $this->response = Yii::$app->response;
    }

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        if ($this->request->getMethod() == 'OPTIONS') {
            return true;
        }

        $payload = $this->getPayload();
        if (!$payload) {
            return null;
        }
        return User::findIdentity($payload->user->id);
    }

    /**
     * Get payload from cookie or header
     * @return object
     */
    public function getPayload()
    {
        if ($this->payload) {
            return $this->payload;
        }

        // check cookie first
        $request = Yii::$app->request;
        $jwt = $request->cookies->getValue($this->cookieName);
        if ($jwt) {
            $payload = $this->decode($jwt);
            if ($payload) {
                $this->payload = $payload;
                return $payload;
            }
        }

        // then check header
        $authHeader = $request->getHeaders()->get("Authorization");
        if ($authHeader !== null && preg_match("/^Bearer\\s+(.*?)$/", $authHeader, $matches)) {
            $payload = $this->decode($matches[1]);
            if ($payload) {
                $this->payload = $payload;
                return $payload;
            }
        }

        return false;
    }

    /**
     * Set jwt in cookie
     * @param string $jwt
     */
    public function setCookieJwt($jwt)
    {
        $this->response->cookies->add(new Cookie([
            "name" => $this->cookieName,
            "value" => $jwt,
            "secure" => $this->request->isSecureConnection,
            "expire" => strtotime("+1 year"), // use exp claim in jwt instead of cookie
        ]));
    }

    /**
     * Remove jwt cookie
     */
    public function removeCookieJwt()
    {
        $this->response->cookies->remove($this->cookieName);
    }

    /**
     * Encode data into jwt string
     * @param array|object $data
     * @param int|string $ttl seconds from current time
     * @return string
     * @link http://websec.io/2014/08/04/Securing-Requests-with-JWT.html
     */
    public function encode($data, $ttl = null)
    {
        // build token data
        $data = (array) $data;
        $token = $this->getTokenDefaults();
        $token = array_merge($token, $data);

        // add in expire time if set
        $ttl = $ttl === null ? $this->ttl : $ttl;
        if ($ttl) {
            $token["exp"] = is_string($ttl) ? strtotime($ttl) : $token["iat"] + $ttl;
        }

        return JWT::encode($token, $this->key, $this->algorithm);
    }

    /**
     * Decode jwt string
     * Check iss, aud, iat, nbt, and exp claims
     * @param string $jwt
     * @return object
     * @throws Exception
     */
    public function decode($jwt)
    {
        JWT::$leeway = $this->leeway;
        try {
            $payload = JWT::decode($jwt, $this->key, [$this->algorithm]);
            $tokenDefaults = $this->getTokenDefaults();
            if ($payload->iss != $tokenDefaults["iss"] || $payload->aud != $tokenDefaults["aud"]) {
                return false;
            }
            if (!$this->request->validateCsrfToken($payload->jti)) {
                return false;
            }
            return $payload;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get token defaults
     * @return array
     */
    protected function getTokenDefaults()
    {
        $time = time();
        $request = Yii::$app->request;
        return [
            "iss" => $request->getHostInfo(),
            "aud" => parse_url($request->getReferrer(), PHP_URL_HOST) ?: $request->getHostInfo(),
            "jti" => $request->getCsrfToken(),
            "iat" => $time,
            "nbf" => $time,
        ];
    }

    /**
     * Generate a jwt token for user
     * @param array $userAttributes
     * @param bool $rememberMe
     * @return string
     */
    public function generateUserToken($userAttributes, $rememberMe)
    {
        $userAttributes = (array) $userAttributes;
        $ttl = $rememberMe ? $this->ttlRememberMe : $this->ttl;

        return $this->encode([
            "sub" => $userAttributes["id"],
            "user" => $userAttributes,
            "rememberMe" => $rememberMe ? 1 : 0,
        ], $ttl);
    }

    /**
     * Generate a jwt token for user based on access token
     * Note: this token does NOT expire, so you should have some way to revoke the access token
     * @param string $accessToken
     * @param int $id
     * @return string
     */
    public function generateRefreshToken($accessToken, $id = null)
    {
        // add sub if set. this isn't needed, but can be set if desired
        if ($id) {
            $data["sub"] = $id;
        }
        $data["accessToken"] = $accessToken;

        // set ttl = 0 so it won't get an exp
        $ttl = 0;
        return $this->encode($data, $ttl);
    }

    /**
     * Regenerate a token (update iat, nbf, and exp)
     * @param object $payload
     * @return string
     */
    public function regenerateToken($payload)
    {
        // calculate ttl
        $ttl = null;
        if (!empty($payload->exp)) {
            $ttl = $payload->exp - $payload->iat;
        }

        // calculate times and csrf
        $time = time();
        $payload->iat = $time;
        $payload->nbf = $time;
        $payload->jti = $this->request->getCsrfToken(true);
        return $this->encode($payload, $ttl);
    }
}
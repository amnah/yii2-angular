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
    public $cookieName = "token";

    /**
     * @var object Payload from cookie or header auth
     */
    private $payload;

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

        $payload = $this->getCookieHeaderPayload();
        if (!$payload) {
            return null;
        }
        return User::findIdentity($payload->user->id);
    }

    /**
     * Get payload from cookie or header
     * @return object
     */
    public function getCookieHeaderPayload()
    {
        if ($this->payload) {
            return $this->payload;
        }

        // check cookie first
        $request = Yii::$app->request;
        $token = $request->cookies->getValue($this->cookieName);
        if ($token) {
            $payload = $this->decode($token);
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
     * Set token in cookie
     * @param string $token
     * @param int $exp
     */
    public function setCookieToken($token, $exp)
    {
        $this->response->cookies->add(new Cookie([
            "name" => $this->cookieName,
            "value" => $token,
            "secure" => $this->request->isSecureConnection,
            "expire" => $exp,
        ]));
    }

    /**
     * Remove token cookie
     */
    public function removeCookieToken()
    {
        $this->response->cookies->remove($this->cookieName);
    }

    /**
     * Encode data into jwt token string
     * @param array|object $data
     * @return string
     * @link http://websec.io/2014/08/04/Securing-Requests-with-JWT.html
     */
    public function encode($data)
    {
        // build token data
        $data = (array) $data;
        $token = $this->getTokenDefaults();
        $token = array_merge($token, $data);
        return JWT::encode($token, $this->key, $this->algorithm);
    }

    /**
     * Decode jwt token string
     * Check iss, aud, iat, nbt, and exp claims
     * @param string $token
     * @return object
     * @throws Exception
     */
    public function decode($token)
    {
        JWT::$leeway = $this->leeway;
        try {
            $payload = JWT::decode($token, $this->key, [$this->algorithm]);
            $tokenDefaults = $this->getTokenDefaults();
            if ($payload->iss != $tokenDefaults["iss"] || $payload->aud != $tokenDefaults["aud"]) {
                return false;
            }
            if ($payload->useCookie && !$this->request->validateCsrfToken($payload->jti)) {
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
     * @param bool $useCookie
     * @return string
     */
    public function generateUserToken($userAttributes, $rememberMe = true, $useCookie = true)
    {
        $userAttributes = (array) $userAttributes;
        $data = [
            "sub" => $userAttributes["id"],
            "user" => $userAttributes,
            "rememberMe" => $rememberMe ? 1 : 0,
            "useCookie" => $useCookie ? 1 : 0,
        ];

        // compute expire time and encode
        $ttl = $rememberMe ? $this->ttlRememberMe : $this->ttl;
        $exp = is_string($ttl) ? strtotime($ttl) : time() + $ttl;
        if ($ttl) {
            $data["exp"] = $exp;
        }
        $token = $this->encode($data);

        // set cookie and return
        if ($useCookie) {
            $this->setCookieToken($token, $exp);
        }
        return $token;
    }

    /**
     * Generate a jwt token for user based on access token
     * Note: this token does NOT expire, so you should have some way to revoke the access token
     * @param int $id
     * @param string $accessToken
     * @param bool $rememberMe
     * @param bool $useCookie
     * @return string
     */
    public function generateRefreshToken($id, $accessToken, $rememberMe = true, $useCookie = true)
    {
        $data = [
            "sub" => $id,
            "accessToken" => $accessToken,
            "rememberMe" => $rememberMe ? 1 : 0,
            "useCookie" => $useCookie ? 1 : 0,
        ];
        $refreshToken = $this->encode($data);

        // set cookie and return
        if ($useCookie) {
            $this->setCookieToken($refreshToken, strtotime("2037-12-31")); // far, far future
        }
        return $refreshToken;
    }
}
<?php

namespace app\components;

use Exception;

use Yii;
use yii\base\InvalidConfigException;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Cookie;
use yii\web\IdentityInterface;
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
    public $algorithm = "HS256";

    /**
     * @var int|string Default token expiration. Integer = seconds, string = strtotime()
     *                 Example: 300 = "+5 minutes"
     */
    public $ttl = "+2 hours";

    /**
     * @var int|string Token expiration when user sets "remember me"
     * @link http://stackoverflow.com/questions/26739167/jwt-json-web-token-automatic-prolongation-of-expiration/26834685#26834685
     */
    public $ttlRememberMe = "+1 week";

    /**
     * @var int Jwt expiration leeway (in seconds)
     * @link https://github.com/firebase/php-jwt#example
     */
    public $leeway = 60;

    /**
     * @var string Token param name (to check in $_GET and cookies)
     */
    public $tokenParam = "_token";

    /**
     * @var string Refresh token param name (to check in $_GET and cookies)
     */
    public $refreshTokenParam = "_refreshToken";

    /**
     * @var bool Flag for if payload is from cookie
     */
    public $fromJwtCookie;

    /**
     * @var IdentityInterface Authenticated user
     */
    protected $authenticatedUser;

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
        if ($request->getIsOptions()) {
            return true;
        }

        $payload = $this->getTokenPayload();
        if (!$payload) {
            return null;
        }

        /** @var IdentityInterface $class */
        $class = Yii::$app->user->identityClass;
        $user = $class::findIdentity($payload->user->id);

        // set identity for this one request
        // this is needed for other filters to work properly, eg, \yii\filters\RateLimiter
        Yii::$app->user->setIdentity($user);
        return $this->authenticatedUser = $user;
    }

    /**
     * Get the authenticated user
     * @return IdentityInterface
     */
    public function getAuthenticatedUser()
    {
        return $this->authenticatedUser;
    }

    /**
     * Get token payload from $_GET, cookie, or header (in that order)
     * @return object
     */
    public function getTokenPayload()
    {
        if ($this->payload !== null) {
            return $this->payload;
        }

        // check $_GET, cookie, and header
        $token = $this->request->get($this->tokenParam);
        if (!$token) {
            $token = $this->request->cookies->getValue($this->tokenParam);
            if ($token) {
                $this->fromJwtCookie = true;
            }
        }
        if (!$token) {
            $authHeader = $this->request->getHeaders()->get("Authorization");
            if ($authHeader !== null && preg_match("/^Bearer\\s+(.*?)$/", $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        // decode and store payload
        $this->payload = $token ? $this->decode($token) : false;
        return $this->payload;
    }

    /**
     * Get refresh token payload from $_GET or cookie
     * @return object
     */
    public function getRefreshTokenPayload()
    {
        // check $_GET and then cookie
        $refreshToken = $this->request->get($this->refreshTokenParam);
        if (!$refreshToken) {
            $refreshToken = $this->request->cookies->getValue($this->refreshTokenParam);
            if ($refreshToken) {
                $this->fromJwtCookie = true;
            }
        }

        // decode token
        return $refreshToken ? $this->decode($refreshToken) : false;
    }

    /**
     * Add token in cookie
     * @param string $cookieName
     * @param string $token
     * @param int $exp
     */
    public function addCookieToken($cookieName, $token, $exp)
    {
        $this->response->cookies->add(new Cookie([
            "name" => $cookieName,
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
        $this->response->cookies->remove($this->tokenParam);
        return $this;
    }

    /**
     * Remove refresh token cookie
     */
    public function removeRefreshCookieToken()
    {
        $this->response->cookies->remove($this->refreshTokenParam);
        return $this;
    }

    /**
     * Encode data into jwt token string
     * @param array|object $data
     * @return string
     */
    public function encode($data)
    {
        $data = (array) $data;
        $data = array_merge($this->getTokenDefaults(), $data);
        return JWT::encode($data, $this->key, $this->algorithm);
    }

    /**
     * Decode jwt token string
     * @param string $token
     * @return object|bool
     * @throws Exception
     */
    public function decode($token)
    {
        JWT::$leeway = $this->leeway;
        try {
            $payload = JWT::decode($token, $this->key, [$this->algorithm]);
        } catch (Exception $e) {
            return false;
        }

        // ensure that iss, aud, and csrf are good
        $tokenDefaults = $this->getTokenDefaults();
        if ($payload->iss != $tokenDefaults["iss"] || $payload->aud != $tokenDefaults["aud"]) {
            return false;
        }
        if (!empty($payload->csrf) && !$this->request->validateCsrfToken($payload->csrf)) {
            return false;
        }
        return $payload;
    }

    /**
     * Get token defaults
     * @return array
     * @link http://websec.io/2014/08/04/Securing-Requests-with-JWT.html
     */
    protected function getTokenDefaults()
    {
        $hostInfo = parse_url($this->request->getHostInfo(), PHP_URL_HOST);
        $referrerInfo = parse_url($this->request->getReferrer(), PHP_URL_HOST);
        return [
            "iss" => $hostInfo,
            "aud" => $referrerInfo ?: $hostInfo,
            "iat" => time(),
        ];
    }

    /**
     * Generate a jwt token for user
     * @param array $userAttributes
     * @param bool $rememberMe
     * @param bool $jwtCookie
     * @return string
     */
    public function generateUserToken($userAttributes, $rememberMe = false, $jwtCookie = false)
    {
        $userAttributes = (array) $userAttributes;
        $data = [
            "sub" => (int) $userAttributes["id"],
            "user" => $userAttributes,
            "rememberMe" => (int) $rememberMe,
        ];

        $ttl = $rememberMe ? $this->ttlRememberMe : $this->ttl;
        $exp = is_string($ttl) ? strtotime($ttl) : time() + $ttl;
        if ($ttl) {
            $data["exp"] = $exp;
        }

        // compute csrf if using cookie
        if ($jwtCookie) {
            $data["csrf"] = $this->request->getCsrfToken();
        }

        $token = $this->encode($data);
        if ($jwtCookie) {
            $this->addCookieToken($this->tokenParam, $token, $exp);
        }
        return $token;
    }

    /**
     * Generate a jwt token for user based on access token
     * Note: this token does NOT expire, so you should have some way to revoke the access token
     * @param int $id
     * @param string $accessToken
     * @param bool $jwtCookie
     * @return string
     */
    public function generateRefreshToken($id, $accessToken, $jwtCookie = true)
    {
        $data = [
            "sub" => (int) $id,
            "accessToken" => $accessToken,
        ];

        $refreshToken = $this->encode($data);
        if ($jwtCookie) {
            $this->addCookieToken($this->refreshTokenParam, $refreshToken, strtotime("2037-12-31")); // far, far future
        }
        return $refreshToken;
    }
}
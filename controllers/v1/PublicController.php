<?php

namespace app\controllers\v1;

use Yii;
use app\controllers\BaseApiController;
use app\models\Profile;
use app\models\Role;
use app\models\User;
use app\models\UserToken;
use app\models\forms\ContactForm;
use app\models\forms\LoginForm;

class PublicController extends BaseApiController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors["jwtAuth"]);
        return $behaviors;
    }

    /**
     * Contact
     */
    public function actionContact()
    {
        $model = new ContactForm();
        $toEmail = Yii::$app->params["adminEmail"];
        $model->load(Yii::$app->request->post(), "");
        if ($model->contact($toEmail)) {
            return ["success" => true];
        }
        return ["errors" => $model->errors];
    }

    /**
     * Login
     */
    public function actionLogin()
    {
        // notice that we set the second parameter $formName = ""
        $request = Yii::$app->request;
        $model = new LoginForm();
        $model->load($request->post(), "");
        if ($model->validate()) {
            $userAttributes = $model->getUser()->toArray();
            $rememberMe = $request->post("rememberMe", true);
            $jwtCookie = $request->post("jwtCookie", true);
            $authJwtData = $this->generateAuthOutput($userAttributes, $rememberMe, $jwtCookie);
            return ["success" => $authJwtData];
        }
        return ["errors" => $model->errors];
    }

    /**
     * Logout
     */
    public function actionLogout()
    {
        $jwtAuth = $this->jwtAuth;
        $jwtAuth->removeCookieToken();
        $jwtAuth->removeRefreshCookieToken();
        return ["success" => true];
    }

    /**
     * Register
     */
    public function actionRegister()
    {
        // load post data and validate
        $request = Yii::$app->request;
        $user = new User(["scenario" => "register"]);
        $profile = new Profile();
        $user->load($request->post(), "");
        $profile->load($request->post(), "");
        if (!$user->validate() || !$profile->validate()) {
            return ["errors" => array_merge($user->errors, $profile->errors)];
        }

        // create user/profile
        $user->setRegisterAttributes(Role::ROLE_USER)->save(false);
        $profile->setUser($user->id)->save(false);

        // determine userToken type to see if we need to send email
        $userTokenType = null;
        if ($user->status == $user::STATUS_INACTIVE) {
            $userTokenType = UserToken::TYPE_EMAIL_ACTIVATE;
        } elseif ($user->status == $user::STATUS_UNCONFIRMED_EMAIL) {
            $userTokenType = UserToken::TYPE_EMAIL_CHANGE;
        }

        // check if we have a userToken type to process, or just generate jwt data
        if ($userTokenType) {
            $userToken = UserToken::generate($user->id, $userTokenType);
            $user->sendEmailConfirmation($userToken);
            return ["success" => ["userToken" => 1]];
        } else {
            $userAttributes = $user->toArray();
            $rememberMe = $request->post("rememberMe", true);
            $jwtCookie = $request->post("jwtCookie", true);
            return ["success" => $this->generateAuthOutput($userAttributes, $rememberMe, $jwtCookie)];
        }
    }

    /**
     * Confirm email
     */
    public function actionConfirm()
    {
        /** @var User $user */

        // search for userToken
        $success = false;
        $email = "";
        $token = Yii::$app->request->get("token");
        $userToken = UserToken::findByToken($token, [UserToken::TYPE_EMAIL_ACTIVATE, UserToken::TYPE_EMAIL_CHANGE]);
        if ($userToken) {

            // find user and ensure that another user doesn't have that email
            //   for example, user registered another account before confirming change of email
            $user = User::findOne($userToken->user_id);
            $newEmail = $userToken->data;
            if ($user->confirm($newEmail)) {
                $success = true;
            }

            // set email and delete token
            $email = $newEmail ?: $user->email;
            $userToken->delete();
        }

        if ($success) {
            return ["success" => $email];
        } elseif ($email) {
            return ["error" => "Email is already active"];
        } else {
            return ["error" => "Invalid token"];
        }
    }

    /**
     * Renew token
     */
    public function actionRenewToken()
    {
        // attempt to renew token using regular token in $_GET, cookie, or header
        $jwtAuth = $this->jwtAuth;
        $payload = $jwtAuth->getTokenPayload();
        if ($payload) {
            return ["success" => $this->generateAuthOutput($payload->user, $payload->rememberMe, $payload->jwtCookie)];
        }

        // attempt to renew token using refresh token
        return $this->actionUseRefreshToken();
    }

    /**
     * Get refresh token
     * Note: PERMANENT. You should have some way to revoke these access tokens
     */
    public function actionRequestRefreshToken()
    {
        /** @var \app\models\User $user */

        $jwtAuth = $this->jwtAuth;
        $payload = $jwtAuth->getTokenPayload();
        if (!$payload) {
            return ["error" => Yii::t("app", "Invalid token")];
        }

        // get user based off of id and get access token
        $user = Yii::$app->user->identityClass;
        $user = $user::findIdentity($payload->sub);

        // generate refresh token
        // note that we use $user->id here, but it can also be the id of your token table
        $id = $user->id;
        $token = $user->access_token;
        return ["success" => $jwtAuth->generateRefreshToken($id, $token, $payload->jwtCookie)];
    }

    /**
     * Remove refresh token
     */
    public function actionRemoveRefreshToken()
    {
        $this->jwtAuth->removeRefreshCookieToken();
        return ["success" => true];
    }

    /**
     * Use refreshToken to refresh the regular token
     * @return array
     */
    public function actionUseRefreshToken()
    {
        /** @var \app\models\User $user */

        // get token/payload
        $jwtAuth = $this->jwtAuth;
        $payload = $jwtAuth->getRefreshTokenPayload();
        if (!$payload) {
            return ["error" => Yii::t("app", "Invalid token")];
        }

        // find user and generate auth data
        // note: use $rememberMe = false for refresh tokens. faster expiration = more security
        $rememberMe = false;
        $user = Yii::$app->user->identityClass;
        $user = $user::findIdentityByAccessToken($payload->accessToken);
        return ["success" => $this->generateAuthOutput($user->toArray(), $rememberMe, $payload->jwtCookie)];
    }

    /**
     * Generate auth data (for sending back to client)
     * @param array|object $userAttributes
     * @param bool $rememberMe
     * @param bool $jwtCookie
     * @return boolean
     */
    protected function generateAuthOutput($userAttributes, $rememberMe, $jwtCookie)
    {
        $jwtAuth = $this->jwtAuth;
        $token = $jwtAuth->generateUserToken($userAttributes, $rememberMe, $jwtCookie);
        return [
            "user" => $userAttributes,
            "token" => $token,
        ];
    }
}

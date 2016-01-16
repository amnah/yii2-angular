<?php

namespace app\controllers\v1;

use Yii;
use app\models\Profile;
use app\models\Role;
use app\models\User;
use app\models\UserToken;
use app\models\forms\LoginForm;
use amnah\yii2\user\models\forms\ForgotForm;
use amnah\yii2\user\models\forms\LoginEmailForm;

class AuthController extends PublicController
{
    /**
     * Generate auth data (for sending back to client)
     * @param User $user
     * @param bool $rememberMe
     * @param bool $jwtCookie
     * @return boolean
     */
    protected function generateAuthOutput($user, $rememberMe, $jwtCookie)
    {
        $jwtAuth = $this->jwtAuth;
        $userAttributes = is_callable([$user, "toArray"]) ? $user->toArray() : $user;
        $token = $jwtAuth->generateUserToken($userAttributes, $rememberMe, $jwtCookie);
        return [
            "user" => $user,
            "token" => $token,
        ];
    }

    /**
     * Login
     */
    public function actionLogin()
    {
        /** @var User $user */

        // notice that we set the second parameter $formName = ""
        $request = Yii::$app->request;
        $model = new LoginForm();
        $model->load($request->post(), "");
        if ($model->validate()) {
            $user = $model->getUser();
            $rememberMe = $request->post("rememberMe", true);
            $jwtCookie = $request->post("jwtCookie", true);
            $authJwtData = $this->generateAuthOutput($user, $rememberMe, $jwtCookie);
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
            $rememberMe = $request->post("rememberMe", true);
            $jwtCookie = $request->post("jwtCookie", true);
            return ["success" => $this->generateAuthOutput($user, $rememberMe, $jwtCookie)];
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
    public function actionRenewToken($refreshDb = 0)
    {
        /** @var \app\models\User $user */

        // check payload for user data
        $user = null;
        $jwtAuth = $this->jwtAuth;
        $payload = $jwtAuth->getTokenPayload();
        if ($payload && !$refreshDb) {
            $user = $payload->user;
        } elseif ($payload && $refreshDb) {
            $user = Yii::$app->user->identityClass;
            $user = $user::findIdentity($payload->sub);
        }

        // renew token using user if it's set
        // otherwise attempt to renew token using refresh token
        if ($user) {
            return ["success" => $this->generateAuthOutput($user, $payload->rememberMe, $payload->jwtCookie)];
        }
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
     */
    public function actionUseRefreshToken()
    {
        /** @var User $user */

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
        return ["success" => $this->generateAuthOutput($user, $rememberMe, $payload->jwtCookie)];
    }

    /**
     * Login via email
     */
    public function actionLoginEmail()
    {
        $post = Yii::$app->request->post();
        $loginEmailForm = new LoginEmailForm();
        if ($loginEmailForm->load($post, "") && $loginEmailForm->sendEmail()) {
            return [
                "success" => true,
                "user" => $loginEmailForm->getUser(),
            ];
        }

        return ["errors" => $loginEmailForm->errors];
    }

    /**
     * Login/register callback via email
     */
    public function actionLoginCallback($token, $jwtCookie = true)
    {
        /** @var User $user */

        // check token and log user in directly
        $userToken = UserToken::findByToken($token, UserToken::TYPE_EMAIL_LOGIN);
        if (!$userToken) {
            return ["error" => "Invalid token"];
        }

        // log user in directly
        $rememberMe = $userToken->data;
        $user = $userToken->user;
        if ($user) {
            $userToken->delete();
            return ["success" => $this->generateAuthOutput($user, $rememberMe, $jwtCookie)];
        }

        // check for post data
        $user = new User();
        $profile = new Profile();
        $post = Yii::$app->request->post();
        if (!$user->load($post, "")) {
            return ["success" => true, "email" => $userToken->data];
        }

        // ensure that email is taken from the $userToken (and not from user input)
        $user->email = $userToken->data;

        // load profile, validate, and register
        $profile->load($post);
        $userValidate = $user->validate();
        $profileValidate = $profile->validate();
        if ($userValidate && $profileValidate) {
            $user->setRegisterAttributes(Role::ROLE_USER, User::STATUS_ACTIVE)->save();
            $profile->setUser($user->id)->save();
            $userToken->delete();
            return ["success" => $this->generateAuthOutput($user, $rememberMe, $jwtCookie)];
        } else {
            $errors = array_merge($user->errors, $profile->errors);
            return ["errors" => $errors];
        }
    }

    /**
     * Forgot
     */
    public function actionForgot()
    {
        $model = new ForgotForm();
        $model->load(Yii::$app->request->post(), "");
        if ($model->sendForgotEmail()) {
            return ["success" => true];
        }
        return ["errors" => $model->errors];

    }

    /**
     * Reset
     */
    public function actionReset($token)
    {
        /** @var User $user */

        // get user token and check expiration
        $userToken = UserToken::findByToken($token, UserToken::TYPE_PASSWORD_RESET);
        if (!$userToken) {
            return ["error" => "Invalid token"];
        }

        // get user and load post
        // return user email if user hasn't submitted yet
        $user = User::findOne($userToken->user_id);
        $loadedPost = $user->load(Yii::$app->request->post(), "");
        if (!$loadedPost) {
            return ["success" => $user->email];
        }

        // set scenario and save new password
        $user->setScenario("reset");
        if ($user->save(true, ["password", "newPassword", "newPasswordConfirm"])) {
            $userToken->delete();
            return ["success" => true];
        }
        return ["errors" => $user->errors];
    }
}

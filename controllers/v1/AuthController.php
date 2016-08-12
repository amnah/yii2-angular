<?php

namespace app\controllers\v1;

use Yii;
use app\models\Profile;
use app\models\Role;
use app\models\User;
use app\models\UserToken;
use app\models\forms\LoginForm;
use app\models\forms\ForgotForm;
use app\models\forms\LoginEmailForm;

class AuthController extends PublicController
{
    /**
     * Login
     */
    public function actionLogin()
    {
        /** @var User $user */

        $request = Yii::$app->request;
        $model = new LoginForm();
        if ($model->loadPostAndValidate()) {
            $user = $model->getUser();
            $rememberMe = $request->post("rememberMe", true);
            $jwtCookie = $request->post("jwtCookie", true);
            return ["success" => $this->generateAuthSuccess($user, $rememberMe, $jwtCookie)];
        }
        return ["errors" => $model->errors];
    }

    /**
     * Logout
     */
    public function actionLogout()
    {
        $this->jwtAuth->removeCookieToken()->removeRefreshCookieToken();
        return ["success" => true];
    }

    /**
     * Register
     */
    public function actionRegister()
    {
        $user = new User(["scenario" => "register"]);
        $profile = new Profile();

        // ensure that both models get validated for errors
        $userValidate = $user->loadPostAndValidate();
        $profileValidate = $profile->loadPostAndValidate();
        if (!$userValidate || !$profileValidate) {
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
            $request = Yii::$app->request;
            $rememberMe = $request->post("rememberMe", true);
            $jwtCookie = $request->post("jwtCookie", true);
            return ["success" => $this->generateAuthSuccess($user, $rememberMe, $jwtCookie)];
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
        /** @var User $user */

        $user = null;
        $jwtAuth = $this->jwtAuth;
        $payload = $jwtAuth->getTokenPayload();

        // renew token directly or generate fresh from db
        if ($payload && !$refreshDb) {
            $token = $jwtAuth->renewToken($payload);
            return ["success" => ["user" => $payload->user, "token" => $token]];
        } elseif ($payload && $refreshDb) {
            $user = Yii::$app->user->identityClass;
            $user = $user::findIdentity($payload->user->id);
            return ["success" => $this->generateAuthSuccess($user, $payload->rememberMe, $jwtAuth->fromJwtCookie)];
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
        /** @var User $user */

        $jwtAuth = $this->jwtAuth;
        $payload = $jwtAuth->getTokenPayload();
        if (!$payload) {
            return ["error" => Yii::t("app", "Invalid token")];
        }

        // get user based off of id and get access token
        $user = Yii::$app->user->identityClass;
        $user = $user::findIdentity($payload->user->id);

        // generate refresh token
        // you can change this, eg, UserToken.token instead of User.access_token
        $token = $user->access_token;
        return ["success" => $jwtAuth->generateRefreshToken($user, $token, $jwtAuth->fromJwtCookie)];
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
        $returnError = ["error" => Yii::t("app", "Invalid token")];
        if (!$payload) {
            return $returnError;
        }

        // find user and check data
        $user = Yii::$app->user->identityClass;
        $user = $user::findIdentityByAccessToken($payload->accessToken);
        if (!$jwtAuth->checkUserAuthHash($user, $payload->auth)) {
            return $returnError;
        }

        // use $rememberMe = false for refresh tokens. faster expiration = more security
        $rememberMe = false;
        return ["success" => $this->generateAuthSuccess($user, $rememberMe, $jwtAuth->fromJwtCookie)];
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
     * Login via email
     */
    public function actionLoginEmail()
    {
        $loginEmailForm = new LoginEmailForm();
        if ($loginEmailForm->loadPost() && $loginEmailForm->sendEmail()) {
            return ["success" => ["user" => $loginEmailForm->getUser()]];
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
            return ["success" => $this->generateAuthSuccess($user, $rememberMe, $jwtCookie)];
        }

        // check for post data (for registering)
        $user = new User();
        $profile = new Profile();
        if (!$user->loadPost()) {
            return ["success" => true, "email" => $userToken->data];
        }

        // ensure that email is taken from the $userToken (and not from user input)
        $user->email = $userToken->data;

        // load profile, validate, and register
        $userValidate = $user->validate();
        $profileValidate = $profile->loadPostAndValidate();
        if ($userValidate && $profileValidate) {
            $user->setRegisterAttributes(Role::ROLE_USER, User::STATUS_ACTIVE)->save();
            $profile->setUser($user->id)->save();
            $userToken->delete();
            return ["success" => $this->generateAuthSuccess($user, $rememberMe, $jwtCookie)];
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
        if ($model->loadPost() && $model->sendForgotEmail()) {
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
        if (!$user->loadPost()) {
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

    /**
     * Generate auth success (for sending back to client)
     * @param User $user
     * @param bool $rememberMe
     * @param bool $jwtCookie
     * @return array
     */
    protected function generateAuthSuccess($user, $rememberMe, $jwtCookie)
    {
        $token = $this->jwtAuth->generateUserToken($user, $rememberMe, $jwtCookie);
        return [
            "user" => $user->toArray(),
            "token" => $token,
        ];
    }
}

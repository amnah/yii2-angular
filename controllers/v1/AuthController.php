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

        $model = new LoginForm();
        if ($model->loadPostAndValidate()) {
            $user = $model->getUser();
            return $this->performLogin($user);
        }
        return ["errors" => $model->errors];
    }

    /**
     * Perform the actual login
     * @param User $user
     * @param bool $rememberMe
     * @return array
     */
    protected function performLogin($user, $rememberMe = null)
    {
        if (!$user) {
            return ["error" => "Invalid user"];
        }

        /** @var \amnah\yii2\user\Module $userModule */
        $userModule = Yii::$app->getModule("user");

        // check rememberMe from post request
        if ($rememberMe === null) {
            $rememberMe = $this->request->post("rememberMe");
        }
        $loginDuration = $rememberMe ? $userModule->loginDuration : 0;
        $this->user->login($user, $loginDuration);
        return ["success" => ["user" => $user]];
    }

    /**
     * Logout
     */
    public function actionLogout()
    {
        $this->user->logout();
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

        // check if we have a userToken type to process, or just log in normally
        if ($userTokenType) {
            $userToken = UserToken::generate($user->id, $userTokenType);
            $user->sendEmailConfirmation($userToken);
            return ["success" => ["userToken" => 1]];
        } else {
            return $this->performLogin($user);
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
        $token = $this->request->get("token");
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
     * Check auth status
     */
    public function actionCheckAuth()
    {
        /** @var User $user */
        $user = $this->user->identity;
        if ($user) {
            return ["success" => ["user" => $user]];
        }
        return ["error" => "Not logged in"];
    }

    /**
     * Get csrf token
     */
    public function actionGetCsrfToken()
    {
        return ["success" => $this->request->csrfToken];
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
    public function actionLoginCallback($token)
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
            return $this->performLogin($user, $rememberMe);
        }

        // check for post data (for registering)
        $user = new User();
        $profile = new Profile();
        if (!$user->loadPost()) {
            return ["success" => true, "email" => $userToken->data];
        }

        // ensure that email is taken from the $userToken (NOT from user input)
        $user->email = $userToken->data;
        $rememberMe = 1;

        // load profile, validate, and register
        $userValidate = $user->validate();
        $profileValidate = $profile->loadPostAndValidate();
        if ($userValidate && $profileValidate) {
            $user->setRegisterAttributes(Role::ROLE_USER, User::STATUS_ACTIVE)->save();
            $profile->setUser($user->id)->save();
            $userToken->delete();
            return $this->performLogin($user, $rememberMe);
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
}
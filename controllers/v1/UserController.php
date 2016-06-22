<?php

namespace app\controllers\v1;

use Yii;
use app\controllers\BaseApiController;
use app\models\User;
use app\models\UserToken;
use app\models\Profile;

class UserController extends BaseApiController
{
    /**
     * User account
     */
    public function actionIndex()
    {
        /** @var User $user */

        // get user
        $user = $this->jwtAuth->getAuthenticatedUser();
        $user->setScenario("account");

        // check for post input errors
        $loadedAndValidated = $user->loadPostAndValidate();
        if ($loadedAndValidated === false) {
            return ["errors" => $user->errors];
        }

        // process account update or find a $userToken (for pending email confirmation)
        $userToken = null;
        if ($loadedAndValidated) {

            // check if user changed his email
            $newEmail = $user->checkEmailChange();
            if ($newEmail) {
                $userToken = UserToken::generate($user->id, UserToken::TYPE_EMAIL_CHANGE, $newEmail);
                $user->sendEmailConfirmation($userToken);
            }
            $user->save(false);
        } else {
            $userToken = UserToken::findByUser($user->id, UserToken::TYPE_EMAIL_CHANGE);
        }

        $hasPassword = (bool) $user->password;
        return ["success" => ["user" => $user, "userToken" => $userToken, "hasPassword" => $hasPassword]];
    }

    /**
     * Resend email change
     */
    public function actionChangeResend()
    {
        /** @var User $user */

        $user = $this->jwtAuth->getAuthenticatedUser();
        $userToken = UserToken::findByUser($user->id, UserToken::TYPE_EMAIL_CHANGE);
        if ($userToken) {
            $user->sendEmailConfirmation($userToken);
            return ["success" => true];
        }
        return ["error" => true];
    }

    /**
     * Cancel email change
     */
    public function actionChangeCancel()
    {
        /** @var User $user */

        $user = $this->jwtAuth->getAuthenticatedUser();
        $userToken = UserToken::findByUser($user->id, UserToken::TYPE_EMAIL_CHANGE);
        if ($userToken) {
            $userToken->delete();
            return ["success" => true];
        }
        return ["error" => true];
    }

    /**
     * Profile
     */
    public function actionProfile()
    {
        /** @var User $user */
        /** @var Profile $profile */

        // get user and profile
        $user = $this->jwtAuth->getAuthenticatedUser();
        $profile = Profile::findOne(["user_id" => $user->id]);

        // update profile
        if ($profile->loadPostAndSave() === false) {
            return ["errors" => $profile->errors];
        }

        return ["success" => ["profile" => $profile]];
    }
}

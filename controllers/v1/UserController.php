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
        $payload = $this->jwtAuth->getTokenPayload();
        $user = User::findOne($payload->user->id);
        $user->setScenario("account");

        // check for post input errors
        $loadedPost = $user->load(Yii::$app->request->post(), "");
        if ($loadedPost && !$user->validate()) {
            return ["errors" => $user->errors];
        }

        // process account update or find a $userToken (for pending email confirmation)
        $userToken = null;
        if ($loadedPost) {

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

        $payload = $this->jwtAuth->getTokenPayload();
        $user = User::findOne($payload->user->id);
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
        $payload = $this->jwtAuth->getTokenPayload();
        $userToken = UserToken::findByUser($payload->user->id, UserToken::TYPE_EMAIL_CHANGE);
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
        $payload = $this->jwtAuth->getTokenPayload();
        $user = User::findOne($payload->user->id);
        $profile = Profile::findOne(["user_id" => $user->id]);

        // update profile
        $loadedPost = $profile->load(Yii::$app->request->post(), "");
        if ($loadedPost && !$profile->save()) {
            return ["errors" => $profile->errors];
        }

        return ["success" => ["profile" => $profile]];
    }
}

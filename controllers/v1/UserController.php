<?php

namespace app\controllers\v1;

use Yii;
use app\controllers\BaseApiController;
use app\models\User;
use app\models\UserToken;
use app\models\Profile;

class UserController extends BaseApiController
{
    public function actionIndex()
    {
        $payload = $this->jwtAuth->getTokenPayload();
        if (!$payload) {
            return ["error" => true];
        }
        return ["success" => $payload->user];
    }

    /**
     * Account
     */
    public function actionAccount()
    {
        /** @var User $user */
        /** @var UserToken $userToken */

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

<?php

namespace app\controllers\v1;

use Yii;
use app\controllers\BaseApiController;
use app\models\User;
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

        return ["success" => $profile->toArray()];
    }
}

<?php
namespace tests\models;
use app\models\User;

class UserTest extends \Codeception\Test\Unit
{
    public function testFindUserById()
    {
        expect_that($user = User::findIdentity(1));
        expect($user->username)->equals('neo');

        expect_not(User::findIdentity(999));
    }

    public function testFindUserByAccessToken()
    {
        expect_that($user = User::findIdentityByAccessToken('CH6vulJCn5h-YeUXRKIiD93jjAkA7CLP'));
        expect($user->username)->equals('neo');

        expect_not(User::findIdentityByAccessToken('non-existing'));        
    }

    public function testFindUserByUsername()
    {
        expect_that($user = User::find()->where(['username' => 'neo'])->one());
        expect_not(User::find()->where(['username' => 'not-neo'])->one());
    }

    /**
     * @depends testFindUserByUsername
     */
    public function testValidateUser()
    {
        /** @var User $user */
        $user = User::find()->where(['username' => 'neo'])->one();
        expect_that($user->validateAuthKey('OE3G_BQnj5RHipsS4vye_NFARS6PouFr'));
        expect_not($user->validateAuthKey('test102key'));

        expect_that($user->validatePassword('neo'));
        expect_not($user->validatePassword('123456'));
    }

}

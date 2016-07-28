<?php
namespace tests\models;
use app\models\User;

class User2Test extends \Codeception\Test\Unit
{
    public function testFindUserByUsername()
    {
        expect_that($user = User::find()->where(['username' => 'neozzz'])->one());
        expect_not(User::find()->where(['username' => 'neo'])->one());
    }
}

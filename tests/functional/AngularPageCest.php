<?php
class AngularPageCest
{
    public function _before(\FunctionalTester $I)
    {
        $I->amOnPage(['/']);
    }

    public function openPage(\FunctionalTester $I)
    {
        $I->see('Yii 2 angular', 'p');
    }
}

<?php

namespace frontend\tests\functional;

use frontend\tests\FunctionalTester;

class HomeCest
{
    public function checkOpen(FunctionalTester $I)
    {
        $I->amOnPage(\Yii::$app->homeUrl);
        $I->see('My Company');
        $I->seeLink('注册');
        $I->seeLink('登录');
        $I->click('登录');
        $I->see('Login', 'h1');
    }
}
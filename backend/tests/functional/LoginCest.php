<?php

namespace backend\tests\functional;

use \backend\tests\FunctionalTester;
use common\fixtures\User as UserFixture;

/**
 * Class LoginCest
 */
class LoginCest
{
    public function _before(FunctionalTester $I)
    {
        $I->haveFixtures([
            'user' => [
                'class' => UserFixture::className(),
                'dataFile' => codecept_data_dir() . 'login_data.php'
            ]
        ]);
    }
    /**
     * @param FunctionalTester $I
     */
    public function loginUser(FunctionalTester $I)
    {
        $I->amOnPage('/site/login');
        $I->fillField('Username', 'erau');
        $I->fillField('Password', 'password_0');
        $I->click('login-button');

        $I->see('退出 (erau)', 'form button[type=submit]');
        $I->dontSeeLink('登录');
        $I->dontSeeLink('注册');
    }
}

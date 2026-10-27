<?php

namespace frontend\tests\unit\models;

use common\fixtures\User as UserFixture;
use frontend\models\ResetPasswordForm;

class ResetPasswordFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \frontend\tests\UnitTester
     */
    protected $tester;


    public function _before()
    {
        $this->tester->haveFixtures([
            'user' => [
                'class' => UserFixture::className(),
                'dataFile' => codecept_data_dir() . 'user.php'
            ],
        ]);
    }

    public function testResetWrongToken()
    {
        $this->expectException('yii\base\InvalidParamException');
        new ResetPasswordForm('');
    }

    public function testResetNonexistentToken()
    {
        $this->expectException('yii\base\InvalidParamException');
        new ResetPasswordForm('notexistingtoken_1391882543');
    }

    public function testResetCorrectToken()
    {
        $user = $this->tester->grabFixture('user', 0);
        $form = new ResetPasswordForm($user['password_reset_token']);
        $this->assertNotEmpty($form->resetPassword());
    }

}

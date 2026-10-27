<?php

namespace common\tests\unit\models;

use Yii;
use common\models\LoginForm;
use common\fixtures\User as UserFixture;

/**
 * Login form test
 */
class LoginFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \frontend\tests\UnitTester
     */
    protected $tester;


    public function _before()
    {
        Yii::$app->request->cookieValidationKey = 'fund-planning-tests-only';
        $this->tester->haveFixtures([
            'user' => [
                'class' => UserFixture::className(),
                'dataFile' => codecept_data_dir() . 'user.php'
            ]
        ]);
    }

    public function testLoginNoUser()
    {
        $model = new LoginForm([
            'username' => 'not_existing_username',
            'password' => 'not_existing_password',
        ]);

        $this->assertFalse($model->login());
        $this->assertTrue(Yii::$app->user->isGuest);
    }

    public function testLoginWrongPassword()
    {
        $model = new LoginForm([
            'username' => 'bayer.hudson',
            'password' => 'wrong_password',
        ]);

        $this->assertFalse($model->login());
        $this->assertArrayHasKey('password', $model->errors);
        $this->assertTrue(Yii::$app->user->isGuest);
    }

    public function testLoginCorrect()
    {
        $model = new LoginForm([
            'username' => 'bayer.hudson',
            'password' => 'password_0',
        ]);

        $this->assertTrue($model->login());
        $this->assertArrayNotHasKey('password', $model->errors);
        $this->assertFalse(Yii::$app->user->isGuest);
    }
}

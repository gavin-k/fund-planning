<?php
namespace frontend\tests\unit\models;

use common\fixtures\User as UserFixture;
use frontend\models\SignupForm;

class SignupFormTest extends \Codeception\Test\Unit
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
            ]
        ]);
    }

    public function testCorrectSignup()
    {
        $model = new SignupForm([
            'username' => 'some_username',
            'email' => 'some_email@example.com',
            'password' => 'some_password',
        ]);

        $user = $model->signup();

        $this->assertInstanceOf('common\models\User', $user);

        $this->assertEquals('some_username', $user->username);
        $this->assertEquals('some_email@example.com', $user->email);
        $this->assertTrue($user->validatePassword('some_password'));
    }

    public function testNotCorrectSignup()
    {
        $model = new SignupForm([
            'username' => 'troy.becker',
            'email' => 'nicolas.dianna@hotmail.com',
            'password' => 'some_password',
        ]);

        $this->assertEmpty($model->signup());
        $this->assertNotEmpty($model->getErrors('username'));
        $this->assertNotEmpty($model->getErrors('email'));

        $this->assertEquals('This username has already been taken.', $model->getFirstError('username'));
        $this->assertEquals('This email address has already been taken.', $model->getFirstError('email'));
    }
}

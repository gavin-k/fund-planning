<?php

namespace frontend\tests\unit\models;

use Yii;
use frontend\models\PasswordResetRequestForm;
use common\fixtures\User as UserFixture;
use common\models\User;

class PasswordResetRequestFormTest extends \Codeception\Test\Unit
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

    public function testSendMessageWithWrongEmailAddress()
    {
        $model = new PasswordResetRequestForm();
        $model->email = 'not-existing-email@example.com';
        $this->assertEmpty($model->sendEmail());
    }

    public function testNotSendEmailsToInactiveUser()
    {
        $user = $this->tester->grabFixture('user', 1);
        $model = new PasswordResetRequestForm();
        $model->email = $user['email'];
        $this->assertEmpty($model->sendEmail());
    }

    public function testSendEmailSuccessfully()
    {
        $userFixture = $this->tester->grabFixture('user', 0);
        
        $model = new PasswordResetRequestForm();
        $model->email = $userFixture['email'];
        $user = User::findOne(['password_reset_token' => $userFixture['password_reset_token']]);

        $this->assertNotEmpty($model->sendEmail());
        $this->assertNotEmpty($user->password_reset_token);

        $emailMessage = $this->tester->grabLastSentEmail();
        $this->assertInstanceOf('yii\mail\MessageInterface', $emailMessage);
        $this->assertArrayHasKey($model->email, $emailMessage->getTo());
        $this->assertArrayHasKey(Yii::$app->params['supportEmail'], $emailMessage->getFrom());
    }
}

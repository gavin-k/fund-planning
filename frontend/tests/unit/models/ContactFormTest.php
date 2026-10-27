<?php
namespace frontend\tests\unit\models;

use Yii;
use frontend\models\ContactForm;

class ContactFormTest extends \Codeception\Test\Unit
{
    public function testSendEmail()
    {
        $model = new ContactForm();

        $model->attributes = [
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'subject' => 'very important letter subject',
            'body' => 'body of current message',
        ];

        $this->assertNotEmpty($model->sendEmail('admin@example.com'));

        // using Yii2 module actions to check email was sent
        $this->tester->seeEmailIsSent();

        $emailMessage = $this->tester->grabLastSentEmail();
        $this->assertInstanceOf('yii\mail\MessageInterface', $emailMessage);
        $this->assertArrayHasKey('admin@example.com', $emailMessage->getTo());
        $this->assertArrayHasKey('tester@example.com', $emailMessage->getFrom());
        $this->assertEquals('very important letter subject', $emailMessage->getSubject());
        $this->assertStringContainsString('body of current message', $emailMessage->toString());
    }
}

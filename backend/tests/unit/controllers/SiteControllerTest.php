<?php
namespace backend\tests\unit\controllers;

use Yii;
use backend\controllers\SiteController;
use common\models\Fund;
use common\models\Income;

class SiteControllerTest extends \Codeception\Test\Unit
{
    protected function _before()
    {
        foreach (['return_distribution', 'income_distribution', 'return_record', 'investment', 'income', 'fund', 'investment_product'] as $table) {
            Yii::$app->db->createCommand()->delete('{{%' . $table . '}}')->execute();
        }
    }

    protected function _after()
    {
        foreach (['return_distribution', 'income_distribution', 'return_record', 'investment', 'income', 'fund', 'investment_product'] as $table) {
            Yii::$app->db->createCommand()->delete('{{%' . $table . '}}')->execute();
        }
    }

    public function testDashboardRendersRealFundAndIncomeFields()
    {
        $fund = new Fund(['name' => 'Dashboard fund', 'allocation_percent' => 100, 'current_balance' => 1234]);
        $this->assertTrue($fund->save());
        $income = new Income([
            'source' => 'Salary', 'amount' => 100, 'income_date' => date('Y-m-d'),
            'notes' => 'Dashboard income note', 'is_distributed' => 1,
        ]);
        $this->assertTrue($income->save());

        $controller = new SiteController('site', Yii::$app);
        $controller->layout = false;
        Yii::$app->controller = $controller;
        $result = $controller->actionIndex();
        $this->assertStringContainsString('Dashboard fund', $result);
        $this->assertStringContainsString('1,234.00', $result);
        $this->assertStringContainsString('Dashboard income not', $result);
    }
}

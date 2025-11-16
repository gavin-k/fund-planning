<?php
namespace common\tests\unit\models;

use common\models\FinancialGoal;
use common\models\Fund;
use Codeception\Test\Unit;

class FinancialGoalTest extends Unit
{
    protected function _before()
    {
        // 清空测试数据
        FinancialGoal::deleteAll();
        Fund::deleteAll();
    }

    public function testCreateFinancialGoal()
    {
        $goal = new FinancialGoal();
        $goal->name = '买车';
        $goal->target_amount = 200000;
        $goal->target_date = date('Y-m-d', strtotime('+1 year'));

        $this->assertTrue($goal->save(), 'Goal should be saved');
        $this->assertNotNull($goal->id);
        $this->assertEquals(0, $goal->current_amount, 'Current amount should default to 0');
        $this->assertEquals(FinancialGoal::STATUS_IN_PROGRESS, $goal->status);
    }

    public function testValidationNameRequired()
    {
        $goal = new FinancialGoal();
        $goal->target_amount = 100000;
        $goal->target_date = date('Y-m-d', strtotime('+6 months'));

        $this->assertFalse($goal->save(), 'Should not save without name');
        $this->assertArrayHasKey('name', $goal->errors);
    }

    public function testValidationTargetAmountRequired()
    {
        $goal = new FinancialGoal();
        $goal->name = '旅游';
        $goal->target_date = date('Y-m-d', strtotime('+6 months'));

        $this->assertFalse($goal->save(), 'Should not save without target amount');
        $this->assertArrayHasKey('target_amount', $goal->errors);
    }

    public function testValidationTargetDateRequired()
    {
        $goal = new FinancialGoal();
        $goal->name = '旅游';
        $goal->target_amount = 50000;

        $this->assertFalse($goal->save(), 'Should not save without target date');
        $this->assertArrayHasKey('target_date', $goal->errors);
    }

    public function testGetProgress()
    {
        $goal = new FinancialGoal();
        $goal->name = '测试目标';
        $goal->target_amount = 100000;
        $goal->current_amount = 30000;
        $goal->target_date = date('Y-m-d', strtotime('+6 months'));
        $goal->save();

        $progress = $goal->getProgress();
        $this->assertEquals(30, $progress, 'Progress should be 30%');
    }

    public function testGetProgressMax100()
    {
        $goal = new FinancialGoal();
        $goal->name = '测试目标';
        $goal->target_amount = 100000;
        $goal->current_amount = 150000; // 超过目标
        $goal->target_date = date('Y-m-d', strtotime('+6 months'));
        $goal->save();

        $progress = $goal->getProgress();
        $this->assertEquals(100, $progress, 'Progress should not exceed 100%');
    }

    public function testGetRemainingAmount()
    {
        $goal = new FinancialGoal();
        $goal->name = '测试目标';
        $goal->target_amount = 100000;
        $goal->current_amount = 30000;
        $goal->target_date = date('Y-m-d', strtotime('+6 months'));
        $goal->save();

        $remaining = $goal->getRemainingAmount();
        $this->assertEquals(70000, $remaining);
    }

    public function testGetRemainingDays()
    {
        $goal = new FinancialGoal();
        $goal->name = '测试目标';
        $goal->target_amount = 100000;
        $goal->target_date = date('Y-m-d', strtotime('+30 days'));
        $goal->save();

        $days = $goal->getRemainingDays();
        $this->assertGreaterThanOrEqual(29, $days);
        $this->assertLessThanOrEqual(31, $days);
    }

    public function testGetSuggestedMonthlySaving()
    {
        $goal = new FinancialGoal();
        $goal->name = '测试目标';
        $goal->target_amount = 120000;
        $goal->current_amount = 0;
        $goal->target_date = date('Y-m-d', strtotime('+12 months'));
        $goal->save();

        $suggested = $goal->getSuggestedMonthlySaving();
        $this->assertGreaterThanOrEqual(9900, $suggested); // 约 10000/月
        $this->assertLessThanOrEqual(10100, $suggested);
    }

    public function testMarkAsCompleted()
    {
        $goal = new FinancialGoal();
        $goal->name = '测试目标';
        $goal->target_amount = 100000;
        $goal->current_amount = 80000;
        $goal->target_date = date('Y-m-d', strtotime('+1 month'));
        $goal->save();

        $this->assertTrue($goal->markAsCompleted());
        $this->assertEquals(FinancialGoal::STATUS_COMPLETED, $goal->status);
        $this->assertEquals(100000, $goal->current_amount);
        $this->assertNotNull($goal->completed_at);
    }

    public function testIsOverdue()
    {
        $goal = new FinancialGoal();
        $goal->name = '测试目标';
        $goal->target_amount = 100000;
        $goal->target_date = date('Y-m-d', strtotime('-1 day')); // 昨天
        $goal->save();

        $this->assertTrue($goal->isOverdue(), 'Goal should be overdue');
    }

    public function testIsDueSoon()
    {
        $goal = new FinancialGoal();
        $goal->name = '测试目标';
        $goal->target_amount = 100000;
        $goal->target_date = date('Y-m-d', strtotime('+5 days')); // 5天后
        $goal->save();

        $this->assertTrue($goal->isDueSoon(), 'Goal should be due soon');
    }

    public function testSyncCurrentAmountFromFund()
    {
        // 创建基金
        $fund = new Fund();
        $fund->name = '测试基金';
        $fund->allocation_percent = 20;
        $fund->current_balance = 50000;
        $fund->save();

        // 创建目标并关联基金
        $goal = new FinancialGoal();
        $goal->name = '测试目标';
        $goal->target_amount = 100000;
        $goal->fund_id = $fund->id;
        $goal->target_date = date('Y-m-d', strtotime('+6 months'));
        $goal->save();

        // 同步金额
        $this->assertTrue($goal->syncCurrentAmount());
        $this->assertEquals(50000, $goal->current_amount, 'Current amount should sync from fund balance');
    }

    public function testSyncCurrentAmountCapAtTarget()
    {
        // 创建基金
        $fund = new Fund();
        $fund->name = '测试基金';
        $fund->allocation_percent = 20;
        $fund->current_balance = 150000; // 超过目标
        $fund->save();

        // 创建目标并关联基金
        $goal = new FinancialGoal();
        $goal->name = '测试目标';
        $goal->target_amount = 100000;
        $goal->fund_id = $fund->id;
        $goal->target_date = date('Y-m-d', strtotime('+6 months'));
        $goal->save();

        // 同步金额
        $this->assertTrue($goal->syncCurrentAmount());
        $this->assertEquals(100000, $goal->current_amount, 'Current amount should not exceed target');
    }

    public function testGetStatusText()
    {
        $goal = new FinancialGoal();
        $goal->name = '测试目标';
        $goal->target_amount = 100000;
        $goal->target_date = date('Y-m-d', strtotime('+6 months'));
        $goal->status = FinancialGoal::STATUS_IN_PROGRESS;
        $goal->save();

        $statusList = FinancialGoal::getStatusList();
        $this->assertEquals($statusList[FinancialGoal::STATUS_IN_PROGRESS], $goal->getStatusText());
    }

    protected function _after()
    {
        // 清理测试数据
        FinancialGoal::deleteAll();
        Fund::deleteAll();
    }
}

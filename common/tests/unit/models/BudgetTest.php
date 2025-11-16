<?php
namespace common\tests\unit\models;

use common\models\Budget;
use common\models\Fund;
use Codeception\Test\Unit;

class BudgetTest extends Unit
{
    protected function _before()
    {
        // 清空测试数据
        Budget::deleteAll();
        Fund::deleteAll();
    }

    public function testCreateBudget()
    {
        $budget = new Budget();
        $budget->period_type = Budget::PERIOD_MONTH;
        $budget->budget_amount = 50000;
        $budget->start_date = date('Y-m-01');
        $budget->end_date = date('Y-m-t');

        $this->assertTrue($budget->save(), 'Budget should be saved');
        $this->assertNotNull($budget->id);
        $this->assertEquals(0, $budget->actual_amount, 'Actual amount should default to 0');
        $this->assertEquals(Budget::STATUS_ACTIVE, $budget->status);
    }

    public function testValidationPeriodTypeRequired()
    {
        $budget = new Budget();
        $budget->budget_amount = 50000;
        $budget->start_date = date('Y-m-01');
        $budget->end_date = date('Y-m-t');

        $this->assertFalse($budget->save(), 'Should not save without period type');
        $this->assertArrayHasKey('period_type', $budget->errors);
    }

    public function testValidationBudgetAmountRequired()
    {
        $budget = new Budget();
        $budget->period_type = Budget::PERIOD_MONTH;
        $budget->start_date = date('Y-m-01');
        $budget->end_date = date('Y-m-t');

        $this->assertFalse($budget->save(), 'Should not save without budget amount');
        $this->assertArrayHasKey('budget_amount', $budget->errors);
    }

    public function testValidationDateRequired()
    {
        $budget = new Budget();
        $budget->period_type = Budget::PERIOD_MONTH;
        $budget->budget_amount = 50000;

        $this->assertFalse($budget->save(), 'Should not save without dates');
    }

    public function testGetUsageRate()
    {
        $budget = new Budget();
        $budget->period_type = Budget::PERIOD_MONTH;
        $budget->budget_amount = 100000;
        $budget->actual_amount = 60000;
        $budget->start_date = date('Y-m-01');
        $budget->end_date = date('Y-m-t');
        $budget->save();

        $usageRate = $budget->getUsageRate();
        $this->assertEquals(60, $usageRate, 'Usage rate should be 60%');
    }

    public function testGetRemainingBudget()
    {
        $budget = new Budget();
        $budget->period_type = Budget::PERIOD_MONTH;
        $budget->budget_amount = 100000;
        $budget->actual_amount = 60000;
        $budget->start_date = date('Y-m-01');
        $budget->end_date = date('Y-m-t');
        $budget->save();

        $remaining = $budget->getRemainingBudget();
        $this->assertEquals(40000, $remaining);
    }

    public function testIsOverBudget()
    {
        $budget = new Budget();
        $budget->period_type = Budget::PERIOD_MONTH;
        $budget->budget_amount = 100000;
        $budget->actual_amount = 120000; // 超支
        $budget->start_date = date('Y-m-01');
        $budget->end_date = date('Y-m-t');
        $budget->save();

        $this->assertTrue($budget->isOverBudget(), 'Should be over budget');
    }

    public function testGetOverBudgetAmount()
    {
        $budget = new Budget();
        $budget->period_type = Budget::PERIOD_MONTH;
        $budget->budget_amount = 100000;
        $budget->actual_amount = 120000;
        $budget->start_date = date('Y-m-01');
        $budget->end_date = date('Y-m-t');
        $budget->save();

        $overAmount = $budget->getOverBudgetAmount();
        $this->assertEquals(20000, $overAmount);
    }

    public function testGetOverBudgetAmountWhenNotOver()
    {
        $budget = new Budget();
        $budget->period_type = Budget::PERIOD_MONTH;
        $budget->budget_amount = 100000;
        $budget->actual_amount = 80000;
        $budget->start_date = date('Y-m-01');
        $budget->end_date = date('Y-m-t');
        $budget->save();

        $overAmount = $budget->getOverBudgetAmount();
        $this->assertEquals(0, $overAmount, 'Should be 0 when not over budget');
    }

    public function testIsInPeriod()
    {
        $budget = new Budget();
        $budget->period_type = Budget::PERIOD_MONTH;
        $budget->budget_amount = 100000;
        $budget->start_date = date('Y-m-01');
        $budget->end_date = date('Y-m-t');
        $budget->save();

        $this->assertTrue($budget->isInPeriod(), 'Should be in period');
    }

    public function testGetRemainingDays()
    {
        $budget = new Budget();
        $budget->period_type = Budget::PERIOD_MONTH;
        $budget->budget_amount = 100000;
        $budget->start_date = date('Y-m-01');
        $budget->end_date = date('Y-m-d', strtotime('+10 days'));
        $budget->save();

        $days = $budget->getRemainingDays();
        $this->assertGreaterThanOrEqual(9, $days);
        $this->assertLessThanOrEqual(11, $days);
    }

    public function testGetBudgetStatusLabel()
    {
        // 测试正常状态
        $budget = new Budget();
        $budget->period_type = Budget::PERIOD_MONTH;
        $budget->budget_amount = 100000;
        $budget->actual_amount = 60000;
        $budget->start_date = date('Y-m-01');
        $budget->end_date = date('Y-m-t');
        $budget->save();

        $label = $budget->getBudgetStatusLabel();
        $this->assertArrayHasKey('class', $label);
        $this->assertArrayHasKey('text', $label);
        $this->assertEquals('info', $label['class']); // 60% 应该是 info
    }

    public function testGetBudgetStatusLabelOverBudget()
    {
        $budget = new Budget();
        $budget->period_type = Budget::PERIOD_MONTH;
        $budget->budget_amount = 100000;
        $budget->actual_amount = 120000; // 超支
        $budget->start_date = date('Y-m-01');
        $budget->end_date = date('Y-m-t');
        $budget->save();

        $label = $budget->getBudgetStatusLabel();
        $this->assertEquals('danger', $label['class']);
    }

    public function testGetStatusText()
    {
        $budget = new Budget();
        $budget->period_type = Budget::PERIOD_MONTH;
        $budget->budget_amount = 100000;
        $budget->start_date = date('Y-m-01');
        $budget->end_date = date('Y-m-t');
        $budget->status = Budget::STATUS_ACTIVE;
        $budget->save();

        $statusList = Budget::getStatusList();
        $this->assertEquals($statusList[Budget::STATUS_ACTIVE], $budget->getStatusText());
    }

    public function testGetPeriodTypeText()
    {
        $budget = new Budget();
        $budget->period_type = Budget::PERIOD_MONTH;
        $budget->budget_amount = 100000;
        $budget->start_date = date('Y-m-01');
        $budget->end_date = date('Y-m-t');
        $budget->save();

        $typeList = Budget::getPeriodTypeList();
        $this->assertEquals($typeList[Budget::PERIOD_MONTH], $budget->getPeriodTypeText());
    }

    protected function _after()
    {
        // 清理测试数据
        Budget::deleteAll();
        Fund::deleteAll();
    }
}

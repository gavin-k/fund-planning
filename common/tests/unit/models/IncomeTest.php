<?php

namespace common\tests\unit\models;

use Yii;
use common\models\Income;
use common\models\Fund;
use common\models\IncomeDistribution;

/**
 * Income model test
 * 测试收入自动分配算法
 */
class IncomeTest extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // 清空相关表 - 使用 CASCADE 处理外键约束
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%income_distribution}}, {{%income}}, {{%fund}} CASCADE')->execute();
    }

    protected function _after()
    {
        // 清理数据
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%income_distribution}}, {{%income}}, {{%fund}} CASCADE')->execute();
    }

    /**
     * 测试创建收入记录
     */
    public function testCreateIncome()
    {
        $income = new Income([
            'amount' => 10000,
            'source' => '工资',
            'income_date' => '2024-01-01',
        ]);

        $this->assertTrue($income->validate(), 'income should be valid');
    }

    /**
     * 测试验证规则 - 金额必须为正数
     */
    public function testValidationAmountPositive()
    {
        $income = new Income([
            'amount' => -100,
            'income_date' => '2024-01-01',
        ]);

        $this->assertFalse($income->validate(), 'income with negative amount should not be valid');
        $this->assertArrayHasKey('amount', $income->errors, 'income should have error on amount');
    }

    /**
     * 测试收入自动分配到各基金
     * 核心功能测试
     */
    public function testDistributeToFunds()
    {
        // 创建基金
        $fund1 = new Fund([
            'name' => '储蓄基金',
            'allocation_percent' => 25,
            'current_balance' => 0,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund1->save(false);

        $fund2 = new Fund([
            'name' => '教育基金',
            'allocation_percent' => 8,
            'current_balance' => 0,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund2->save(false);

        $fund3 = new Fund([
            'name' => '旅游基金',
            'allocation_percent' => 10,
            'current_balance' => 0,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund3->save(false);

        $fund4 = new Fund([
            'name' => '流动资金',
            'allocation_percent' => 57,
            'current_balance' => 0,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund4->save(false);

        // 创建收入
        $income = new Income([
            'amount' => 100000,
            'source' => '工资',
            'income_date' => '2024-01-01',
        ]);
        $income->save();

        // 验证是否已分配
        $this->assertEquals(1, $income->is_distributed, 'income should be distributed');

        // 验证分配记录数量
        $distributions = IncomeDistribution::find()->where(['income_id' => $income->id])->all();
        $this->assertEquals(4, count($distributions), 'should have 4 distribution records');

        // 验证各基金余额
        $fund1->refresh();
        $fund2->refresh();
        $fund3->refresh();
        $fund4->refresh();

        $this->assertEquals(25000, $fund1->current_balance, 'fund1 balance should be 25000');
        $this->assertEquals(8000, $fund2->current_balance, 'fund2 balance should be 8000');
        $this->assertEquals(10000, $fund3->current_balance, 'fund3 balance should be 10000');
        $this->assertEquals(57000, $fund4->current_balance, 'fund4 balance should be 57000');

        // 验证总金额
        $totalDistributed = $fund1->current_balance + $fund2->current_balance +
                           $fund3->current_balance + $fund4->current_balance;
        $this->assertEquals(100000, $totalDistributed, 'total distributed should equal income amount');
    }

    /**
     * 测试收入分配 - 没有启用的基金
     */
    public function testDistributeToFundsNoActiveFunds()
    {
        // 创建禁用的基金
        $fund = new Fund([
            'name' => '禁用基金',
            'allocation_percent' => 100,
            'current_balance' => 0,
            'status' => Fund::STATUS_INACTIVE,
        ]);
        $fund->save(false);

        // 创建收入
        $income = new Income([
            'amount' => 10000,
            'source' => '测试',
            'income_date' => '2024-01-01',
        ]);
        $income->save();

        // 验证未分配
        $this->assertEquals(0, $income->is_distributed, 'income should not be distributed');
    }

    /**
     * 测试收入分配 - 总比例为0
     */
    public function testDistributeToFundsZeroTotalPercent()
    {
        // 创建分配比例为0的基金
        $fund = new Fund([
            'name' => '零比例基金',
            'allocation_percent' => 0,
            'current_balance' => 0,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund->save(false);

        // 创建收入
        $income = new Income([
            'amount' => 10000,
            'source' => '测试',
            'income_date' => '2024-01-01',
        ]);
        $income->save();

        // 验证未分配
        $this->assertEquals(0, $income->is_distributed, 'income should not be distributed');
    }

    /**
     * 测试收入分配 - 精度问题处理
     * 最后一个基金应该得到剩余金额
     */
    public function testDistributeToFundsPrecision()
    {
        // 创建会产生精度问题的比例
        $fund1 = new Fund([
            'name' => '基金1',
            'allocation_percent' => 33.33,
            'current_balance' => 0,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund1->save(false);

        $fund2 = new Fund([
            'name' => '基金2',
            'allocation_percent' => 33.33,
            'current_balance' => 0,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund2->save(false);

        $fund3 = new Fund([
            'name' => '基金3',
            'allocation_percent' => 33.34,
            'current_balance' => 0,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund3->save(false);

        // 创建收入
        $income = new Income([
            'amount' => 100,
            'source' => '测试精度',
            'income_date' => '2024-01-01',
        ]);
        $income->save();

        // 验证已分配
        $this->assertEquals(1, $income->is_distributed, 'income should be distributed');

        // 验证总金额（应该正好等于100，没有精度损失）
        $fund1->refresh();
        $fund2->refresh();
        $fund3->refresh();

        $totalDistributed = $fund1->current_balance + $fund2->current_balance + $fund3->current_balance;
        $this->assertEquals(100, $totalDistributed, 'total distributed should equal income amount exactly');
    }

    /**
     * 测试获取分配详情
     */
    public function testGetDistributionDetails()
    {
        // 创建基金
        $fund = new Fund([
            'name' => '测试基金',
            'allocation_percent' => 100,
            'current_balance' => 0,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund->save(false);

        // 创建收入
        $income = new Income([
            'amount' => 5000,
            'source' => '测试',
            'income_date' => '2024-01-01',
        ]);
        $income->save();

        // 获取分配详情
        $details = $income->getDistributionDetails();

        $this->assertEquals(1, count($details), 'should have distribution details');
        $this->assertTrue(isset($details[0]['fund_name']), 'detail should have fund_name');
        $this->assertTrue(isset($details[0]['amount']), 'detail should have amount');
        $this->assertTrue(isset($details[0]['percent']), 'detail should have percent');
    }

    /**
     * 测试重复分配应该失败
     */
    public function testDistributeToFundsTwiceShouldFail()
    {
        // 创建基金
        $fund = new Fund([
            'name' => '测试基金',
            'allocation_percent' => 100,
            'current_balance' => 0,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund->save(false);

        // 创建并分配收入
        $income = new Income([
            'amount' => 5000,
            'source' => '测试',
            'income_date' => '2024-01-01',
        ]);
        $income->save();

        $this->assertEquals(1, $income->is_distributed, 'income should be distributed');

        // 尝试再次分配
        $result = $income->distributeToFunds();

        $this->assertFalse($result, 'second distribution should fail');
        $this->assertArrayHasKey('is_distributed', $income->errors, 'should have error');
    }
}

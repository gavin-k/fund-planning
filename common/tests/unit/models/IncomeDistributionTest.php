<?php

namespace common\tests\unit\models;

use Yii;
use common\models\IncomeDistribution;
use common\models\Income;
use common\models\Fund;

/**
 * IncomeDistribution 模型单元测试
 */
class IncomeDistributionTest extends \Codeception\Test\Unit
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
     * 测试创建收入分配记录
     */
    public function testCreateIncomeDistribution()
    {
        // 创建基金
        $fund = new Fund([
            'name' => '储蓄基金',
            'allocation_percent' => 25,
            'current_balance' => 0,
        ]);
        $fund->save();

        // 创建收入（不自动分配）
        $income = new Income([
            'amount' => 1000,
            'source' => '工资',
            'income_date' => date('Y-m-d'),
            'is_distributed' => 0,
        ]);
        $income->save(false);

        // 手动创建分配记录
        $distribution = new IncomeDistribution([
            'income_id' => $income->id,
            'fund_id' => $fund->id,
            'amount' => 250,
            'percent' => 25,
        ]);

        $this->assertTrue($distribution->validate(), 'distribution should be valid');
        $this->assertTrue($distribution->save(), 'distribution should be saved');
        $this->assertNotNull($distribution->id, 'distribution should have id');
        $this->assertEquals(250, $distribution->amount, 'amount should be 250');
        $this->assertEquals(25, $distribution->percent, 'percent should be 25');
    }

    /**
     * 测试验证规则 - 收入ID必填
     */
    public function testValidationIncomeIdRequired()
    {
        $distribution = new IncomeDistribution([
            'fund_id' => 1,
            'amount' => 100,
            'percent' => 10,
        ]);

        $this->assertFalse($distribution->validate(), 'distribution should not be valid');
        $this->assertArrayHasKey('income_id', $distribution->errors, 'distribution should have error on income_id');
    }

    /**
     * 测试验证规则 - 基金ID必填
     */
    public function testValidationFundIdRequired()
    {
        $distribution = new IncomeDistribution([
            'income_id' => 1,
            'amount' => 100,
            'percent' => 10,
        ]);

        $this->assertFalse($distribution->validate(), 'distribution should not be valid');
        $this->assertArrayHasKey('fund_id', $distribution->errors, 'distribution should have error on fund_id');
    }

    /**
     * 测试验证规则 - 金额必填
     */
    public function testValidationAmountRequired()
    {
        $distribution = new IncomeDistribution([
            'income_id' => 1,
            'fund_id' => 1,
            'percent' => 10,
        ]);

        $this->assertFalse($distribution->validate(), 'distribution should not be valid');
        $this->assertArrayHasKey('amount', $distribution->errors, 'distribution should have error on amount');
    }

    /**
     * 测试验证规则 - 比例必填
     */
    public function testValidationPercentRequired()
    {
        $distribution = new IncomeDistribution([
            'income_id' => 1,
            'fund_id' => 1,
            'amount' => 100,
        ]);

        $this->assertFalse($distribution->validate(), 'distribution should not be valid');
        $this->assertArrayHasKey('percent', $distribution->errors, 'distribution should have error on percent');
    }

    /**
     * 测试获取收入关联
     */
    public function testGetIncome()
    {
        // 创建基金
        $fund = new Fund([
            'name' => '储蓄基金',
            'allocation_percent' => 25,
            'current_balance' => 0,
        ]);
        $fund->save();

        // 创建收入
        $income = new Income([
            'amount' => 1000,
            'source' => '工资',
            'income_date' => date('Y-m-d'),
            'is_distributed' => 0,
        ]);
        $income->save(false);

        // 创建分配记录
        $distribution = new IncomeDistribution([
            'income_id' => $income->id,
            'fund_id' => $fund->id,
            'amount' => 250,
            'percent' => 25,
        ]);
        $distribution->save();

        // 测试关联
        $relatedIncome = $distribution->income;
        $this->assertNotNull($relatedIncome, 'income should not be null');
        $this->assertEquals($income->id, $relatedIncome->id, 'income id should match');
        $this->assertEquals(1000, $relatedIncome->amount, 'income amount should be 1000');
    }

    /**
     * 测试获取基金关联
     */
    public function testGetFund()
    {
        // 创建基金
        $fund = new Fund([
            'name' => '储蓄基金',
            'allocation_percent' => 25,
            'current_balance' => 0,
        ]);
        $fund->save();

        // 创建收入
        $income = new Income([
            'amount' => 1000,
            'source' => '工资',
            'income_date' => date('Y-m-d'),
            'is_distributed' => 0,
        ]);
        $income->save(false);

        // 创建分配记录
        $distribution = new IncomeDistribution([
            'income_id' => $income->id,
            'fund_id' => $fund->id,
            'amount' => 250,
            'percent' => 25,
        ]);
        $distribution->save();

        // 测试关联
        $relatedFund = $distribution->fund;
        $this->assertNotNull($relatedFund, 'fund should not be null');
        $this->assertEquals($fund->id, $relatedFund->id, 'fund id should match');
        $this->assertEquals('储蓄基金', $relatedFund->name, 'fund name should match');
    }

    /**
     * 测试 created_at 自动设置
     */
    public function testCreatedAtAutoSet()
    {
        // 创建基金
        $fund = new Fund([
            'name' => '储蓄基金',
            'allocation_percent' => 25,
            'current_balance' => 0,
        ]);
        $fund->save();

        // 创建收入
        $income = new Income([
            'amount' => 1000,
            'source' => '工资',
            'income_date' => date('Y-m-d'),
            'is_distributed' => 0,
        ]);
        $income->save(false);

        // 创建分配记录
        $distribution = new IncomeDistribution([
            'income_id' => $income->id,
            'fund_id' => $fund->id,
            'amount' => 250,
            'percent' => 25,
        ]);

        $timeBefore = time();
        $this->assertTrue($distribution->save(), 'distribution should be saved');
        $timeAfter = time();

        $this->assertNotNull($distribution->created_at, 'created_at should not be null');
        $this->assertGreaterThanOrEqual($timeBefore, $distribution->created_at, 'created_at should be >= time before save');
        $this->assertLessThanOrEqual($timeAfter, $distribution->created_at, 'created_at should be <= time after save');
    }

    /**
     * 测试验证规则 - 无效的收入ID
     */
    public function testValidationInvalidIncomeId()
    {
        // 创建基金
        $fund = new Fund([
            'name' => '储蓄基金',
            'allocation_percent' => 25,
            'current_balance' => 0,
        ]);
        $fund->save();

        // 使用不存在的收入ID
        $distribution = new IncomeDistribution([
            'income_id' => 99999,
            'fund_id' => $fund->id,
            'amount' => 250,
            'percent' => 25,
        ]);

        $this->assertFalse($distribution->validate(), 'distribution should not be valid');
        $this->assertArrayHasKey('income_id', $distribution->errors, 'distribution should have error on income_id');
    }

    /**
     * 测试验证规则 - 无效的基金ID
     */
    public function testValidationInvalidFundId()
    {
        // 创建收入
        $income = new Income([
            'amount' => 1000,
            'source' => '工资',
            'income_date' => date('Y-m-d'),
            'is_distributed' => 0,
        ]);
        $income->save(false);

        // 使用不存在的基金ID
        $distribution = new IncomeDistribution([
            'income_id' => $income->id,
            'fund_id' => 99999,
            'amount' => 250,
            'percent' => 25,
        ]);

        $this->assertFalse($distribution->validate(), 'distribution should not be valid');
        $this->assertArrayHasKey('fund_id', $distribution->errors, 'distribution should have error on fund_id');
    }
}

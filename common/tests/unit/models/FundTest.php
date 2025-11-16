<?php

namespace common\tests\unit\models;

use Yii;
use common\models\Fund;

/**
 * Fund model test
 */
class FundTest extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // 清空相关表 - 使用 CASCADE 处理外键约束
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%fund}} CASCADE')->execute();
    }

    protected function _after()
    {
        // 清理数据
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%fund}} CASCADE')->execute();
    }

    /**
     * 测试创建基金
     */
    public function testCreateFund()
    {
        $fund = new Fund([
            'name' => '储蓄基金',
            'allocation_percent' => 25,
            'current_balance' => 0,
            'description' => '用于储蓄',
            'status' => Fund::STATUS_ACTIVE,
        ]);

        $this->assertTrue($fund->validate(), 'fund should be valid');
        $this->assertTrue($fund->save(), 'fund should be saved');
        $this->assertNotNull($fund->id, 'fund should have id');
        $this->assertEquals('储蓄基金', $fund->name, 'fund name should be correct');
    }

    /**
     * 测试验证规则 - 名称必填
     */
    public function testValidationNameRequired()
    {
        $fund = new Fund([
            'allocation_percent' => 25,
        ]);

        $this->assertFalse($fund->validate(), 'fund should not be valid');
        $this->assertArrayHasKey('name', $fund->errors, 'fund should have error on name');
    }

    /**
     * 测试验证规则 - 分配比例范围
     */
    public function testValidationAllocationPercent()
    {
        // 测试负数
        $fund = new Fund([
            'name' => '测试基金',
            'allocation_percent' => -10,
        ]);
        $this->assertFalse($fund->validate(), 'fund with negative percent should not be valid');
        $this->assertArrayHasKey('allocation_percent', $fund->errors, 'fund should have error on allocation_percent');

        // 测试超过100
        $fund = new Fund([
            'name' => '测试基金',
            'allocation_percent' => 150,
        ]);
        $this->assertFalse($fund->validate(), 'fund with percent > 100 should not be valid');
        $this->assertArrayHasKey('allocation_percent', $fund->errors, 'fund should have error on allocation_percent');

        // 测试有效范围
        $fund = new Fund([
            'name' => '测试基金',
            'allocation_percent' => 50,
        ]);
        $this->assertTrue($fund->validate(['allocation_percent']), 'fund with valid percent should be valid');
    }

    /**
     * 测试增加余额
     */
    public function testAddBalance()
    {
        $fund = new Fund([
            'name' => '测试基金',
            'allocation_percent' => 25,
            'current_balance' => 1000,
        ]);
        $fund->save(false);

        $result = $fund->addBalance(500);

        $this->assertTrue($result, 'should return true');
        $this->assertEquals(1500, $fund->current_balance, 'balance should be increased');
    }

    /**
     * 测试减少余额 - 成功
     */
    public function testReduceBalanceSuccess()
    {
        $fund = new Fund([
            'name' => '测试基金',
            'allocation_percent' => 25,
            'current_balance' => 1000,
        ]);
        $fund->save(false);

        $result = $fund->reduceBalance(300);

        $this->assertTrue($result, 'should return true');
        $this->assertEquals(700, $fund->current_balance, 'balance should be reduced');
    }

    /**
     * 测试减少余额 - 余额不足
     */
    public function testReduceBalanceInsufficientFunds()
    {
        $fund = new Fund([
            'name' => '测试基金',
            'allocation_percent' => 25,
            'current_balance' => 500,
        ]);
        $fund->save(false);

        $result = $fund->reduceBalance(1000);

        $this->assertFalse($result, 'should return false');
        $this->assertArrayHasKey('current_balance', $fund->errors, 'should have error');
        $this->assertEquals(500, $fund->current_balance, 'balance should not change');
    }

    /**
     * 测试计算可用余额
     */
    public function testGetAvailableBalance()
    {
        $fund = new Fund([
            'name' => '测试基金',
            'allocation_percent' => 25,
            'current_balance' => 1000,
        ]);
        $fund->save(false);

        // 没有投资时，可用余额等于当前余额
        $availableBalance = $fund->getAvailableBalance();
        $this->assertEquals(1000, $availableBalance, 'available balance should equal current balance');
    }

    /**
     * 测试获取状态文本
     */
    public function testGetStatusText()
    {
        $fund = new Fund([
            'name' => '测试基金',
            'allocation_percent' => 25,
            'status' => Fund::STATUS_ACTIVE,
        ]);

        $this->assertEquals('启用', $fund->getStatusText(), 'status text should be correct');

        $fund->status = Fund::STATUS_INACTIVE;
        $this->assertEquals('禁用', $fund->getStatusText(), 'inactive status text should be correct');
    }

    /**
     * 测试名称唯一性
     */
    public function testNameUniqueness()
    {
        $fund1 = new Fund([
            'name' => '唯一基金',
            'allocation_percent' => 25,
        ]);
        $fund1->save(false);

        $fund2 = new Fund([
            'name' => '唯一基金',
            'allocation_percent' => 30,
        ]);

        $this->assertFalse($fund2->validate(), 'fund with duplicate name should not be valid');
        $this->assertArrayHasKey('name', $fund2->errors, 'fund should have error on name');
    }
}

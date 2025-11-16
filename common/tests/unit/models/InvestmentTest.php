<?php

namespace common\tests\unit\models;

use Yii;
use common\models\Investment;
use common\models\Fund;
use common\models\InvestmentProduct;

/**
 * Investment model test
 * 测试投资和赎回功能
 */
class InvestmentTest extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // 清空相关表 - 使用 CASCADE 处理外键约束
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%investment}}, {{%investment_product}}, {{%fund}} CASCADE')->execute();
    }

    protected function _after()
    {
        // 清理数据
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%investment}}, {{%investment_product}}, {{%fund}} CASCADE')->execute();
    }

    /**
     * 准备测试数据
     */
    protected function prepareTestData()
    {
        // 创建基金
        $fund = new Fund([
            'name' => '测试基金',
            'allocation_percent' => 50,
            'current_balance' => 10000,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund->save(false);

        // 创建产品
        $product = new InvestmentProduct([
            'name' => '测试产品',
            'type' => InvestmentProduct::TYPE_ALIPAY,
            'platform' => '支付宝',
            'current_amount' => 0,
            'status' => InvestmentProduct::STATUS_ACTIVE,
        ]);
        $product->save(false);

        return ['fund' => $fund, 'product' => $product];
    }

    /**
     * 测试创建投资
     */
    public function testCreateInvestment()
    {
        $data = $this->prepareTestData();

        $investment = new Investment([
            'fund_id' => $data['fund']->id,
            'product_id' => $data['product']->id,
            'amount' => 5000,
            'investment_date' => '2024-01-01',
        ]);

        $this->assertTrue($investment->validate(), 'investment should be valid');
        $this->assertTrue($investment->save(), 'investment should be saved');
        $this->assertEquals(Investment::STATUS_ACTIVE, $investment->status, 'investment should be active');
    }

    /**
     * 测试投资金额超过可用余额应该失败
     */
    public function testInvestmentExceedsAvailableBalance()
    {
        $data = $this->prepareTestData();

        // 尝试投资超过余额的金额
        $investment = new Investment([
            'fund_id' => $data['fund']->id,
            'product_id' => $data['product']->id,
            'amount' => 15000, // 超过基金的10000余额
            'investment_date' => '2024-01-01',
        ]);

        $this->assertFalse($investment->save(), 'investment should not be saved');
        $this->assertArrayHasKey('amount', $investment->errors, 'investment should have error on amount');
    }

    /**
     * 测试计算可用余额
     * 当有投资时，可用余额 = 当前余额 - 已投资金额
     */
    public function testAvailableBalanceWithInvestments()
    {
        $data = $this->prepareTestData();

        // 创建投资
        $investment = new Investment([
            'fund_id' => $data['fund']->id,
            'product_id' => $data['product']->id,
            'amount' => 3000,
            'investment_date' => '2024-01-01',
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment->save();

        $data['fund']->refresh();

        // 计算可用余额
        $availableBalance = $data['fund']->getAvailableBalance();

        $this->assertEquals(10000, $data['fund']->current_balance, 'current balance should still be 10000');
        $this->assertEquals(7000, $availableBalance, 'available balance should be 7000');
    }

    /**
     * 测试赎回投资
     * 赎回后资金应返回基金
     */
    public function testWithdrawInvestment()
    {
        $data = $this->prepareTestData();

        // 创建投资
        $investment = new Investment([
            'fund_id' => $data['fund']->id,
            'product_id' => $data['product']->id,
            'amount' => 5000,
            'investment_date' => '2024-01-01',
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment->save();

        $initialBalance = $data['fund']->current_balance;

        // 赎回投资
        $result = $investment->withdraw();

        $this->assertTrue($result, 'withdraw should succeed');
        $this->assertEquals(Investment::STATUS_WITHDRAWN, $investment->status, 'investment status should be withdrawn');

        // 验证余额变化
        $data['fund']->refresh();
        $this->assertEquals($initialBalance + 5000, $data['fund']->current_balance, 'fund balance should increase by investment amount');
    }

    /**
     * 测试重复赎回应该失败
     */
    public function testWithdrawInvestmentTwiceShouldFail()
    {
        $data = $this->prepareTestData();

        // 创建投资
        $investment = new Investment([
            'fund_id' => $data['fund']->id,
            'product_id' => $data['product']->id,
            'amount' => 5000,
            'investment_date' => '2024-01-01',
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment->save();

        // 第一次赎回
        $investment->withdraw();

        // 尝试第二次赎回
        $result = $investment->withdraw();

        $this->assertFalse($result, 'second withdraw should fail');
        $this->assertArrayHasKey('status', $investment->errors, 'should have error on status');
    }

    /**
     * 测试投资后产品总额更新
     */
    public function testProductAmountUpdatesAfterInvestment()
    {
        $data = $this->prepareTestData();

        $this->assertEquals(0, $data['product']->current_amount, 'product initial amount should be 0');

        // 创建投资
        $investment = new Investment([
            'fund_id' => $data['fund']->id,
            'product_id' => $data['product']->id,
            'amount' => 5000,
            'investment_date' => '2024-01-01',
        ]);
        $investment->save();

        // 产品总额应该更新
        $data['product']->refresh();
        $this->assertEquals(5000, $data['product']->current_amount, 'product amount should be updated');
    }

    /**
     * 测试多笔投资的总额计算
     */
    public function testMultipleInvestments()
    {
        $data = $this->prepareTestData();

        // 创建第一笔投资
        $investment1 = new Investment([
            'fund_id' => $data['fund']->id,
            'product_id' => $data['product']->id,
            'amount' => 3000,
            'investment_date' => '2024-01-01',
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment1->save();

        // 创建第二笔投资
        $investment2 = new Investment([
            'fund_id' => $data['fund']->id,
            'product_id' => $data['product']->id,
            'amount' => 2000,
            'investment_date' => '2024-01-02',
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment2->save();

        // 验证基金已投资金额
        $data['fund']->refresh();
        $investedAmount = $data['fund']->getInvestedAmount();
        $this->assertEquals(5000, $investedAmount, 'invested amount should be 5000');

        // 验证可用余额
        $availableBalance = $data['fund']->getAvailableBalance();
        $this->assertEquals(5000, $availableBalance, 'available balance should be 5000');
    }

    /**
     * 测试获取状态文本
     */
    public function testGetStatusText()
    {
        $data = $this->prepareTestData();

        $investment = new Investment([
            'fund_id' => $data['fund']->id,
            'product_id' => $data['product']->id,
            'amount' => 5000,
            'investment_date' => '2024-01-01',
            'status' => Investment::STATUS_ACTIVE,
        ]);

        $this->assertEquals('生效中', $investment->getStatusText(), 'active status text should be correct');

        $investment->status = Investment::STATUS_WITHDRAWN;
        $this->assertEquals('已赎回', $investment->getStatusText(), 'withdrawn status text should be correct');
    }

    /**
     * 测试验证规则 - 金额必须为正数
     */
    public function testValidationAmountPositive()
    {
        $data = $this->prepareTestData();

        $investment = new Investment([
            'fund_id' => $data['fund']->id,
            'product_id' => $data['product']->id,
            'amount' => -100,
            'investment_date' => '2024-01-01',
        ]);

        $this->assertFalse($investment->validate(), 'investment with negative amount should not be valid');
        $this->assertArrayHasKey('amount', $investment->errors, 'investment should have error on amount');
    }

    /**
     * 测试外键验证 - 无效的基金ID
     */
    public function testValidationInvalidFundId()
    {
        $data = $this->prepareTestData();

        $investment = new Investment([
            'fund_id' => 99999, // 不存在的基金ID
            'product_id' => $data['product']->id,
            'amount' => 1000,
            'investment_date' => '2024-01-01',
        ]);

        $this->assertFalse($investment->validate(), 'investment with invalid fund_id should not be valid');
        $this->assertArrayHasKey('fund_id', $investment->errors, 'investment should have error on fund_id');
    }
}

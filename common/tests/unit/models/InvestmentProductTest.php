<?php

namespace common\tests\unit\models;

use Yii;
use common\models\InvestmentProduct;
use common\models\Investment;
use common\models\Fund;

/**
 * InvestmentProduct 模型单元测试
 */
class InvestmentProductTest extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // 清空相关表 - 使用 CASCADE 处理外键约束
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_record}}, {{%investment}}, {{%investment_product}}, {{%fund}} CASCADE')->execute();
    }

    protected function _after()
    {
        // 清理数据
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_record}}, {{%investment}}, {{%investment_product}}, {{%fund}} CASCADE')->execute();
    }

    /**
     * 测试创建理财产品
     */
    public function testCreateProduct()
    {
        $product = new InvestmentProduct([
            'name' => '余额宝',
            'type' => InvestmentProduct::TYPE_ALIPAY,
            'platform' => '支付宝',
            'current_amount' => 0,
            'description' => '支付宝余额宝',
            'status' => InvestmentProduct::STATUS_ACTIVE,
        ]);

        $this->assertTrue($product->validate(), 'product should be valid');
        $this->assertTrue($product->save(), 'product should be saved');
        $this->assertNotNull($product->id, 'product should have id');
        $this->assertEquals('余额宝', $product->name, 'product name should be correct');
        $this->assertEquals(InvestmentProduct::TYPE_ALIPAY, $product->type, 'product type should be correct');
    }

    /**
     * 测试验证规则 - 名称必填
     */
    public function testValidationNameRequired()
    {
        $product = new InvestmentProduct([
            'type' => InvestmentProduct::TYPE_BANK,
        ]);

        $this->assertFalse($product->validate(), 'product should not be valid');
        $this->assertArrayHasKey('name', $product->errors, 'product should have error on name');
    }

    /**
     * 测试验证规则 - 类型必填
     */
    public function testValidationTypeRequired()
    {
        $product = new InvestmentProduct([
            'name' => '测试产品',
        ]);

        $this->assertFalse($product->validate(), 'product should not be valid');
        $this->assertArrayHasKey('type', $product->errors, 'product should have error on type');
    }

    /**
     * 测试验证规则 - 类型必须在范围内
     */
    public function testValidationTypeInRange()
    {
        $product = new InvestmentProduct([
            'name' => '测试产品',
            'type' => 'invalid_type',
        ]);

        $this->assertFalse($product->validate(), 'product should not be valid');
        $this->assertArrayHasKey('type', $product->errors, 'product should have error on type');
    }

    /**
     * 测试验证规则 - 投资总额非负
     */
    public function testValidationCurrentAmountNonNegative()
    {
        $product = new InvestmentProduct([
            'name' => '测试产品',
            'type' => InvestmentProduct::TYPE_BANK,
            'current_amount' => -100,
        ]);

        $this->assertFalse($product->validate(), 'product should not be valid');
        $this->assertArrayHasKey('current_amount', $product->errors, 'product should have error on current_amount');
    }

    /**
     * 测试获取状态文本
     */
    public function testGetStatusText()
    {
        $product = new InvestmentProduct([
            'name' => '测试产品',
            'type' => InvestmentProduct::TYPE_BANK,
            'status' => InvestmentProduct::STATUS_ACTIVE,
        ]);
        $product->save();

        $this->assertEquals('使用中', $product->getStatusText(), 'status text should be correct');

        $product->status = InvestmentProduct::STATUS_INACTIVE;
        $this->assertEquals('已停用', $product->getStatusText(), 'status text should be correct');
    }

    /**
     * 测试获取类型文本
     */
    public function testGetTypeText()
    {
        $product = new InvestmentProduct([
            'name' => '余额宝',
            'type' => InvestmentProduct::TYPE_ALIPAY,
        ]);
        $product->save();

        $this->assertEquals('支付宝', $product->getTypeText(), 'type text should be correct');

        $product->type = InvestmentProduct::TYPE_BANK;
        $this->assertEquals('银行理财', $product->getTypeText(), 'type text should be correct');

        $product->type = InvestmentProduct::TYPE_STOCK;
        $this->assertEquals('股票', $product->getTypeText(), 'type text should be correct');

        $product->type = InvestmentProduct::TYPE_FUND;
        $this->assertEquals('基金', $product->getTypeText(), 'type text should be correct');
    }

    /**
     * 测试计算当前投资总额
     */
    public function testCalculateCurrentAmount()
    {
        // 创建产品
        $product = new InvestmentProduct([
            'name' => '测试产品',
            'type' => InvestmentProduct::TYPE_BANK,
            'current_amount' => 0,
        ]);
        $product->save();

        // 创建基金
        $fund1 = new Fund([
            'name' => '基金1',
            'allocation_percent' => 50,
            'current_balance' => 10000,
        ]);
        $fund1->save();

        $fund2 = new Fund([
            'name' => '基金2',
            'allocation_percent' => 50,
            'current_balance' => 5000,
        ]);
        $fund2->save();

        // 创建投资
        $investment1 = new Investment([
            'fund_id' => $fund1->id,
            'product_id' => $product->id,
            'amount' => 3000,
            'investment_date' => date('Y-m-d'),
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment1->save(false);

        $investment2 = new Investment([
            'fund_id' => $fund2->id,
            'product_id' => $product->id,
            'amount' => 2000,
            'investment_date' => date('Y-m-d'),
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment2->save(false);

        // 计算总额
        $calculated = $product->calculateCurrentAmount();
        $this->assertEquals(5000, $calculated, 'calculated amount should be 5000');
    }

    /**
     * 测试更新当前投资总额
     */
    public function testUpdateCurrentAmount()
    {
        // 创建产品
        $product = new InvestmentProduct([
            'name' => '测试产品',
            'type' => InvestmentProduct::TYPE_BANK,
            'current_amount' => 0,
        ]);
        $product->save();

        // 创建基金
        $fund = new Fund([
            'name' => '基金1',
            'allocation_percent' => 100,
            'current_balance' => 10000,
        ]);
        $fund->save();

        // 创建投资
        $investment = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'amount' => 3000,
            'investment_date' => date('Y-m-d'),
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment->save(false);

        // 更新总额
        $this->assertTrue($product->updateCurrentAmount(), 'update should succeed');
        $product->refresh();
        $this->assertEquals(3000, $product->current_amount, 'current amount should be updated to 3000');
    }

    /**
     * 测试更新投资总额 - 包含已赎回的投资
     */
    public function testCalculateCurrentAmountExcludesWithdrawn()
    {
        // 创建产品
        $product = new InvestmentProduct([
            'name' => '测试产品',
            'type' => InvestmentProduct::TYPE_BANK,
            'current_amount' => 0,
        ]);
        $product->save();

        // 创建基金
        $fund = new Fund([
            'name' => '基金1',
            'allocation_percent' => 100,
            'current_balance' => 10000,
        ]);
        $fund->save();

        // 创建生效投资
        $investment1 = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'amount' => 3000,
            'investment_date' => date('Y-m-d'),
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment1->save(false);

        // 创建已赎回投资
        $investment2 = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'amount' => 2000,
            'investment_date' => date('Y-m-d'),
            'status' => Investment::STATUS_WITHDRAWN,
        ]);
        $investment2->save(false);

        // 计算总额应该只包含生效的投资
        $calculated = $product->calculateCurrentAmount();
        $this->assertEquals(3000, $calculated, 'calculated amount should only include active investments');
    }

    /**
     * 测试获取生效中的投资
     */
    public function testGetActiveInvestments()
    {
        // 创建产品
        $product = new InvestmentProduct([
            'name' => '测试产品',
            'type' => InvestmentProduct::TYPE_BANK,
        ]);
        $product->save();

        // 创建基金
        $fund = new Fund([
            'name' => '基金1',
            'allocation_percent' => 100,
            'current_balance' => 10000,
        ]);
        $fund->save();

        // 创建2个生效投资和1个已赎回投资
        $investment1 = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'amount' => 1000,
            'investment_date' => date('Y-m-d'),
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment1->save(false);

        $investment2 = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'amount' => 2000,
            'investment_date' => date('Y-m-d'),
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment2->save(false);

        $investment3 = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'amount' => 3000,
            'investment_date' => date('Y-m-d'),
            'status' => Investment::STATUS_WITHDRAWN,
        ]);
        $investment3->save(false);

        // 获取生效投资
        $activeInvestments = $product->getActiveInvestments()->all();
        $this->assertCount(2, $activeInvestments, 'should have 2 active investments');
    }

    /**
     * 测试默认状态
     */
    public function testDefaultStatus()
    {
        $product = new InvestmentProduct([
            'name' => '测试产品',
            'type' => InvestmentProduct::TYPE_BANK,
        ]);
        $product->save();

        $this->assertEquals(InvestmentProduct::STATUS_ACTIVE, $product->status, 'default status should be active');
    }
}

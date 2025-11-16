<?php

namespace common\tests\unit\models;

use Yii;
use common\models\ReturnDistribution;
use common\models\ReturnRecord;
use common\models\InvestmentProduct;
use common\models\Fund;

/**
 * ReturnDistribution 模型单元测试
 */
class ReturnDistributionTest extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // 清空相关表 - 使用 CASCADE 处理外键约束
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_distribution}}, {{%return_record}}, {{%investment_product}}, {{%fund}} CASCADE')->execute();
    }

    protected function _after()
    {
        // 清理数据
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_distribution}}, {{%return_record}}, {{%investment_product}}, {{%fund}} CASCADE')->execute();
    }

    /**
     * 测试创建收益分配记录
     */
    public function testCreateReturnDistribution()
    {
        // 创建基金
        $fund = new Fund([
            'name' => '储蓄基金',
            'allocation_percent' => 50,
            'current_balance' => 0,
        ]);
        $fund->save();

        // 创建产品
        $product = new InvestmentProduct([
            'name' => '余额宝',
            'type' => InvestmentProduct::TYPE_ALIPAY,
            'current_amount' => 0,
        ]);
        $product->save();

        // 创建收益记录（不自动分配）
        $returnRecord = new ReturnRecord([
            'product_id' => $product->id,
            'total_amount' => 100,
            'return_date' => date('Y-m-d'),
            'is_distributed' => 0,
        ]);
        $returnRecord->save(false);

        // 手动创建分配记录
        $distribution = new ReturnDistribution([
            'return_id' => $returnRecord->id,
            'fund_id' => $fund->id,
            'amount' => 50,
            'percent' => 50,
        ]);

        $this->assertTrue($distribution->validate(), 'distribution should be valid');
        $this->assertTrue($distribution->save(), 'distribution should be saved');
        $this->assertNotNull($distribution->id, 'distribution should have id');
        $this->assertEquals(50, $distribution->amount, 'amount should be 50');
        $this->assertEquals(50, $distribution->percent, 'percent should be 50');
    }

    /**
     * 测试验证规则 - 收益ID必填
     */
    public function testValidationReturnIdRequired()
    {
        $distribution = new ReturnDistribution([
            'fund_id' => 1,
            'amount' => 100,
            'percent' => 50,
        ]);

        $this->assertFalse($distribution->validate(), 'distribution should not be valid');
        $this->assertArrayHasKey('return_id', $distribution->errors, 'distribution should have error on return_id');
    }

    /**
     * 测试验证规则 - 基金ID必填
     */
    public function testValidationFundIdRequired()
    {
        $distribution = new ReturnDistribution([
            'return_id' => 1,
            'amount' => 100,
            'percent' => 50,
        ]);

        $this->assertFalse($distribution->validate(), 'distribution should not be valid');
        $this->assertArrayHasKey('fund_id', $distribution->errors, 'distribution should have error on fund_id');
    }

    /**
     * 测试验证规则 - 金额必填
     */
    public function testValidationAmountRequired()
    {
        $distribution = new ReturnDistribution([
            'return_id' => 1,
            'fund_id' => 1,
            'percent' => 50,
        ]);

        $this->assertFalse($distribution->validate(), 'distribution should not be valid');
        $this->assertArrayHasKey('amount', $distribution->errors, 'distribution should have error on amount');
    }

    /**
     * 测试验证规则 - 比例必填
     */
    public function testValidationPercentRequired()
    {
        $distribution = new ReturnDistribution([
            'return_id' => 1,
            'fund_id' => 1,
            'amount' => 100,
        ]);

        $this->assertFalse($distribution->validate(), 'distribution should not be valid');
        $this->assertArrayHasKey('percent', $distribution->errors, 'distribution should have error on percent');
    }

    /**
     * 测试获取收益记录关联
     */
    public function testGetReturn()
    {
        // 创建基金
        $fund = new Fund([
            'name' => '储蓄基金',
            'allocation_percent' => 50,
            'current_balance' => 0,
        ]);
        $fund->save();

        // 创建产品
        $product = new InvestmentProduct([
            'name' => '余额宝',
            'type' => InvestmentProduct::TYPE_ALIPAY,
            'current_amount' => 0,
        ]);
        $product->save();

        // 创建收益记录
        $returnRecord = new ReturnRecord([
            'product_id' => $product->id,
            'total_amount' => 100,
            'return_date' => date('Y-m-d'),
            'is_distributed' => 0,
        ]);
        $returnRecord->save(false);

        // 创建分配记录
        $distribution = new ReturnDistribution([
            'return_id' => $returnRecord->id,
            'fund_id' => $fund->id,
            'amount' => 50,
            'percent' => 50,
        ]);
        $distribution->save();

        // 测试关联
        $relatedReturn = $distribution->return;
        $this->assertNotNull($relatedReturn, 'return should not be null');
        $this->assertEquals($returnRecord->id, $relatedReturn->id, 'return id should match');
        $this->assertEquals(100, $relatedReturn->total_amount, 'return total amount should be 100');
    }

    /**
     * 测试获取基金关联
     */
    public function testGetFund()
    {
        // 创建基金
        $fund = new Fund([
            'name' => '储蓄基金',
            'allocation_percent' => 50,
            'current_balance' => 0,
        ]);
        $fund->save();

        // 创建产品
        $product = new InvestmentProduct([
            'name' => '余额宝',
            'type' => InvestmentProduct::TYPE_ALIPAY,
            'current_amount' => 0,
        ]);
        $product->save();

        // 创建收益记录
        $returnRecord = new ReturnRecord([
            'product_id' => $product->id,
            'total_amount' => 100,
            'return_date' => date('Y-m-d'),
            'is_distributed' => 0,
        ]);
        $returnRecord->save(false);

        // 创建分配记录
        $distribution = new ReturnDistribution([
            'return_id' => $returnRecord->id,
            'fund_id' => $fund->id,
            'amount' => 50,
            'percent' => 50,
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
            'allocation_percent' => 50,
            'current_balance' => 0,
        ]);
        $fund->save();

        // 创建产品
        $product = new InvestmentProduct([
            'name' => '余额宝',
            'type' => InvestmentProduct::TYPE_ALIPAY,
            'current_amount' => 0,
        ]);
        $product->save();

        // 创建收益记录
        $returnRecord = new ReturnRecord([
            'product_id' => $product->id,
            'total_amount' => 100,
            'return_date' => date('Y-m-d'),
            'is_distributed' => 0,
        ]);
        $returnRecord->save(false);

        // 创建分配记录
        $distribution = new ReturnDistribution([
            'return_id' => $returnRecord->id,
            'fund_id' => $fund->id,
            'amount' => 50,
            'percent' => 50,
        ]);

        $timeBefore = time();
        $this->assertTrue($distribution->save(), 'distribution should be saved');
        $timeAfter = time();

        $this->assertNotNull($distribution->created_at, 'created_at should not be null');
        $this->assertGreaterThanOrEqual($timeBefore, $distribution->created_at, 'created_at should be >= time before save');
        $this->assertLessThanOrEqual($timeAfter, $distribution->created_at, 'created_at should be <= time after save');
    }

    /**
     * 测试验证规则 - 无效的收益ID
     */
    public function testValidationInvalidReturnId()
    {
        // 创建基金
        $fund = new Fund([
            'name' => '储蓄基金',
            'allocation_percent' => 50,
            'current_balance' => 0,
        ]);
        $fund->save();

        // 使用不存在的收益ID
        $distribution = new ReturnDistribution([
            'return_id' => 99999,
            'fund_id' => $fund->id,
            'amount' => 50,
            'percent' => 50,
        ]);

        $this->assertFalse($distribution->validate(), 'distribution should not be valid');
        $this->assertArrayHasKey('return_id', $distribution->errors, 'distribution should have error on return_id');
    }

    /**
     * 测试验证规则 - 无效的基金ID
     */
    public function testValidationInvalidFundId()
    {
        // 创建产品
        $product = new InvestmentProduct([
            'name' => '余额宝',
            'type' => InvestmentProduct::TYPE_ALIPAY,
            'current_amount' => 0,
        ]);
        $product->save();

        // 创建收益记录
        $returnRecord = new ReturnRecord([
            'product_id' => $product->id,
            'total_amount' => 100,
            'return_date' => date('Y-m-d'),
            'is_distributed' => 0,
        ]);
        $returnRecord->save(false);

        // 使用不存在的基金ID
        $distribution = new ReturnDistribution([
            'return_id' => $returnRecord->id,
            'fund_id' => 99999,
            'amount' => 50,
            'percent' => 50,
        ]);

        $this->assertFalse($distribution->validate(), 'distribution should not be valid');
        $this->assertArrayHasKey('fund_id', $distribution->errors, 'distribution should have error on fund_id');
    }
}

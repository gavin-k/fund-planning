<?php

namespace common\tests\unit\models;

use Yii;
use common\models\ReturnRecord;
use common\models\ReturnDistribution;
use common\models\Investment;
use common\models\Fund;
use common\models\InvestmentProduct;

/**
 * ReturnRecord model test
 * 测试收益按投资比例自动分配算法
 */
class ReturnRecordTest extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // 清空相关表 - 使用 CASCADE 处理外键约束
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_distribution}}, {{%return_record}}, {{%investment}}, {{%investment_product}}, {{%fund}} CASCADE')->execute();
    }

    protected function _after()
    {
        // 清理数据
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_distribution}}, {{%return_record}}, {{%investment}}, {{%investment_product}}, {{%fund}} CASCADE')->execute();
    }

    /**
     * 准备测试数据
     */
    protected function prepareTestData()
    {
        // 创建两个基金
        $fund1 = new Fund([
            'name' => '储蓄基金',
            'allocation_percent' => 60,
            'current_balance' => 100000,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund1->save(false);

        $fund2 = new Fund([
            'name' => '教育基金',
            'allocation_percent' => 40,
            'current_balance' => 50000,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund2->save(false);

        // 创建产品
        $product = new InvestmentProduct([
            'name' => '余额宝',
            'type' => InvestmentProduct::TYPE_ALIPAY,
            'platform' => '支付宝',
            'current_amount' => 0,
            'status' => InvestmentProduct::STATUS_ACTIVE,
        ]);
        $product->save(false);

        return [
            'fund1' => $fund1,
            'fund2' => $fund2,
            'product' => $product,
        ];
    }

    /**
     * 测试创建收益记录
     */
    public function testCreateReturnRecord()
    {
        $data = $this->prepareTestData();

        $returnRecord = new ReturnRecord([
            'product_id' => $data['product']->id,
            'total_amount' => 1000,
            'return_date' => '2024-01-01',
        ]);

        $this->assertTrue($returnRecord->validate(), 'return record should be valid');
    }

    /**
     * 测试收益按投资比例分配
     * 核心功能测试
     */
    public function testDistributeToFundsByInvestmentRatio()
    {
        $data = $this->prepareTestData();

        // 创建投资
        // 储蓄基金投资 60000 (60%)
        $investment1 = new Investment([
            'fund_id' => $data['fund1']->id,
            'product_id' => $data['product']->id,
            'amount' => 60000,
            'investment_date' => '2024-01-01',
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment1->save();

        // 教育基金投资 40000 (40%)
        $investment2 = new Investment([
            'fund_id' => $data['fund2']->id,
            'product_id' => $data['product']->id,
            'amount' => 40000,
            'investment_date' => '2024-01-01',
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment2->save();

        // 记录产品余额
        $fund1BalanceBefore = $data['fund1']->current_balance;
        $fund2BalanceBefore = $data['fund2']->current_balance;

        // 创建收益记录
        $returnRecord = new ReturnRecord([
            'product_id' => $data['product']->id,
            'total_amount' => 5000,
            'return_date' => '2024-01-01',
        ]);
        $returnRecord->save();

        // 验证是否已分配
        $this->assertEquals(1, $returnRecord->is_distributed, 'return should be distributed');

        // 验证分配记录数量
        $distributions = ReturnDistribution::find()->where(['return_id' => $returnRecord->id])->all();
        $this->assertEquals(2, count($distributions), 'should have 2 distribution records');

        // 验证各基金余额变化
        $data['fund1']->refresh();
        $data['fund2']->refresh();

        // 储蓄基金应得 5000 * 60% = 3000
        $this->assertEquals($fund1BalanceBefore + 3000, $data['fund1']->current_balance, 'fund1 balance should increase by 3000');

        // 教育基金应得 5000 * 40% = 2000
        $this->assertEquals($fund2BalanceBefore + 2000, $data['fund2']->current_balance, 'fund2 balance should increase by 2000');

        // 验证总分配金额
        $totalDistributed = 0;
        foreach ($distributions as $dist) {
            $totalDistributed += $dist->amount;
        }
        $this->assertEquals(5000, $totalDistributed, 'total distributed should equal return amount');
    }

    /**
     * 测试收益分配 - 没有投资时应该失败
     */
    public function testDistributeToFundsNoInvestments()
    {
        $data = $this->prepareTestData();

        // 创建收益记录（没有任何投资）
        $returnRecord = new ReturnRecord([
            'product_id' => $data['product']->id,
            'total_amount' => 1000,
            'return_date' => '2024-01-01',
        ]);
        $returnRecord->save();

        // 验证未分配
        $this->assertEquals(0, $returnRecord->is_distributed, 'return should not be distributed');
    }

    /**
     * 测试收益分配 - 只有已赎回的投资
     */
    public function testDistributeToFundsOnlyWithdrawnInvestments()
    {
        $data = $this->prepareTestData();

        // 创建已赎回的投资
        $investment = new Investment([
            'fund_id' => $data['fund1']->id,
            'product_id' => $data['product']->id,
            'amount' => 10000,
            'investment_date' => '2024-01-01',
            'status' => Investment::STATUS_WITHDRAWN,
        ]);
        $investment->save(false);

        // 创建收益记录
        $returnRecord = new ReturnRecord([
            'product_id' => $data['product']->id,
            'total_amount' => 1000,
            'return_date' => '2024-01-01',
        ]);
        $returnRecord->save();

        // 验证未分配（因为没有生效中的投资）
        $this->assertEquals(0, $returnRecord->is_distributed, 'return should not be distributed');
    }

    /**
     * 测试收益分配 - 单个基金多笔投资
     */
    public function testDistributeToFundsMultipleInvestmentsFromOneFund()
    {
        $data = $this->prepareTestData();

        // 储蓄基金两笔投资，总计 60000
        $investment1 = new Investment([
            'fund_id' => $data['fund1']->id,
            'product_id' => $data['product']->id,
            'amount' => 30000,
            'investment_date' => '2024-01-01',
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment1->save();

        $investment2 = new Investment([
            'fund_id' => $data['fund1']->id,
            'product_id' => $data['product']->id,
            'amount' => 30000,
            'investment_date' => '2024-01-02',
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment2->save();

        // 教育基金一笔投资 40000
        $investment3 = new Investment([
            'fund_id' => $data['fund2']->id,
            'product_id' => $data['product']->id,
            'amount' => 40000,
            'investment_date' => '2024-01-01',
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment3->save();

        $fund1BalanceBefore = $data['fund1']->current_balance;
        $fund2BalanceBefore = $data['fund2']->current_balance;

        // 创建收益记录
        $returnRecord = new ReturnRecord([
            'product_id' => $data['product']->id,
            'total_amount' => 10000,
            'return_date' => '2024-01-01',
        ]);
        $returnRecord->save();

        // 验证分配
        $data['fund1']->refresh();
        $data['fund2']->refresh();

        // 储蓄基金应得 10000 * 60% = 6000
        $this->assertEquals($fund1BalanceBefore + 6000, $data['fund1']->current_balance, 'fund1 balance should increase by 6000');

        // 教育基金应得 10000 * 40% = 4000
        $this->assertEquals($fund2BalanceBefore + 4000, $data['fund2']->current_balance, 'fund2 balance should increase by 4000');
    }

    /**
     * 测试收益分配 - 精度问题处理
     * 最后一个基金应该得到剩余金额
     */
    public function testDistributeToFundsPrecision()
    {
        $data = $this->prepareTestData();

        // 创建会产生精度问题的投资比例
        // 基金1: 33333 (33.33%)
        $investment1 = new Investment([
            'fund_id' => $data['fund1']->id,
            'product_id' => $data['product']->id,
            'amount' => 33333,
            'investment_date' => '2024-01-01',
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment1->save();

        // 基金2: 66667 (66.67%)
        $investment2 = new Investment([
            'fund_id' => $data['fund2']->id,
            'product_id' => $data['product']->id,
            'amount' => 66667,
            'investment_date' => '2024-01-01',
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment2->save();

        // 创建收益记录
        $returnRecord = new ReturnRecord([
            'product_id' => $data['product']->id,
            'total_amount' => 1000,
            'return_date' => '2024-01-01',
        ]);
        $returnRecord->save();

        // 验证总分配金额（应该正好等于1000，没有精度损失）
        $distributions = ReturnDistribution::find()->where(['return_id' => $returnRecord->id])->all();
        $totalDistributed = 0;
        foreach ($distributions as $dist) {
            $totalDistributed += $dist->amount;
        }

        $this->assertEquals(1000, $totalDistributed, 'total distributed should equal return amount exactly');
    }

    /**
     * 测试获取分配详情
     */
    public function testGetDistributionDetails()
    {
        $data = $this->prepareTestData();

        // 创建投资
        $investment = new Investment([
            'fund_id' => $data['fund1']->id,
            'product_id' => $data['product']->id,
            'amount' => 10000,
            'investment_date' => '2024-01-01',
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment->save();

        // 创建收益记录
        $returnRecord = new ReturnRecord([
            'product_id' => $data['product']->id,
            'total_amount' => 500,
            'return_date' => '2024-01-01',
        ]);
        $returnRecord->save();

        // 获取分配详情
        $details = $returnRecord->getDistributionDetails();

        $this->assertGreaterThan(0, count($details), 'should have distribution details');
        $this->assertTrue(isset($details[0]['fund_name']), 'detail should have fund_name');
        $this->assertTrue(isset($details[0]['amount']), 'detail should have amount');
        $this->assertTrue(isset($details[0]['percent']), 'detail should have percent');
    }

    /**
     * 测试重复分配应该失败
     */
    public function testDistributeToFundsTwiceShouldFail()
    {
        $data = $this->prepareTestData();

        // 创建投资
        $investment = new Investment([
            'fund_id' => $data['fund1']->id,
            'product_id' => $data['product']->id,
            'amount' => 10000,
            'investment_date' => '2024-01-01',
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment->save();

        // 创建并分配收益
        $returnRecord = new ReturnRecord([
            'product_id' => $data['product']->id,
            'total_amount' => 500,
            'return_date' => '2024-01-01',
        ]);
        $returnRecord->save();

        $this->assertEquals(1, $returnRecord->is_distributed, 'return should be distributed');

        // 尝试再次分配
        $result = $returnRecord->distributeToFunds();

        $this->assertFalse($result, 'second distribution should fail');
        $this->assertArrayHasKey('is_distributed', $returnRecord->errors, 'should have error');
    }

    /**
     * 测试验证规则 - 金额必须为正数
     */
    public function testValidationAmountPositive()
    {
        $data = $this->prepareTestData();

        $returnRecord = new ReturnRecord([
            'product_id' => $data['product']->id,
            'total_amount' => -100,
            'return_date' => '2024-01-01',
        ]);

        $this->assertFalse($returnRecord->validate(), 'return with negative amount should not be valid');
        $this->assertArrayHasKey('total_amount', $returnRecord->errors, 'return should have error on total_amount');
    }

    /**
     * 测试外键验证 - 无效的产品ID
     */
    public function testValidationInvalidProductId()
    {
        $returnRecord = new ReturnRecord([
            'product_id' => 99999, // 不存在的产品ID
            'total_amount' => 1000,
            'return_date' => '2024-01-01',
        ]);

        $this->assertFalse($returnRecord->validate(), 'return with invalid product_id should not be valid');
        $this->assertArrayHasKey('product_id', $returnRecord->errors, 'return should have error on product_id');
    }
}

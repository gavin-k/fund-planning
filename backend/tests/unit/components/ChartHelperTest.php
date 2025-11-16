<?php

namespace backend\tests\unit\components;

use Yii;
use backend\components\ChartHelper;
use common\models\Fund;
use common\models\Income;
use common\models\Investment;
use common\models\InvestmentProduct;
use common\models\ReturnRecord;

/**
 * ChartHelper 组件测试
 */
class ChartHelperTest extends \Codeception\Test\Unit
{
    /**
     * @var \backend\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // 清空相关表
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%fund}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%income}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%investment}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%investment_product}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_record}} CASCADE')->execute();
    }

    protected function _after()
    {
        // 清理数据
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%fund}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%income}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%investment}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%investment_product}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_record}} CASCADE')->execute();
    }

    /**
     * 测试获取基金余额饼图数据 - 空数据
     */
    public function testGetFundBalanceChartDataEmpty()
    {
        $data = ChartHelper::getFundBalanceChartData();

        $this->assertIsArray($data, 'should return array');
        $this->assertArrayHasKey('labels', $data, 'should have labels');
        $this->assertArrayHasKey('datasets', $data, 'should have datasets');
        $this->assertEmpty($data['labels'], 'labels should be empty');
    }

    /**
     * 测试获取基金余额饼图数据 - 有数据
     */
    public function testGetFundBalanceChartDataWithData()
    {
        // 创建测试基金
        $fund1 = new Fund(['name' => '基金A', 'allocation_percentage' => 40, 'balance' => 5000, 'status' => Fund::STATUS_ACTIVE]);
        $fund1->save(false);

        $fund2 = new Fund(['name' => '基金B', 'allocation_percentage' => 30, 'balance' => 3000, 'status' => Fund::STATUS_ACTIVE]);
        $fund2->save(false);

        $fund3 = new Fund(['name' => '基金C', 'allocation_percentage' => 30, 'balance' => 0, 'status' => Fund::STATUS_ACTIVE]);
        $fund3->save(false);

        $data = ChartHelper::getFundBalanceChartData();

        $this->assertCount(2, $data['labels'], 'should have 2 labels (exclude zero balance)');
        $this->assertEquals('基金A', $data['labels'][0], 'first label should be 基金A');
        $this->assertEquals('基金B', $data['labels'][1], 'second label should be 基金B');

        $this->assertCount(1, $data['datasets'], 'should have 1 dataset');
        $this->assertEquals(5000.0, $data['datasets'][0]['data'][0], 'first data should be 5000');
        $this->assertEquals(3000.0, $data['datasets'][0]['data'][1], 'second data should be 3000');
        $this->assertCount(2, $data['datasets'][0]['backgroundColor'], 'should have 2 colors');
    }

    /**
     * 测试获取近12个月收益趋势数据 - 空数据
     */
    public function testGetMonthlyReturnTrendDataEmpty()
    {
        $data = ChartHelper::getMonthlyReturnTrendData();

        $this->assertIsArray($data, 'should return array');
        $this->assertArrayHasKey('labels', $data, 'should have labels');
        $this->assertArrayHasKey('datasets', $data, 'should have datasets');
        $this->assertCount(12, $data['labels'], 'should have 12 months');
        $this->assertCount(2, $data['datasets'], 'should have 2 datasets (income and return)');

        // 验证所有月份数据都是0
        foreach ($data['datasets'][0]['data'] as $value) {
            $this->assertEquals(0.0, $value, 'income data should be 0');
        }
        foreach ($data['datasets'][1]['data'] as $value) {
            $this->assertEquals(0.0, $value, 'return data should be 0');
        }
    }

    /**
     * 测试获取近12个月收益趋势数据 - 有数据
     */
    public function testGetMonthlyReturnTrendDataWithData()
    {
        // 创建收入记录
        $income = new Income([
            'source' => '工资',
            'amount' => 10000,
            'income_date' => date('Y-m-15'),
            'status' => Income::STATUS_DISTRIBUTED,
        ]);
        $income->save(false);

        // 创建收益记录
        $return = new ReturnRecord([
            'source' => '投资收益',
            'amount' => 500,
            'return_date' => date('Y-m-20'),
            'status' => ReturnRecord::STATUS_DISTRIBUTED,
        ]);
        $return->save(false);

        $data = ChartHelper::getMonthlyReturnTrendData();

        $this->assertCount(12, $data['labels'], 'should have 12 months');

        // 验证当前月份有数据
        $currentMonth = date('Y-m');
        $currentMonthIndex = array_search($currentMonth, $data['labels']);

        $this->assertNotFalse($currentMonthIndex, 'current month should be in labels');
        $this->assertEquals(10000.0, $data['datasets'][0]['data'][$currentMonthIndex], 'current month income should be 10000');
        $this->assertEquals(500.0, $data['datasets'][1]['data'][$currentMonthIndex], 'current month return should be 500');
    }

    /**
     * 测试获取投资产品分布数据
     */
    public function testGetInvestmentDistributionData()
    {
        // 创建基金
        $fund = new Fund(['name' => '测试基金', 'allocation_percentage' => 100, 'balance' => 10000]);
        $fund->save(false);

        // 创建投资产品
        $product1 = new InvestmentProduct(['name' => '产品A', 'description' => '测试产品A', 'status' => InvestmentProduct::STATUS_ACTIVE]);
        $product1->save(false);

        $product2 = new InvestmentProduct(['name' => '产品B', 'description' => '测试产品B', 'status' => InvestmentProduct::STATUS_ACTIVE]);
        $product2->save(false);

        // 创建投资记录
        $inv1 = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product1->id,
            'amount' => 3000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $inv1->save(false);

        $inv2 = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product2->id,
            'amount' => 2000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $inv2->save(false);

        $data = ChartHelper::getInvestmentDistributionData();

        $this->assertCount(2, $data['labels'], 'should have 2 labels');
        $this->assertContains('产品A', $data['labels'], 'should contain 产品A');
        $this->assertContains('产品B', $data['labels'], 'should contain 产品B');

        $this->assertCount(1, $data['datasets'], 'should have 1 dataset');
        $this->assertCount(2, $data['datasets'][0]['data'], 'should have 2 data points');
    }

    /**
     * 测试获取基金投资占比数据
     */
    public function testGetFundInvestmentChartData()
    {
        // 创建基金
        $fund1 = new Fund(['name' => '基金A', 'allocation_percentage' => 50, 'balance' => 5000]);
        $fund1->save(false);

        $fund2 = new Fund(['name' => '基金B', 'allocation_percentage' => 50, 'balance' => 5000]);
        $fund2->save(false);

        // 创建产品
        $product = new InvestmentProduct(['name' => '产品', 'description' => '测试产品', 'status' => InvestmentProduct::STATUS_ACTIVE]);
        $product->save(false);

        // 创建投资记录
        $inv1 = new Investment([
            'fund_id' => $fund1->id,
            'product_id' => $product->id,
            'amount' => 4000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $inv1->save(false);

        $inv2 = new Investment([
            'fund_id' => $fund2->id,
            'product_id' => $product->id,
            'amount' => 3000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $inv2->save(false);

        $data = ChartHelper::getFundInvestmentChartData();

        $this->assertCount(2, $data['labels'], 'should have 2 labels');
        $this->assertContains('基金A', $data['labels'], 'should contain 基金A');
        $this->assertContains('基金B', $data['labels'], 'should contain 基金B');

        $this->assertCount(1, $data['datasets'], 'should have 1 dataset');
        $this->assertCount(2, $data['datasets'][0]['backgroundColor'], 'should have 2 colors');
    }

    /**
     * 测试获取月度收支对比数据 - 当前年份
     */
    public function testGetMonthlyIncomeExpenseDataCurrentYear()
    {
        $data = ChartHelper::getMonthlyIncomeExpenseData();

        $this->assertIsArray($data, 'should return array');
        $this->assertArrayHasKey('labels', $data, 'should have labels');
        $this->assertArrayHasKey('datasets', $data, 'should have datasets');
        $this->assertCount(12, $data['labels'], 'should have 12 months');
        $this->assertEquals('1月', $data['labels'][0], 'first label should be 1月');
        $this->assertEquals('12月', $data['labels'][11], 'last label should be 12月');

        $this->assertCount(2, $data['datasets'], 'should have 2 datasets');
        $this->assertEquals('收入', $data['datasets'][0]['label'], 'first dataset should be 收入');
        $this->assertEquals('投资', $data['datasets'][1]['label'], 'second dataset should be 投资');
    }

    /**
     * 测试获取月度收支对比数据 - 指定年份
     */
    public function testGetMonthlyIncomeExpenseDataSpecificYear()
    {
        // 创建去年的收入记录
        $lastYear = (int)date('Y') - 1;
        $income = new Income([
            'source' => '工资',
            'amount' => 5000,
            'income_date' => $lastYear . '-06-15',
            'status' => Income::STATUS_DISTRIBUTED,
        ]);
        $income->save(false);

        $data = ChartHelper::getMonthlyIncomeExpenseData($lastYear);

        $this->assertCount(12, $data['labels'], 'should have 12 months');
        $this->assertCount(2, $data['datasets'], 'should have 2 datasets');

        // 验证6月份有数据
        $this->assertEquals(5000.0, $data['datasets'][0]['data'][5], 'June income should be 5000');
    }

    /**
     * 测试颜色循环使用
     */
    public function testColorCycling()
    {
        // 创建超过颜色数量的基金
        for ($i = 1; $i <= 15; $i++) {
            $fund = new Fund([
                'name' => "基金{$i}",
                'allocation_percentage' => 1,
                'balance' => 1000,
                'status' => Fund::STATUS_ACTIVE,
            ]);
            $fund->save(false);
        }

        $data = ChartHelper::getFundBalanceChartData();

        // 验证颜色数组被循环使用
        $this->assertCount(15, $data['datasets'][0]['backgroundColor'], 'should have 15 colors');

        // 验证颜色循环：第10个颜色应该等于第1个颜色（因为有9个颜色常量）
        $this->assertEquals(
            $data['datasets'][0]['backgroundColor'][0],
            $data['datasets'][0]['backgroundColor'][9],
            'colors should cycle after 9'
        );
    }
}

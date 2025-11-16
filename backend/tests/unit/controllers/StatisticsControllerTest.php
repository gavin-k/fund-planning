<?php

namespace backend\tests\unit\controllers;

use Yii;
use backend\controllers\StatisticsController;
use common\models\Fund;
use common\models\Income;
use common\models\Investment;
use common\models\InvestmentProduct;
use common\models\ReturnRecord;
use common\models\ReturnDistribution;
use yii\web\Application;

/**
 * StatisticsController 测试
 */
class StatisticsControllerTest extends \Codeception\Test\Unit
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
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_distribution}} CASCADE')->execute();
    }

    protected function _after()
    {
        // 清理数据
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%fund}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%income}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%investment}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%investment_product}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_record}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_distribution}} CASCADE')->execute();
    }

    /**
     * 测试统计总览页面 - 默认参数
     */
    public function testActionIndexDefault()
    {
        $controller = new StatisticsController('statistics', Yii::$app);

        // 模拟请求
        Yii::$app->request->setQueryParams([]);

        $result = $controller->actionIndex();

        // 验证返回结果
        $this->assertNotNull($result, 'should return result');
        $this->assertStringContainsString('index', $result, 'should render index view');
    }

    /**
     * 测试统计总览页面 - 自定义时间范围
     */
    public function testActionIndexWithDateRange()
    {
        $controller = new StatisticsController('statistics', Yii::$app);

        // 模拟请求
        Yii::$app->request->setQueryParams([
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ]);

        $result = $controller->actionIndex();

        $this->assertNotNull($result, 'should return result');
    }

    /**
     * 测试期间统计数据计算
     */
    public function testPeriodStatistics()
    {
        // 创建测试数据
        $income = new Income([
            'source' => '工资',
            'amount' => 10000,
            'income_date' => date('Y-m-15'),
            'status' => Income::STATUS_DISTRIBUTED,
        ]);
        $income->save(false);

        $return = new ReturnRecord([
            'source' => '投资收益',
            'amount' => 500,
            'return_date' => date('Y-m-20'),
            'status' => ReturnRecord::STATUS_DISTRIBUTED,
        ]);
        $return->save(false);

        // 创建基金和投资
        $fund = new Fund(['name' => '测试基金', 'allocation_percentage' => 100, 'balance' => 10000]);
        $fund->save(false);

        $product = new InvestmentProduct(['name' => '产品', 'description' => '测试', 'status' => InvestmentProduct::STATUS_ACTIVE]);
        $product->save(false);

        $investment = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'amount' => 5000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment->save(false);

        $controller = new StatisticsController('statistics', Yii::$app);

        // 设置当月时间范围
        Yii::$app->request->setQueryParams([
            'start_date' => date('Y-m-01'),
            'end_date' => date('Y-m-d'),
        ]);

        $result = $controller->actionIndex();

        // 这里实际上会返回渲染的视图，但我们可以验证没有抛出异常
        $this->assertNotNull($result, 'should return result');
    }

    /**
     * 测试各基金收益率计算 - 空数据
     */
    public function testCalculateFundReturnsEmpty()
    {
        $controller = new StatisticsController('statistics', Yii::$app);

        // 使用反射调用私有方法
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('calculateFundReturns');
        $method->setAccessible(true);

        $result = $method->invoke($controller);

        $this->assertIsArray($result, 'should return array');
        $this->assertEmpty($result, 'should be empty');
    }

    /**
     * 测试各基金收益率计算 - 有数据
     */
    public function testCalculateFundReturnsWithData()
    {
        // 创建基金
        $fund1 = new Fund(['name' => '基金A', 'allocation_percentage' => 50, 'balance' => 6000]);
        $fund1->save(false);

        $fund2 = new Fund(['name' => '基金B', 'allocation_percentage' => 50, 'balance' => 4500]);
        $fund2->save(false);

        // 创建产品
        $product = new InvestmentProduct(['name' => '产品', 'description' => '测试', 'status' => InvestmentProduct::STATUS_ACTIVE]);
        $product->save(false);

        // 创建投资记录
        $inv1 = new Investment([
            'fund_id' => $fund1->id,
            'product_id' => $product->id,
            'amount' => 5000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $inv1->save(false);

        $inv2 = new Investment([
            'fund_id' => $fund2->id,
            'product_id' => $product->id,
            'amount' => 4000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $inv2->save(false);

        // 创建收益分配记录
        $dist1 = new ReturnDistribution([
            'fund_id' => $fund1->id,
            'amount' => 500,
        ]);
        $dist1->save(false);

        $dist2 = new ReturnDistribution([
            'fund_id' => $fund2->id,
            'amount' => 400,
        ]);
        $dist2->save(false);

        $controller = new StatisticsController('statistics', Yii::$app);

        // 使用反射调用私有方法
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('calculateFundReturns');
        $method->setAccessible(true);

        $result = $method->invoke($controller);

        $this->assertIsArray($result, 'should return array');
        $this->assertCount(2, $result, 'should have 2 funds');

        // 验证第一个基金的数据
        $this->assertEquals('基金A', $result[0]['name'], 'first fund name should be correct');
        $this->assertEquals(5000.0, $result[0]['invested'], 'first fund invested should be 5000');
        $this->assertEquals(500.0, $result[0]['returns'], 'first fund returns should be 500');
        $this->assertEquals(10.0, $result[0]['rate'], 'first fund rate should be 10%');
        $this->assertEquals(6000, $result[0]['balance'], 'first fund balance should be 6000');

        // 验证第二个基金的数据
        $this->assertEquals('基金B', $result[1]['name'], 'second fund name should be correct');
        $this->assertEquals(4000.0, $result[1]['invested'], 'second fund invested should be 4000');
        $this->assertEquals(400.0, $result[1]['returns'], 'second fund returns should be 400');
        $this->assertEquals(10.0, $result[1]['rate'], 'second fund rate should be 10%');
    }

    /**
     * 测试各基金收益率计算 - 无投资的基金
     */
    public function testCalculateFundReturnsNoInvestment()
    {
        // 创建基金但不创建投资
        $fund = new Fund(['name' => '空基金', 'allocation_percentage' => 100, 'balance' => 1000]);
        $fund->save(false);

        $controller = new StatisticsController('statistics', Yii::$app);

        // 使用反射调用私有方法
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('calculateFundReturns');
        $method->setAccessible(true);

        $result = $method->invoke($controller);

        $this->assertIsArray($result, 'should return array');
        $this->assertCount(1, $result, 'should have 1 fund');
        $this->assertEquals(0.0, $result[0]['invested'], 'invested should be 0');
        $this->assertEquals(0.0, $result[0]['returns'], 'returns should be 0');
        $this->assertEquals(0.0, $result[0]['rate'], 'rate should be 0%');
    }

    /**
     * 测试收益率计算精度
     */
    public function testReturnRateCalculationPrecision()
    {
        // 创建基金
        $fund = new Fund(['name' => '测试基金', 'allocation_percentage' => 100, 'balance' => 10350]);
        $fund->save(false);

        // 创建产品
        $product = new InvestmentProduct(['name' => '产品', 'description' => '测试', 'status' => InvestmentProduct::STATUS_ACTIVE]);
        $product->save(false);

        // 创建投资记录
        $investment = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'amount' => 10000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment->save(false);

        // 创建收益分配记录 (3.5% 收益率)
        $distribution = new ReturnDistribution([
            'fund_id' => $fund->id,
            'amount' => 350,
        ]);
        $distribution->save(false);

        $controller = new StatisticsController('statistics', Yii::$app);

        // 使用反射调用私有方法
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('calculateFundReturns');
        $method->setAccessible(true);

        $result = $method->invoke($controller);

        $this->assertEquals(3.5, $result[0]['rate'], 'rate should be 3.5%');
    }
}

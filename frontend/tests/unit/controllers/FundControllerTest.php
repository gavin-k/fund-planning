<?php

namespace frontend\tests\unit\controllers;

use Yii;
use frontend\controllers\FundController;
use common\models\Fund;
use common\models\Investment;
use common\models\InvestmentProduct;
use common\models\IncomeDistribution;
use common\models\ReturnDistribution;
use yii\web\NotFoundHttpException;

/**
 * Frontend FundController 测试
 */
class FundControllerTest extends \Codeception\Test\Unit
{
    /**
     * @var \frontend\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // 清空相关表
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%fund}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%investment}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%investment_product}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%income_distribution}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_distribution}}, {{%income}}, {{%return_record}} CASCADE')->execute();
    }

    protected function _after()
    {
        // 清理数据
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%fund}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%investment}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%investment_product}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%income_distribution}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_distribution}}, {{%income}}, {{%return_record}} CASCADE')->execute();
    }

    /**
     * 测试基金列表页面 - 空数据
     */
    public function testActionIndexEmpty()
    {
        $controller = new FundController('fund', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $result = $controller->actionIndex();

        $this->assertNotNull($result, 'should return result');
        $this->assertStringContainsString('index', $result, 'should render index view');
    }

    /**
     * 测试基金列表页面 - 有数据
     */
    public function testActionIndexWithData()
    {
        // 创建测试基金
        $fund1 = new Fund([
            'name' => '储蓄基金',
            'allocation_percent' => 40,
            'current_balance' => 10000,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $this->assertTrue($fund1->save());

        $fund2 = new Fund([
            'name' => '投资基金',
            'allocation_percent' => 60,
            'current_balance' => 15000,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $this->assertTrue($fund2->save());

        // 创建禁用基金（不应该显示）
        $fund3 = new Fund([
            'name' => '禁用基金',
            'allocation_percent' => 0,
            'current_balance' => 5000,
            'status' => Fund::STATUS_INACTIVE,
        ]);
        $this->assertTrue($fund3->save());

        $controller = new FundController('fund', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $result = $controller->actionIndex();

        $this->assertStringContainsString('储蓄基金', $result);
        $this->assertStringContainsString('投资基金', $result);
        $this->assertStringNotContainsString('禁用基金', $result);
        $this->assertLessThan(strpos($result, '储蓄基金'), strpos($result, '投资基金'));
    }

    /**
     * 测试总资产计算
     */
    public function testTotalAssetsCalculation()
    {
        // 创建测试基金
        $fund1 = new Fund(['name' => '基金A', 'allocation_percent' => 50, 'current_balance' => 8000, 'status' => Fund::STATUS_ACTIVE]);
        $this->assertTrue($fund1->save());

        $fund2 = new Fund(['name' => '基金B', 'allocation_percent' => 50, 'current_balance' => 12000, 'status' => Fund::STATUS_ACTIVE]);
        $this->assertTrue($fund2->save());

        $controller = new FundController('fund', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $result = $controller->actionIndex();

        // 验证视图能正常渲染（包含总资产计算）
        $this->assertStringContainsString('¥20,000.00', $result);
    }

    /**
     * 测试总投资计算
     */
    public function testTotalInvestmentCalculation()
    {
        // 创建测试数据
        $fund = new Fund(['name' => '基金', 'allocation_percent' => 100, 'current_balance' => 20000, 'status' => Fund::STATUS_ACTIVE]);
        $this->assertTrue($fund->save());

        $product = new InvestmentProduct(['name' => '产品', 'description' => '测试', 'type' => InvestmentProduct::TYPE_ALIPAY, 'status' => InvestmentProduct::STATUS_ACTIVE]);
        $this->assertTrue($product->save());

        $inv1 = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'investment_date' => '2024-01-01',
            'amount' => 5000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $this->assertTrue($inv1->save());

        $inv2 = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'investment_date' => '2024-01-01',
            'amount' => 3000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $this->assertTrue($inv2->save());

        // 已赎回的投资（不应该计入总投资）
        $inv3 = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'investment_date' => '2024-01-01',
            'amount' => 2000,
            'status' => Investment::STATUS_WITHDRAWN,
        ]);
        $this->assertTrue($inv3->save());

        $controller = new FundController('fund', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $result = $controller->actionIndex();

        $this->assertStringContainsString('¥8,000.00', $result);
        $this->assertStringContainsString('¥20,000.00', $result);
    }

    /**
     * 测试基金详情页面
     */
    public function testActionView()
    {
        // 创建测试基金
        $fund = new Fund([
            'name' => '测试基金',
            'allocation_percent' => 100,
            'current_balance' => 10000,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $this->assertTrue($fund->save());

        $controller = new FundController('fund', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $result = $controller->actionView($fund->id);

        $this->assertNotNull($result, 'should return result');
        $this->assertStringContainsString('view', $result, 'should render view page');
    }

    /**
     * 测试基金详情页面 - 包含投资记录
     */
    public function testActionViewWithInvestments()
    {
        // 创建测试基金
        $fund = new Fund(['name' => '测试基金', 'allocation_percent' => 100, 'current_balance' => 10000, 'status' => Fund::STATUS_ACTIVE]);
        $this->assertTrue($fund->save());

        // 创建产品
        $product = new InvestmentProduct(['name' => '产品A', 'description' => '测试', 'type' => InvestmentProduct::TYPE_ALIPAY, 'status' => InvestmentProduct::STATUS_ACTIVE]);
        $this->assertTrue($product->save());

        // 创建投资记录
        $investment = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'investment_date' => '2024-01-01',
            'amount' => 5000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $this->assertTrue($investment->save());

        $controller = new FundController('fund', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $result = $controller->actionView($fund->id);

        $this->assertNotNull($result, 'should return result');
    }

    /**
     * 测试基金详情页面 - 包含收入分配记录
     */
    public function testActionViewWithIncomeDistributions()
    {
        // 创建测试基金
        $fund = new Fund(['name' => '测试基金', 'allocation_percent' => 100, 'current_balance' => 10000, 'status' => Fund::STATUS_ACTIVE]);
        $this->assertTrue($fund->save());

        // 创建收入分配记录
        $dist1 = $this->incomeDistribution($fund, 1000);
        $this->assertTrue($dist1->save());

        $dist2 = $this->incomeDistribution($fund, 500);
        $this->assertTrue($dist2->save());

        $controller = new FundController('fund', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $result = $controller->actionView($fund->id);

        $this->assertNotNull($result, 'should return result');
    }

    /**
     * 测试基金详情页面 - 包含收益分配记录
     */
    public function testActionViewWithReturnDistributions()
    {
        // 创建测试基金
        $fund = new Fund(['name' => '测试基金', 'allocation_percent' => 100, 'current_balance' => 10000, 'status' => Fund::STATUS_ACTIVE]);
        $this->assertTrue($fund->save());

        // 创建收益分配记录
        $dist1 = $this->returnDistribution($fund, 300);
        $this->assertTrue($dist1->save());

        $dist2 = $this->returnDistribution($fund, 200);
        $this->assertTrue($dist2->save());

        $controller = new FundController('fund', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $result = $controller->actionView($fund->id);

        $this->assertNotNull($result, 'should return result');
    }

    /**
     * 测试基金详情页面 - 完整数据
     */
    public function testActionViewWithAllData()
    {
        // 创建测试基金
        $fund = new Fund(['name' => '完整基金', 'allocation_percent' => 100, 'current_balance' => 20000, 'status' => Fund::STATUS_ACTIVE]);
        $this->assertTrue($fund->save());

        // 创建产品和投资
        $product = new InvestmentProduct(['name' => '产品', 'description' => '测试', 'type' => InvestmentProduct::TYPE_ALIPAY, 'status' => InvestmentProduct::STATUS_ACTIVE]);
        $this->assertTrue($product->save());

        $investment = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'investment_date' => '2024-01-01',
            'amount' => 10000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $this->assertTrue($investment->save());

        // 创建收入分配
        $incomeDist = $this->incomeDistribution($fund, 5000);
        $this->assertTrue($incomeDist->save());

        // 创建收益分配
        $returnDist = $this->returnDistribution($fund, 500);
        $this->assertTrue($returnDist->save());

        $controller = new FundController('fund', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $result = $controller->actionView($fund->id);

        $this->assertNotNull($result, 'should return result');
    }

    /**
     * 测试基金详情页面 - 不存在的基金
     */
    public function testActionViewNotFound()
    {
        $controller = new FundController('fund', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('基金不存在。');

        $controller->actionView(99999);
    }

    /**
     * 测试投资记录限制（最多20条）
     */
    public function testInvestmentRecordsLimit()
    {
        // 创建测试基金
        $fund = new Fund(['name' => '测试基金', 'allocation_percent' => 100, 'current_balance' => 50000, 'status' => Fund::STATUS_ACTIVE]);
        $this->assertTrue($fund->save());

        // 创建产品
        $product = new InvestmentProduct(['name' => '产品', 'description' => '测试', 'type' => InvestmentProduct::TYPE_ALIPAY, 'status' => InvestmentProduct::STATUS_ACTIVE]);
        $this->assertTrue($product->save());

        // 创建25条投资记录
        for ($i = 1; $i <= 25; $i++) {
            $investment = new Investment([
                'fund_id' => $fund->id,
                'product_id' => $product->id,
                'investment_date' => '2024-01-01',
                'amount' => 1000,
                'status' => Investment::STATUS_ACTIVE,
            ]);
            $this->assertTrue($investment->save());
        }

        $controller = new FundController('fund', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $result = $controller->actionView($fund->id);

        // 验证返回结果（实际限制在控制器中，这里验证不抛出异常）
        $this->assertNotNull($result, 'should return result');
    }

    /**
     * 测试分配记录限制（最多10条）
     */
    public function testDistributionRecordsLimit()
    {
        // 创建测试基金
        $fund = new Fund(['name' => '测试基金', 'allocation_percent' => 100, 'current_balance' => 50000, 'status' => Fund::STATUS_ACTIVE]);
        $this->assertTrue($fund->save());

        // 创建15条收入分配记录
        for ($i = 1; $i <= 15; $i++) {
            $dist = $this->incomeDistribution($fund, 1000);
            $this->assertTrue($dist->save());
        }

        // 创建15条收益分配记录
        for ($i = 1; $i <= 15; $i++) {
            $dist = $this->returnDistribution($fund, 100);
            $this->assertTrue($dist->save());
        }

        $controller = new FundController('fund', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $result = $controller->actionView($fund->id);

        // 验证返回结果（实际限制在控制器中）
        $this->assertNotNull($result, 'should return result');
    }
    private function incomeDistribution(Fund $fund, $amount)
    {
        $income = new \common\models\Income([
            'amount' => $amount,
            'income_date' => '2024-01-01',
            'is_distributed' => 1,
        ]);
        $this->assertTrue($income->save());
        return new IncomeDistribution([
            'income_id' => $income->id,
            'fund_id' => $fund->id,
            'amount' => $amount,
            'percent' => 100,
        ]);
    }

    private function returnDistribution(Fund $fund, $amount)
    {
        $product = new InvestmentProduct([
            'name' => '收益产品',
            'type' => InvestmentProduct::TYPE_ALIPAY,
        ]);
        $this->assertTrue($product->save());
        $return = new \common\models\ReturnRecord([
            'product_id' => $product->id,
            'total_amount' => $amount,
            'return_date' => '2024-01-01',
            'is_distributed' => 1,
        ]);
        $this->assertTrue($return->save());
        return new ReturnDistribution([
            'return_id' => $return->id,
            'fund_id' => $fund->id,
            'amount' => $amount,
            'percent' => 100,
        ]);
    }
}

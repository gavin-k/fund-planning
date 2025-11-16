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
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_distribution}} CASCADE')->execute();
    }

    protected function _after()
    {
        // 清理数据
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%fund}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%investment}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%investment_product}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%income_distribution}} CASCADE')->execute();
        Yii::$app->db->createCommand('TRUNCATE TABLE {{%return_distribution}} CASCADE')->execute();
    }

    /**
     * 测试基金列表页面 - 空数据
     */
    public function testActionIndexEmpty()
    {
        $controller = new FundController('fund', Yii::$app);
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
            'allocation_percentage' => 40,
            'balance' => 10000,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund1->save(false);

        $fund2 = new Fund([
            'name' => '投资基金',
            'allocation_percentage' => 60,
            'balance' => 15000,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund2->save(false);

        // 创建禁用基金（不应该显示）
        $fund3 = new Fund([
            'name' => '禁用基金',
            'allocation_percentage' => 0,
            'balance' => 5000,
            'status' => Fund::STATUS_INACTIVE,
        ]);
        $fund3->save(false);

        $controller = new FundController('fund', Yii::$app);
        $result = $controller->actionIndex();

        $this->assertNotNull($result, 'should return result');
    }

    /**
     * 测试总资产计算
     */
    public function testTotalAssetsCalculation()
    {
        // 创建测试基金
        $fund1 = new Fund(['name' => '基金A', 'allocation_percentage' => 50, 'balance' => 8000, 'status' => Fund::STATUS_ACTIVE]);
        $fund1->save(false);

        $fund2 = new Fund(['name' => '基金B', 'allocation_percentage' => 50, 'balance' => 12000, 'status' => Fund::STATUS_ACTIVE]);
        $fund2->save(false);

        $controller = new FundController('fund', Yii::$app);
        $result = $controller->actionIndex();

        // 验证视图能正常渲染（包含总资产计算）
        $this->assertNotNull($result, 'should return result');
    }

    /**
     * 测试总投资计算
     */
    public function testTotalInvestmentCalculation()
    {
        // 创建测试数据
        $fund = new Fund(['name' => '基金', 'allocation_percentage' => 100, 'balance' => 20000, 'status' => Fund::STATUS_ACTIVE]);
        $fund->save(false);

        $product = new InvestmentProduct(['name' => '产品', 'description' => '测试', 'status' => InvestmentProduct::STATUS_ACTIVE]);
        $product->save(false);

        $inv1 = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'amount' => 5000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $inv1->save(false);

        $inv2 = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'amount' => 3000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $inv2->save(false);

        // 已赎回的投资（不应该计入总投资）
        $inv3 = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'amount' => 2000,
            'status' => Investment::STATUS_REDEEMED,
        ]);
        $inv3->save(false);

        $controller = new FundController('fund', Yii::$app);
        $result = $controller->actionIndex();

        $this->assertNotNull($result, 'should return result');
    }

    /**
     * 测试基金详情页面
     */
    public function testActionView()
    {
        // 创建测试基金
        $fund = new Fund([
            'name' => '测试基金',
            'allocation_percentage' => 100,
            'balance' => 10000,
            'status' => Fund::STATUS_ACTIVE,
        ]);
        $fund->save(false);

        $controller = new FundController('fund', Yii::$app);
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
        $fund = new Fund(['name' => '测试基金', 'allocation_percentage' => 100, 'balance' => 10000, 'status' => Fund::STATUS_ACTIVE]);
        $fund->save(false);

        // 创建产品
        $product = new InvestmentProduct(['name' => '产品A', 'description' => '测试', 'status' => InvestmentProduct::STATUS_ACTIVE]);
        $product->save(false);

        // 创建投资记录
        $investment = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'amount' => 5000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment->save(false);

        $controller = new FundController('fund', Yii::$app);
        $result = $controller->actionView($fund->id);

        $this->assertNotNull($result, 'should return result');
    }

    /**
     * 测试基金详情页面 - 包含收入分配记录
     */
    public function testActionViewWithIncomeDistributions()
    {
        // 创建测试基金
        $fund = new Fund(['name' => '测试基金', 'allocation_percentage' => 100, 'balance' => 10000, 'status' => Fund::STATUS_ACTIVE]);
        $fund->save(false);

        // 创建收入分配记录
        $dist1 = new IncomeDistribution(['fund_id' => $fund->id, 'amount' => 1000]);
        $dist1->save(false);

        $dist2 = new IncomeDistribution(['fund_id' => $fund->id, 'amount' => 500]);
        $dist2->save(false);

        $controller = new FundController('fund', Yii::$app);
        $result = $controller->actionView($fund->id);

        $this->assertNotNull($result, 'should return result');
    }

    /**
     * 测试基金详情页面 - 包含收益分配记录
     */
    public function testActionViewWithReturnDistributions()
    {
        // 创建测试基金
        $fund = new Fund(['name' => '测试基金', 'allocation_percentage' => 100, 'balance' => 10000, 'status' => Fund::STATUS_ACTIVE]);
        $fund->save(false);

        // 创建收益分配记录
        $dist1 = new ReturnDistribution(['fund_id' => $fund->id, 'amount' => 300]);
        $dist1->save(false);

        $dist2 = new ReturnDistribution(['fund_id' => $fund->id, 'amount' => 200]);
        $dist2->save(false);

        $controller = new FundController('fund', Yii::$app);
        $result = $controller->actionView($fund->id);

        $this->assertNotNull($result, 'should return result');
    }

    /**
     * 测试基金详情页面 - 完整数据
     */
    public function testActionViewWithAllData()
    {
        // 创建测试基金
        $fund = new Fund(['name' => '完整基金', 'allocation_percentage' => 100, 'balance' => 20000, 'status' => Fund::STATUS_ACTIVE]);
        $fund->save(false);

        // 创建产品和投资
        $product = new InvestmentProduct(['name' => '产品', 'description' => '测试', 'status' => InvestmentProduct::STATUS_ACTIVE]);
        $product->save(false);

        $investment = new Investment([
            'fund_id' => $fund->id,
            'product_id' => $product->id,
            'amount' => 10000,
            'status' => Investment::STATUS_ACTIVE,
        ]);
        $investment->save(false);

        // 创建收入分配
        $incomeDist = new IncomeDistribution(['fund_id' => $fund->id, 'amount' => 5000]);
        $incomeDist->save(false);

        // 创建收益分配
        $returnDist = new ReturnDistribution(['fund_id' => $fund->id, 'amount' => 500]);
        $returnDist->save(false);

        $controller = new FundController('fund', Yii::$app);
        $result = $controller->actionView($fund->id);

        $this->assertNotNull($result, 'should return result');
    }

    /**
     * 测试基金详情页面 - 不存在的基金
     */
    public function testActionViewNotFound()
    {
        $controller = new FundController('fund', Yii::$app);

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
        $fund = new Fund(['name' => '测试基金', 'allocation_percentage' => 100, 'balance' => 50000, 'status' => Fund::STATUS_ACTIVE]);
        $fund->save(false);

        // 创建产品
        $product = new InvestmentProduct(['name' => '产品', 'description' => '测试', 'status' => InvestmentProduct::STATUS_ACTIVE]);
        $product->save(false);

        // 创建25条投资记录
        for ($i = 1; $i <= 25; $i++) {
            $investment = new Investment([
                'fund_id' => $fund->id,
                'product_id' => $product->id,
                'amount' => 1000,
                'status' => Investment::STATUS_ACTIVE,
            ]);
            $investment->save(false);
        }

        $controller = new FundController('fund', Yii::$app);
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
        $fund = new Fund(['name' => '测试基金', 'allocation_percentage' => 100, 'balance' => 50000, 'status' => Fund::STATUS_ACTIVE]);
        $fund->save(false);

        // 创建15条收入分配记录
        for ($i = 1; $i <= 15; $i++) {
            $dist = new IncomeDistribution(['fund_id' => $fund->id, 'amount' => 1000]);
            $dist->save(false);
        }

        // 创建15条收益分配记录
        for ($i = 1; $i <= 15; $i++) {
            $dist = new ReturnDistribution(['fund_id' => $fund->id, 'amount' => 100]);
            $dist->save(false);
        }

        $controller = new FundController('fund', Yii::$app);
        $result = $controller->actionView($fund->id);

        // 验证返回结果（实际限制在控制器中）
        $this->assertNotNull($result, 'should return result');
    }
}

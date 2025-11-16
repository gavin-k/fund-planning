<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\Fund;
use common\models\Investment;
use common\models\Income;
use common\models\ReturnRecord;
use common\models\FinancialGoal;
use backend\components\ChartHelper;
use backend\components\AnalysisHelper;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        // 1. 总资产统计
        $totalAssets = Fund::find()->sum('current_balance') ?: 0;

        // 2. 各基金余额
        $funds = Fund::find()
            ->select(['id', 'name', 'current_balance', 'allocation_percent'])
            ->orderBy(['allocation_percent' => SORT_DESC])
            ->all();

        // 3. 总投资金额
        $totalInvestment = Investment::find()
            ->where(['status' => Investment::STATUS_ACTIVE])
            ->sum('amount') ?: 0;

        // 4. 本月收入
        $monthlyIncome = Income::find()
            ->where(['>=', 'income_date', date('Y-m-01')])
            ->sum('amount') ?: 0;

        // 5. 本月收益
        $monthlyReturn = ReturnRecord::find()
            ->where(['>=', 'return_date', date('Y-m-01')])
            ->sum('total_amount') ?: 0;

        // 6. 最近收入记录（最近5条）
        $recentIncomes = Income::find()
            ->orderBy(['income_date' => SORT_DESC, 'id' => SORT_DESC])
            ->limit(5)
            ->all();

        // 7. 最近投资记录（最近5条）
        $recentInvestments = Investment::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        // 8. 财务健康评分（新增）
        $healthScore = AnalysisHelper::getFinancialHealthScore();

        // 9. 智能建议（新增，最多显示3条）
        $suggestions = array_slice(AnalysisHelper::getFinancialSuggestions(), 0, 3);

        // 10. 财务目标进度（新增，最多显示3条进行中的目标）
        $activeGoals = FinancialGoal::find()
            ->where(['status' => FinancialGoal::STATUS_IN_PROGRESS])
            ->orderBy(['target_date' => SORT_ASC])
            ->limit(3)
            ->all();

        // 11. 图表数据
        $fundChartData = json_encode(ChartHelper::getFundBalanceChartData());
        $trendChartData = json_encode(ChartHelper::getMonthlyReturnTrendData());
        $investmentChartData = json_encode(ChartHelper::getInvestmentDistributionData());

        return $this->render('index', [
            'totalAssets' => $totalAssets,
            'funds' => $funds,
            'totalInvestment' => $totalInvestment,
            'monthlyIncome' => $monthlyIncome,
            'monthlyReturn' => $monthlyReturn,
            'recentIncomes' => $recentIncomes,
            'recentInvestments' => $recentInvestments,
            'healthScore' => $healthScore,
            'suggestions' => $suggestions,
            'activeGoals' => $activeGoals,
            'fundChartData' => $fundChartData,
            'trendChartData' => $trendChartData,
            'investmentChartData' => $investmentChartData,
        ]);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}

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
        $totalAssets = Fund::find()->sum('balance') ?: 0;

        // 2. 各基金余额
        $funds = Fund::find()
            ->select(['id', 'name', 'balance', 'allocation_percentage'])
            ->orderBy(['allocation_percentage' => SORT_DESC])
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
            ->sum('amount') ?: 0;

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

        return $this->render('index', [
            'totalAssets' => $totalAssets,
            'funds' => $funds,
            'totalInvestment' => $totalInvestment,
            'monthlyIncome' => $monthlyIncome,
            'monthlyReturn' => $monthlyReturn,
            'recentIncomes' => $recentIncomes,
            'recentInvestments' => $recentInvestments,
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

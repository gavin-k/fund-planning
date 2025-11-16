<?php
namespace frontend\controllers;

use Yii;
use common\models\Fund;
use common\models\Investment;
use common\models\IncomeDistribution;
use common\models\ReturnDistribution;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;

/**
 * Fund Controller for Frontend
 * 前台基金控制器
 */
class FundController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * 基金列表
     */
    public function actionIndex()
    {
        $funds = Fund::find()
            ->where(['status' => Fund::STATUS_ACTIVE])
            ->orderBy(['allocation_percentage' => SORT_DESC])
            ->all();

        // 计算总资产
        $totalAssets = Fund::find()->sum('balance') ?: 0;

        // 总投资
        $totalInvestment = Investment::find()
            ->where(['status' => Investment::STATUS_ACTIVE])
            ->sum('amount') ?: 0;

        return $this->render('index', [
            'funds' => $funds,
            'totalAssets' => $totalAssets,
            'totalInvestment' => $totalInvestment,
        ]);
    }

    /**
     * 基金详情
     */
    public function actionView($id)
    {
        $fund = $this->findModel($id);

        // 该基金的投资记录
        $investments = Investment::find()
            ->where(['fund_id' => $id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(20)
            ->all();

        // 该基金的收入分配记录
        $incomeDistributions = IncomeDistribution::find()
            ->where(['fund_id' => $id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        // 该基金的收益分配记录
        $returnDistributions = ReturnDistribution::find()
            ->where(['fund_id' => $id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        return $this->render('view', [
            'fund' => $fund,
            'investments' => $investments,
            'incomeDistributions' => $incomeDistributions,
            'returnDistributions' => $returnDistributions,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = Fund::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('基金不存在。');
    }
}

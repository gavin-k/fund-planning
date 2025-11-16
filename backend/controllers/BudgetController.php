<?php
namespace backend\controllers;

use Yii;
use common\models\Budget;
use common\models\Fund;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * BudgetController implements the CRUD actions for Budget model.
 * 预算管理控制器
 */
class BudgetController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                        'update-actual' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Budget models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Budget::find()
                ->with(['fund'])
                ->orderBy(['status' => SORT_ASC, 'start_date' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        // 统计数据
        $statistics = [
            'total' => Budget::find()->count(),
            'active' => Budget::find()->where(['status' => Budget::STATUS_ACTIVE])->count(),
            'over_budget' => Budget::find()
                ->where(['status' => Budget::STATUS_ACTIVE])
                ->andWhere('actual_amount > budget_amount')
                ->count(),
            'warning' => Budget::find()
                ->where(['status' => Budget::STATUS_ACTIVE])
                ->andWhere('actual_amount >= budget_amount * 0.9')
                ->andWhere('actual_amount <= budget_amount')
                ->count(),
        ];

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Displays a single Budget model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // 获取该预算周期内的投资记录
        $investments = \common\models\Investment::find()
            ->where(['>=', 'investment_date', $model->start_date])
            ->andWhere(['<=', 'investment_date', $model->end_date]);

        if ($model->fund_id) {
            $investments->andWhere(['fund_id' => $model->fund_id]);
        }

        $investmentProvider = new ActiveDataProvider([
            'query' => $investments,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('view', [
            'model' => $model,
            'investmentProvider' => $investmentProvider,
        ]);
    }

    /**
     * Creates a new Budget model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Budget();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', '预算创建成功！');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
            // 默认设置为当月
            $model->start_date = date('Y-m-01');
            $model->end_date = date('Y-m-t');
            $model->period_type = Budget::PERIOD_MONTH;
        }

        return $this->render('create', [
            'model' => $model,
            'fundList' => $this->getFundList(),
        ]);
    }

    /**
     * Updates an existing Budget model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', '预算更新成功！');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'fundList' => $this->getFundList(),
        ]);
    }

    /**
     * Deletes an existing Budget model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', '预算已删除。');

        return $this->redirect(['index']);
    }

    /**
     * 手动更新实际金额
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdateActual($id)
    {
        $model = $this->findModel($id);

        if ($model->updateActualAmount()) {
            Yii::$app->session->setFlash('success', '实际金额已更新！');
        } else {
            Yii::$app->session->setFlash('error', '更新失败，请重试。');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the Budget model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Budget the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Budget::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * 获取基金列表（用于下拉选择）
     * @return array
     */
    private function getFundList()
    {
        $funds = Fund::find()
            ->where(['status' => Fund::STATUS_ACTIVE])
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();

        return ['' => '全局预算（所有基金）'] + $funds;
    }
}

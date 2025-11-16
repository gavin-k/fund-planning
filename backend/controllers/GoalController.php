<?php
namespace backend\controllers;

use Yii;
use common\models\FinancialGoal;
use common\models\Fund;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * GoalController implements the CRUD actions for FinancialGoal model.
 * 财务目标管理控制器
 */
class GoalController extends Controller
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
                        'complete' => ['POST'],
                        'sync' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all FinancialGoal models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => FinancialGoal::find()
                ->with(['fund'])
                ->orderBy(['status' => SORT_ASC, 'target_date' => SORT_ASC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        // 统计数据
        $statistics = [
            'total' => FinancialGoal::find()->count(),
            'in_progress' => FinancialGoal::find()->where(['status' => FinancialGoal::STATUS_IN_PROGRESS])->count(),
            'completed' => FinancialGoal::find()->where(['status' => FinancialGoal::STATUS_COMPLETED])->count(),
            'overdue' => FinancialGoal::find()
                ->where(['status' => FinancialGoal::STATUS_IN_PROGRESS])
                ->andWhere(['<', 'target_date', date('Y-m-d')])
                ->count(),
        ];

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Displays a single FinancialGoal model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // 计算额外的统计信息
        $stats = [
            'progress' => $model->getProgress(),
            'remaining_amount' => $model->getRemainingAmount(),
            'remaining_days' => $model->getRemainingDays(),
            'suggested_monthly' => $model->getSuggestedMonthlySaving(),
            'estimated_completion' => $model->getEstimatedCompletionDate(),
            'is_overdue' => $model->isOverdue(),
            'is_due_soon' => $model->isDueSoon(),
        ];

        return $this->render('view', [
            'model' => $model,
            'stats' => $stats,
        ]);
    }

    /**
     * Creates a new FinancialGoal model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new FinancialGoal();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', '财务目标创建成功！');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'fundList' => $this->getFundList(),
        ]);
    }

    /**
     * Updates an existing FinancialGoal model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', '财务目标更新成功！');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'fundList' => $this->getFundList(),
        ]);
    }

    /**
     * Deletes an existing FinancialGoal model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', '财务目标已删除。');

        return $this->redirect(['index']);
    }

    /**
     * 标记目标为已完成
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionComplete($id)
    {
        $model = $this->findModel($id);

        if ($model->markAsCompleted()) {
            Yii::$app->session->setFlash('success', '恭喜！目标已完成！');
        } else {
            Yii::$app->session->setFlash('error', '操作失败，请重试。');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * 从关联基金同步当前金额
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionSync($id)
    {
        $model = $this->findModel($id);

        if ($model->syncCurrentAmount()) {
            Yii::$app->session->setFlash('success', '金额已同步！');
        } else {
            Yii::$app->session->setFlash('error', '同步失败，请检查是否关联了基金。');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the FinancialGoal model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return FinancialGoal the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FinancialGoal::findOne(['id' => $id])) !== null) {
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
        return Fund::find()
            ->where(['status' => Fund::STATUS_ACTIVE])
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();
    }
}

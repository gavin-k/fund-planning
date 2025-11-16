<?php

namespace backend\controllers;

use Yii;
use common\models\Investment;
use common\models\Fund;
use common\models\InvestmentProduct;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * InvestmentController implements the CRUD actions for Investment model.
 */
class InvestmentController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'withdraw' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Investment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Investment::find()->with(['fund', 'product'])->orderBy(['investment_date' => SORT_DESC, 'created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Investment model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Investment model.
     * 从基金投资到理财产品
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Investment();
        $model->investment_date = date('Y-m-d');

        if ($model->load(Yii::$app->request->post())) {
            // 开始事务
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', '投资成功！');
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    $transaction->rollBack();
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', '投资失败：' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
            'fundList' => $this->getFundList(),
            'productList' => $this->getProductList(),
        ]);
    }

    /**
     * Updates an existing Investment model.
     * 注意：生效中的投资不支持修改
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->status == Investment::STATUS_ACTIVE) {
            Yii::$app->session->setFlash('error', '生效中的投资无法修改！如需修改，请先赎回。');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', '投资更新成功！');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'fundList' => $this->getFundList(),
            'productList' => $this->getProductList(),
        ]);
    }

    /**
     * Deletes an existing Investment model.
     * 注意：生效中的投资不支持删除
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->status == Investment::STATUS_ACTIVE) {
            Yii::$app->session->setFlash('error', '生效中的投资无法删除！如需删除，请先赎回。');
            return $this->redirect(['index']);
        }

        try {
            $model->delete();
            Yii::$app->session->setFlash('success', '投资删除成功！');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', '删除失败：' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Withdraw an investment
     * 赎回投资，资金返回基金
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionWithdraw($id)
    {
        $model = $this->findModel($id);

        if ($model->withdraw()) {
            Yii::$app->session->setFlash('success', '赎回成功！资金已返回基金账户。');
        } else {
            Yii::$app->session->setFlash('error', '赎回失败：' . implode(', ', $model->getFirstErrors()));
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the Investment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Investment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Investment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('请求的投资记录不存在。');
    }

    /**
     * 获取启用的基金列表
     */
    protected function getFundList()
    {
        return ArrayHelper::map(
            Fund::find()->where(['status' => Fund::STATUS_ACTIVE])->all(),
            'id',
            function ($model) {
                return $model->name . ' (可用余额: ¥' . number_format($model->getAvailableBalance(), 2) . ')';
            }
        );
    }

    /**
     * 获取使用中的产品列表
     */
    protected function getProductList()
    {
        return ArrayHelper::map(
            InvestmentProduct::find()->where(['status' => InvestmentProduct::STATUS_ACTIVE])->all(),
            'id',
            function ($model) {
                return $model->name . ' (' . $model->getTypeText() . ')';
            }
        );
    }
}

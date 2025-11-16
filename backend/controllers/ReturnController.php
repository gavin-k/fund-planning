<?php

namespace backend\controllers;

use Yii;
use common\models\ReturnRecord;
use common\models\InvestmentProduct;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * ReturnController implements the CRUD actions for ReturnRecord model.
 */
class ReturnController extends Controller
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
                ],
            ],
        ];
    }

    /**
     * Lists all ReturnRecord models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ReturnRecord::find()->with(['product'])->orderBy(['return_date' => SORT_DESC, 'created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ReturnRecord model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // 获取收益分配记录
        $distributionProvider = new ActiveDataProvider([
            'query' => $model->getDistributions()->orderBy(['fund_id' => SORT_ASC]),
            'pagination' => false,
        ]);

        return $this->render('view', [
            'model' => $model,
            'distributionProvider' => $distributionProvider,
        ]);
    }

    /**
     * Creates a new ReturnRecord model.
     * 创建后会自动按投资比例分配收益到各基金
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ReturnRecord();
        $model->return_date = date('Y-m-d');

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                if ($model->is_distributed) {
                    Yii::$app->session->setFlash('success', '收益记录成功，已自动按投资比例分配到各基金！');
                } else {
                    Yii::$app->session->setFlash('warning', '收益记录成功，但自动分配失败：' . implode(', ', $model->getFirstErrors()));
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'productList' => $this->getProductList(),
        ]);
    }

    /**
     * Updates an existing ReturnRecord model.
     * 注意：不支持修改已分配的收益
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->is_distributed) {
            Yii::$app->session->setFlash('error', '该收益已分配，无法修改！');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', '收益更新成功！');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'productList' => $this->getProductList(),
        ]);
    }

    /**
     * Deletes an existing ReturnRecord model.
     * 注意：不支持删除已分配的收益
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->is_distributed) {
            Yii::$app->session->setFlash('error', '该收益已分配，无法删除！');
            return $this->redirect(['index']);
        }

        try {
            $model->delete();
            Yii::$app->session->setFlash('success', '收益删除成功！');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', '删除失败：' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the ReturnRecord model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ReturnRecord the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ReturnRecord::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('请求的收益记录不存在。');
    }

    /**
     * 获取有投资的产品列表
     */
    protected function getProductList()
    {
        return ArrayHelper::map(
            InvestmentProduct::find()
                ->joinWith('activeInvestments')
                ->where(['investment_product.status' => InvestmentProduct::STATUS_ACTIVE])
                ->groupBy('investment_product.id')
                ->having('COUNT(investment.id) > 0')
                ->all(),
            'id',
            function ($model) {
                $amount = $model->calculateCurrentAmount();
                return $model->name . ' (投资总额: ¥' . number_format($amount, 2) . ')';
            }
        );
    }
}

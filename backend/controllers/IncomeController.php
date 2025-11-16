<?php

namespace backend\controllers;

use Yii;
use common\models\Income;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * IncomeController implements the CRUD actions for Income model.
 */
class IncomeController extends Controller
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
     * Lists all Income models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Income::find()->orderBy(['income_date' => SORT_DESC, 'created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Income model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // 获取分配记录
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
     * Creates a new Income model.
     * 创建后会自动分配到各基金
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Income();
        $model->income_date = date('Y-m-d');

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                if ($model->is_distributed) {
                    Yii::$app->session->setFlash('success', '收入记录成功，已自动分配到各基金！');
                } else {
                    Yii::$app->session->setFlash('warning', '收入记录成功，但自动分配失败：' . implode(', ', $model->getFirstErrors()));
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Income model.
     * 注意：不支持修改已分配的收入
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->is_distributed) {
            Yii::$app->session->setFlash('error', '该收入已分配，无法修改！');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', '收入更新成功！');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Income model.
     * 注意：不支持删除已分配的收入
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->is_distributed) {
            Yii::$app->session->setFlash('error', '该收入已分配，无法删除！');
            return $this->redirect(['index']);
        }

        try {
            $model->delete();
            Yii::$app->session->setFlash('success', '收入删除成功！');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', '删除失败：' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Income model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Income the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Income::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('请求的收入记录不存在。');
    }
}

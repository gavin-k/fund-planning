<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use backend\components\ReportHelper;

/**
 * 报表管理控制器
 */
class ReportController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * 报表首页 - 选择报表类型
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'availableMonths' => ReportHelper::getAvailableMonths(12),
            'availableYears' => ReportHelper::getAvailableYears(),
            'quarters' => ReportHelper::getQuarters(),
        ]);
    }

    /**
     * 月度报表
     *
     * @param string|null $month 月份 (YYYY-MM)，默认当前月
     * @return string
     */
    public function actionMonthly($month = null)
    {
        if ($month === null) {
            $month = date('Y-m');
        }

        // 验证月份格式
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            Yii::$app->session->setFlash('error', '无效的月份格式');
            return $this->redirect(['index']);
        }

        // 生成报表
        $report = ReportHelper::getMonthlyReport($month);

        return $this->render('monthly', [
            'report' => $report,
            'month' => $month,
            'availableMonths' => ReportHelper::getAvailableMonths(12),
        ]);
    }

    /**
     * 季度报表
     *
     * @param int|null $year 年份
     * @param int|null $quarter 季度 (1-4)
     * @return string
     */
    public function actionQuarterly($year = null, $quarter = null)
    {
        if ($year === null) {
            $year = date('Y');
        }
        if ($quarter === null) {
            $quarter = ceil(date('n') / 3); // 当前季度
        }

        // 验证参数
        if ($quarter < 1 || $quarter > 4) {
            Yii::$app->session->setFlash('error', '无效的季度');
            return $this->redirect(['index']);
        }

        // 生成报表
        $report = ReportHelper::getQuarterlyReport($year, $quarter);

        return $this->render('quarterly', [
            'report' => $report,
            'year' => $year,
            'quarter' => $quarter,
            'availableYears' => ReportHelper::getAvailableYears(),
            'quarters' => ReportHelper::getQuarters(),
        ]);
    }

    /**
     * 年度报表
     *
     * @param int|null $year 年份，默认当前年
     * @return string
     */
    public function actionAnnual($year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        // 验证年份
        if (!is_numeric($year) || $year < 2000 || $year > 2100) {
            Yii::$app->session->setFlash('error', '无效的年份');
            return $this->redirect(['index']);
        }

        // 生成报表
        $report = ReportHelper::getAnnualReport($year);

        return $this->render('annual', [
            'report' => $report,
            'year' => $year,
            'availableYears' => ReportHelper::getAvailableYears(),
        ]);
    }

    /**
     * 自定义周期报表
     *
     * @return string
     */
    public function actionCustom()
    {
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');

        // 如果没有提供日期，显示表单
        if ($startDate === null || $endDate === null) {
            return $this->render('custom-form');
        }

        // 验证日期格式
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            Yii::$app->session->setFlash('error', '无效的日期格式');
            return $this->render('custom-form');
        }

        // 验证日期范围
        if (strtotime($startDate) > strtotime($endDate)) {
            Yii::$app->session->setFlash('error', '开始日期不能晚于结束日期');
            return $this->render('custom-form');
        }

        // 生成报表
        $report = ReportHelper::getCustomReport($startDate, $endDate);

        return $this->render('custom', [
            'report' => $report,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * 导出报表（预留接口，后续集成PDF导出）
     *
     * @param string $type 报表类型
     * @param string $format 导出格式 (pdf/excel)
     * @return mixed
     */
    public function actionExport($type, $format = 'pdf')
    {
        // TODO: 实现PDF/Excel导出功能
        Yii::$app->session->setFlash('info', 'PDF导出功能将在下个版本实现');
        return $this->redirect(['index']);
    }
}

<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%budget}}".
 *
 * @property int $id
 * @property int|null $fund_id 关联基金ID
 * @property string $period_type 周期类型
 * @property float $budget_amount 预算金额
 * @property float $actual_amount 实际金额
 * @property string $start_date 开始日期
 * @property string $end_date 结束日期
 * @property string|null $description 预算说明
 * @property int $status 状态:10=生效中,20=已结束,0=已停用
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Fund $fund
 */
class Budget extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;
    const STATUS_ENDED = 20;

    const PERIOD_MONTH = 'month';
    const PERIOD_QUARTER = 'quarter';
    const PERIOD_YEAR = 'year';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%budget}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['period_type', 'budget_amount', 'start_date', 'end_date'], 'required'],
            [['fund_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['budget_amount', 'actual_amount'], 'number', 'min' => 0],
            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
            [['description'], 'string'],
            [['period_type'], 'string', 'max' => 20],
            [['period_type'], 'in', 'range' => [self::PERIOD_MONTH, self::PERIOD_QUARTER, self::PERIOD_YEAR]],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['actual_amount'], 'default', 'value' => 0],
            [['status'], 'in', 'range' => [self::STATUS_INACTIVE, self::STATUS_ACTIVE, self::STATUS_ENDED]],
            [['fund_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fund::class, 'targetAttribute' => ['fund_id' => 'id']],
            ['end_date', 'compare', 'compareAttribute' => 'start_date', 'operator' => '>=', 'message' => '结束日期必须大于等于开始日期'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fund_id' => '关联基金',
            'period_type' => '周期类型',
            'budget_amount' => '预算金额(¥)',
            'actual_amount' => '实际金额(¥)',
            'start_date' => '开始日期',
            'end_date' => '结束日期',
            'description' => '预算说明',
            'status' => '状态',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * Gets query for [[Fund]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFund()
    {
        return $this->hasOne(Fund::class, ['id' => 'fund_id']);
    }

    /**
     * 获取状态文本
     * @return string
     */
    public function getStatusText()
    {
        $statusList = self::getStatusList();
        return $statusList[$this->status] ?? '未知';
    }

    /**
     * 获取状态列表
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_INACTIVE => '已停用',
            self::STATUS_ACTIVE => '生效中',
            self::STATUS_ENDED => '已结束',
        ];
    }

    /**
     * 获取周期类型文本
     * @return string
     */
    public function getPeriodTypeText()
    {
        $typeList = self::getPeriodTypeList();
        return $typeList[$this->period_type] ?? '未知';
    }

    /**
     * 获取周期类型列表
     * @return array
     */
    public static function getPeriodTypeList()
    {
        return [
            self::PERIOD_MONTH => '月度',
            self::PERIOD_QUARTER => '季度',
            self::PERIOD_YEAR => '年度',
        ];
    }

    /**
     * 计算预算使用率（百分比）
     * @return float
     */
    public function getUsageRate()
    {
        if ($this->budget_amount <= 0) {
            return 0;
        }
        $rate = ($this->actual_amount / $this->budget_amount) * 100;
        return round($rate, 2);
    }

    /**
     * 计算剩余预算
     * @return float
     */
    public function getRemainingBudget()
    {
        return max(0, $this->budget_amount - $this->actual_amount);
    }

    /**
     * 检查是否超支
     * @return bool
     */
    public function isOverBudget()
    {
        return $this->actual_amount > $this->budget_amount;
    }

    /**
     * 计算超支金额
     * @return float
     */
    public function getOverBudgetAmount()
    {
        if (!$this->isOverBudget()) {
            return 0;
        }
        return $this->actual_amount - $this->budget_amount;
    }

    /**
     * 更新实际金额（从投资记录计算）
     * @return bool
     */
    public function updateActualAmount()
    {
        $query = Investment::find()
            ->where(['status' => Investment::STATUS_ACTIVE])
            ->andWhere(['>=', 'investment_date', $this->start_date])
            ->andWhere(['<=', 'investment_date', $this->end_date]);

        if ($this->fund_id) {
            $query->andWhere(['fund_id' => $this->fund_id]);
        }

        $this->actual_amount = $query->sum('amount') ?: 0;
        return $this->save(false, ['actual_amount']);
    }

    /**
     * 检查预算是否在有效期内
     * @return bool
     */
    public function isInPeriod()
    {
        $today = date('Y-m-d');
        return $today >= $this->start_date && $today <= $this->end_date;
    }

    /**
     * 计算剩余天数
     * @return int
     */
    public function getRemainingDays()
    {
        $endTimestamp = strtotime($this->end_date);
        $today = strtotime(date('Y-m-d'));
        $diff = $endTimestamp - $today;
        return max(0, ceil($diff / 86400));
    }

    /**
     * 获取预算状态标签（用于显示）
     * @return array ['class' => '样式类', 'text' => '文本']
     */
    public function getBudgetStatusLabel()
    {
        $usageRate = $this->getUsageRate();

        if ($this->isOverBudget()) {
            return ['class' => 'danger', 'text' => '超支 ' . round($usageRate - 100, 1) . '%'];
        }

        if ($usageRate >= 90) {
            return ['class' => 'warning', 'text' => '即将超支 (' . round($usageRate, 1) . '%)'];
        }

        if ($usageRate >= 70) {
            return ['class' => 'info', 'text' => '正常 (' . round($usageRate, 1) . '%)'];
        }

        return ['class' => 'success', 'text' => '充足 (' . round($usageRate, 1) . '%)'];
    }
}

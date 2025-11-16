<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%financial_goal}}".
 *
 * @property int $id
 * @property string $name 目标名称
 * @property float $target_amount 目标金额
 * @property float $current_amount 当前金额
 * @property string $target_date 目标日期
 * @property int|null $fund_id 关联基金ID
 * @property string|null $description 目标描述
 * @property int $status 状态:10=进行中,20=已完成,0=已取消
 * @property int|null $completed_at 完成时间
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Fund $fund
 */
class FinancialGoal extends ActiveRecord
{
    const STATUS_CANCELLED = 0;
    const STATUS_IN_PROGRESS = 10;
    const STATUS_COMPLETED = 20;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%financial_goal}}';
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
            [['name', 'target_amount', 'target_date'], 'required'],
            [['target_amount', 'current_amount'], 'number', 'min' => 0],
            [['target_date'], 'date', 'format' => 'php:Y-m-d'],
            [['fund_id', 'status', 'completed_at', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 100],
            [['status'], 'default', 'value' => self::STATUS_IN_PROGRESS],
            [['current_amount'], 'default', 'value' => 0],
            [['status'], 'in', 'range' => [self::STATUS_CANCELLED, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED]],
            [['fund_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fund::class, 'targetAttribute' => ['fund_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '目标名称',
            'target_amount' => '目标金额(¥)',
            'current_amount' => '当前金额(¥)',
            'target_date' => '目标日期',
            'fund_id' => '关联基金',
            'description' => '目标描述',
            'status' => '状态',
            'completed_at' => '完成时间',
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
            self::STATUS_CANCELLED => '已取消',
            self::STATUS_IN_PROGRESS => '进行中',
            self::STATUS_COMPLETED => '已完成',
        ];
    }

    /**
     * 计算完成进度（百分比）
     * @return float
     */
    public function getProgress()
    {
        if ($this->target_amount <= 0) {
            return 0;
        }
        $progress = ($this->current_amount / $this->target_amount) * 100;
        return min(100, round($progress, 2));
    }

    /**
     * 计算剩余金额
     * @return float
     */
    public function getRemainingAmount()
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    /**
     * 计算剩余天数
     * @return int
     */
    public function getRemainingDays()
    {
        $targetTimestamp = strtotime($this->target_date);
        $today = strtotime(date('Y-m-d'));
        $diff = $targetTimestamp - $today;
        return max(0, ceil($diff / 86400));
    }

    /**
     * 计算建议月储蓄额
     * @return float
     */
    public function getSuggestedMonthlySaving()
    {
        $remainingAmount = $this->getRemainingAmount();
        $remainingDays = $this->getRemainingDays();

        if ($remainingDays <= 0) {
            return $remainingAmount;
        }

        $remainingMonths = max(1, ceil($remainingDays / 30));
        return round($remainingAmount / $remainingMonths, 2);
    }

    /**
     * 计算预计完成日期（按当前速度）
     * @return string|null
     */
    public function getEstimatedCompletionDate()
    {
        if ($this->current_amount <= 0) {
            return null;
        }

        $createdAt = $this->created_at;
        $daysPassed = max(1, (time() - $createdAt) / 86400);
        $dailyAverage = $this->current_amount / $daysPassed;

        if ($dailyAverage <= 0) {
            return null;
        }

        $remainingAmount = $this->getRemainingAmount();
        $daysNeeded = ceil($remainingAmount / $dailyAverage);

        return date('Y-m-d', strtotime("+{$daysNeeded} days"));
    }

    /**
     * 更新当前金额（从关联基金同步）
     * @return bool
     */
    public function syncCurrentAmount()
    {
        if (!$this->fund_id) {
            return false;
        }

        $fund = $this->fund;
        if (!$fund) {
            return false;
        }

        $this->current_amount = min($fund->current_balance, $this->target_amount);
        return $this->save(false, ['current_amount']);
    }

    /**
     * 标记为已完成
     * @return bool
     */
    public function markAsCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = time();
        $this->current_amount = $this->target_amount;
        return $this->save(false, ['status', 'completed_at', 'current_amount']);
    }

    /**
     * 检查是否延期
     * @return bool
     */
    public function isOverdue()
    {
        if ($this->status == self::STATUS_COMPLETED) {
            return false;
        }

        return strtotime($this->target_date) < strtotime(date('Y-m-d'));
    }

    /**
     * 检查是否即将到期（7天内）
     * @return bool
     */
    public function isDueSoon()
    {
        if ($this->status == self::STATUS_COMPLETED) {
            return false;
        }

        $remainingDays = $this->getRemainingDays();
        return $remainingDays > 0 && $remainingDays <= 7;
    }
}

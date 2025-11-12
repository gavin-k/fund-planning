<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%fund}}".
 *
 * @property int $id
 * @property string $name 基金名称
 * @property float $allocation_percent 分配比例
 * @property float $current_balance 当前余额
 * @property string|null $description 描述
 * @property int $status 状态:10=启用,0=禁用
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Investment[] $investments
 * @property IncomeDistribution[] $incomeDistributions
 * @property ReturnDistribution[] $returnDistributions
 */
class Fund extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fund}}';
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
            [['name', 'allocation_percent'], 'required'],
            [['allocation_percent', 'current_balance'], 'number'],
            [['allocation_percent'], 'number', 'min' => 0, 'max' => 100],
            [['current_balance'], 'number', 'min' => 0],
            [['description'], 'string'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['name'], 'unique'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_INACTIVE, self::STATUS_ACTIVE]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '基金名称',
            'allocation_percent' => '分配比例(%)',
            'current_balance' => '当前余额(¥)',
            'description' => '描述',
            'status' => '状态',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 获取投资记录
     */
    public function getInvestments()
    {
        return $this->hasMany(Investment::class, ['fund_id' => 'id']);
    }

    /**
     * 获取生效中的投资
     */
    public function getActiveInvestments()
    {
        return $this->hasMany(Investment::class, ['fund_id' => 'id'])
            ->where(['status' => Investment::STATUS_ACTIVE]);
    }

    /**
     * 获取收入分配记录
     */
    public function getIncomeDistributions()
    {
        return $this->hasMany(IncomeDistribution::class, ['fund_id' => 'id']);
    }

    /**
     * 获取收益分配记录
     */
    public function getReturnDistributions()
    {
        return $this->hasMany(ReturnDistribution::class, ['fund_id' => 'id']);
    }

    /**
     * 获取状态列表
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_INACTIVE => '禁用',
            self::STATUS_ACTIVE => '启用',
        ];
    }

    /**
     * 获取状态文本
     */
    public function getStatusText()
    {
        $list = self::getStatusList();
        return isset($list[$this->status]) ? $list[$this->status] : '未知';
    }

    /**
     * 计算已投资金额
     */
    public function getInvestedAmount()
    {
        return (float)$this->getActiveInvestments()->sum('amount');
    }

    /**
     * 计算可用余额
     */
    public function getAvailableBalance()
    {
        return $this->current_balance - $this->getInvestedAmount();
    }

    /**
     * 增加余额
     * @param float $amount
     * @return bool
     */
    public function addBalance($amount)
    {
        $this->current_balance += $amount;
        return $this->save(false, ['current_balance', 'updated_at']);
    }

    /**
     * 减少余额
     * @param float $amount
     * @return bool
     */
    public function reduceBalance($amount)
    {
        if ($this->getAvailableBalance() < $amount) {
            $this->addError('current_balance', '可用余额不足');
            return false;
        }
        $this->current_balance -= $amount;
        return $this->save(false, ['current_balance', 'updated_at']);
    }
}

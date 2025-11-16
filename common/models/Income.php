<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%income}}".
 *
 * @property int $id
 * @property float $amount 收入金额
 * @property string|null $source 收入来源
 * @property string $income_date 收入日期
 * @property int $is_distributed 是否已分配:1=是,0=否
 * @property string|null $notes 备注
 * @property int $created_at
 * @property int $updated_at
 *
 * @property IncomeDistribution[] $distributions
 */
class Income extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%income}}';
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
            [['amount', 'income_date'], 'required'],
            [['amount'], 'number'],
            [['amount'], 'number', 'min' => 0.01],
            [['income_date'], 'date', 'format' => 'php:Y-m-d'],
            [['is_distributed', 'created_at', 'updated_at'], 'integer'],
            [['notes'], 'string'],
            [['source'], 'string', 'max' => 200],
            [['is_distributed'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'amount' => '收入金额(¥)',
            'source' => '收入来源',
            'income_date' => '收入日期',
            'is_distributed' => '是否已分配',
            'notes' => '备注',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 获取收入分配记录
     */
    public function getDistributions()
    {
        return $this->hasMany(IncomeDistribution::class, ['income_id' => 'id']);
    }

    /**
     * 保存后自动分配到各基金
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // 如果是新增且未分配，自动分配
        if ($insert && !$this->is_distributed) {
            $this->distributeToFunds();
        }
    }

    /**
     * 将收入按比例分配到各基金
     * @return bool
     */
    public function distributeToFunds()
    {
        if ($this->is_distributed) {
            $this->addError('is_distributed', '该收入已经分配过了');
            return false;
        }

        // 获取所有启用的基金
        $funds = Fund::find()
            ->where(['status' => Fund::STATUS_ACTIVE])
            ->all();

        if (empty($funds)) {
            $this->addError('amount', '没有启用的基金，无法分配');
            return false;
        }

        // 计算总比例
        $totalPercent = array_sum(ArrayHelper::getColumn($funds, 'allocation_percent'));

        if ($totalPercent <= 0) {
            $this->addError('amount', '基金分配比例总和为0，无法分配');
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $distributedTotal = 0;
            $distributions = [];

            foreach ($funds as $index => $fund) {
                // 计算分配金额
                if ($index === count($funds) - 1) {
                    // 最后一个基金，使用剩余金额避免精度问题
                    $distributionAmount = $this->amount - $distributedTotal;
                } else {
                    $distributionAmount = round($this->amount * ($fund->allocation_percent / $totalPercent), 2);
                }

                // 创建分配记录
                $distribution = new IncomeDistribution();
                $distribution->income_id = $this->id;
                $distribution->fund_id = $fund->id;
                $distribution->amount = $distributionAmount;
                $distribution->percent = $fund->allocation_percent;

                if (!$distribution->save()) {
                    throw new \Exception('保存分配记录失败: ' . json_encode($distribution->errors));
                }

                // 更新基金余额
                if (!$fund->addBalance($distributionAmount)) {
                    throw new \Exception('更新基金余额失败');
                }

                $distributedTotal += $distributionAmount;
                $distributions[] = $distribution;
            }

            // 标记为已分配
            $this->is_distributed = 1;
            if (!$this->save(false, ['is_distributed', 'updated_at'])) {
                throw new \Exception('更新分配状态失败');
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->addError('amount', $e->getMessage());
            return false;
        }
    }

    /**
     * 获取分配详情
     */
    public function getDistributionDetails()
    {
        $details = [];
        foreach ($this->distributions as $dist) {
            $details[] = [
                'fund_name' => $dist->fund->name,
                'amount' => $dist->amount,
                'percent' => $dist->percent,
            ];
        }
        return $details;
    }
}

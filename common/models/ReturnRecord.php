<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%return_record}}".
 *
 * @property int $id
 * @property int $product_id 产品ID
 * @property float $total_amount 总收益金额
 * @property string $return_date 收益日期
 * @property int $is_distributed 是否已分配:1=是,0=否
 * @property string|null $notes 备注
 * @property int $created_at
 * @property int $updated_at
 *
 * @property InvestmentProduct $product
 * @property ReturnDistribution[] $distributions
 */
class ReturnRecord extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%return_record}}';
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
            [['product_id', 'total_amount', 'return_date'], 'required'],
            [['product_id', 'is_distributed', 'created_at', 'updated_at'], 'integer'],
            [['total_amount'], 'number'],
            [['total_amount'], 'number', 'min' => 0.01],
            [['return_date'], 'date', 'format' => 'php:Y-m-d'],
            [['notes'], 'string'],
            [['is_distributed'], 'default', 'value' => 0],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => InvestmentProduct::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => '理财产品',
            'total_amount' => '总收益金额(¥)',
            'return_date' => '收益日期',
            'is_distributed' => '是否已分配',
            'notes' => '备注',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 获取产品
     */
    public function getProduct()
    {
        return $this->hasOne(InvestmentProduct::class, ['id' => 'product_id']);
    }

    /**
     * 获取收益分配记录
     */
    public function getDistributions()
    {
        return $this->hasMany(ReturnDistribution::class, ['return_id' => 'id']);
    }

    /**
     * 保存后自动分配收益到各基金
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
     * 将收益按投资比例分配到各基金
     * @return bool
     */
    public function distributeToFunds()
    {
        if ($this->is_distributed) {
            $this->addError('is_distributed', '该收益已经分配过了');
            return false;
        }

        // 获取该产品的所有生效投资
        $investments = Investment::find()
            ->where(['product_id' => $this->product_id, 'status' => Investment::STATUS_ACTIVE])
            ->all();

        if (empty($investments)) {
            $this->addError('product_id', '该产品没有生效中的投资，无法分配收益');
            return false;
        }

        // 按基金分组统计投资金额
        $fundInvestments = [];
        $totalInvestment = 0;

        foreach ($investments as $inv) {
            if (!isset($fundInvestments[$inv->fund_id])) {
                $fundInvestments[$inv->fund_id] = [
                    'fund' => $inv->fund,
                    'amount' => 0,
                ];
            }
            $fundInvestments[$inv->fund_id]['amount'] += $inv->amount;
            $totalInvestment += $inv->amount;
        }

        if ($totalInvestment <= 0) {
            $this->addError('product_id', '投资总额为0，无法分配收益');
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $distributedTotal = 0;
            $distributions = [];
            $fundIds = array_keys($fundInvestments);

            foreach ($fundInvestments as $index => $fundInvestment) {
                $fundId = array_search($fundInvestment, $fundInvestments);
                $investedAmount = $fundInvestment['amount'];
                $fund = $fundInvestment['fund'];

                // 计算分配比例和金额
                if ($index === count($fundInvestments) - 1) {
                    // 最后一个基金，使用剩余金额避免精度问题
                    $returnAmount = $this->total_amount - $distributedTotal;
                    $percent = ($totalInvestment > 0) ? ($investedAmount / $totalInvestment) * 100 : 0;
                } else {
                    $percent = ($totalInvestment > 0) ? ($investedAmount / $totalInvestment) * 100 : 0;
                    $returnAmount = round($this->total_amount * ($percent / 100), 2);
                }

                // 创建收益分配记录
                $distribution = new ReturnDistribution();
                $distribution->return_id = $this->id;
                $distribution->fund_id = $fundId;
                $distribution->amount = $returnAmount;
                $distribution->percent = round($percent, 2);

                if (!$distribution->save()) {
                    throw new \Exception('保存收益分配记录失败: ' . json_encode($distribution->errors));
                }

                // 更新基金余额
                if (!$fund->addBalance($returnAmount)) {
                    throw new \Exception('更新基金余额失败');
                }

                $distributedTotal += $returnAmount;
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
            $this->addError('total_amount', $e->getMessage());
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

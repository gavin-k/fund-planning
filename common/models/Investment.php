<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%investment}}".
 *
 * @property int $id
 * @property int $fund_id 基金ID
 * @property int $product_id 产品ID
 * @property float $amount 投资金额
 * @property string $investment_date 投资日期
 * @property int $status 状态:10=生效中,0=已赎回
 * @property string|null $notes 备注
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Fund $fund
 * @property InvestmentProduct $product
 */
class Investment extends ActiveRecord
{
    const STATUS_WITHDRAWN = 0;
    const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%investment}}';
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
            [['fund_id', 'product_id', 'amount', 'investment_date'], 'required'],
            [['fund_id', 'product_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number'],
            [['amount'], 'number', 'min' => 0.01],
            [['investment_date'], 'date', 'format' => 'php:Y-m-d'],
            [['notes'], 'string'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_WITHDRAWN, self::STATUS_ACTIVE]],
            [['fund_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fund::class, 'targetAttribute' => ['fund_id' => 'id']],
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
            'fund_id' => '基金',
            'product_id' => '理财产品',
            'amount' => '投资金额(¥)',
            'investment_date' => '投资日期',
            'status' => '状态',
            'notes' => '备注',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 获取基金
     */
    public function getFund()
    {
        return $this->hasOne(Fund::class, ['id' => 'fund_id']);
    }

    /**
     * 获取产品
     */
    public function getProduct()
    {
        return $this->hasOne(InvestmentProduct::class, ['id' => 'product_id']);
    }

    /**
     * 获取状态列表
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_WITHDRAWN => '已赎回',
            self::STATUS_ACTIVE => '生效中',
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
     * 保存前验证并更新基金余额
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            // 新增投资时，检查基金可用余额
            $fund = $this->fund;
            if (!$fund) {
                $this->addError('fund_id', '基金不存在');
                return false;
            }

            if ($fund->getAvailableBalance() < $this->amount) {
                $this->addError('amount', '基金可用余额不足');
                return false;
            }
        }

        return true;
    }

    /**
     * 保存后更新产品投资总额
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->product) {
            $this->product->updateCurrentAmount();
        }
    }

    /**
     * 赎回投资
     */
    public function withdraw()
    {
        if ($this->status == self::STATUS_WITHDRAWN) {
            $this->addError('status', '该投资已经赎回');
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->status = self::STATUS_WITHDRAWN;
            if (!$this->save(false)) {
                throw new \Exception('更新投资状态失败');
            }

            // 更新产品投资总额
            if ($this->product) {
                $this->product->updateCurrentAmount();
            }

            // 资金返回基金
            if ($this->fund) {
                $this->fund->addBalance($this->amount);
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->addError('status', $e->getMessage());
            return false;
        }
    }
}

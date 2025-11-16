<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%income_distribution}}".
 *
 * @property int $id
 * @property int $income_id 收入ID
 * @property int $fund_id 基金ID
 * @property float $amount 分配金额
 * @property float $percent 分配比例
 * @property int $created_at
 *
 * @property Income $income
 * @property Fund $fund
 */
class IncomeDistribution extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%income_distribution}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['income_id', 'fund_id', 'amount', 'percent'], 'required'],
            [['income_id', 'fund_id', 'created_at'], 'integer'],
            [['amount', 'percent'], 'number'],
            [['income_id'], 'exist', 'skipOnError' => true, 'targetClass' => Income::class, 'targetAttribute' => ['income_id' => 'id']],
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
            'income_id' => '收入',
            'fund_id' => '基金',
            'amount' => '分配金额(¥)',
            'percent' => '分配比例(%)',
            'created_at' => '分配时间',
        ];
    }

    /**
     * 获取收入记录
     */
    public function getIncome()
    {
        return $this->hasOne(Income::class, ['id' => 'income_id']);
    }

    /**
     * 获取基金
     */
    public function getFund()
    {
        return $this->hasOne(Fund::class, ['id' => 'fund_id']);
    }

    /**
     * 保存前设置创建时间
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            $this->created_at = time();
        }

        return true;
    }
}

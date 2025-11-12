<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%return_distribution}}".
 *
 * @property int $id
 * @property int $return_id 收益ID
 * @property int $fund_id 基金ID
 * @property float $amount 分配金额
 * @property float $percent 分配比例
 * @property int $created_at
 *
 * @property ReturnRecord $return
 * @property Fund $fund
 */
class ReturnDistribution extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%return_distribution}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['return_id', 'fund_id', 'amount', 'percent'], 'required'],
            [['return_id', 'fund_id', 'created_at'], 'integer'],
            [['amount', 'percent'], 'number'],
            [['return_id'], 'exist', 'skipOnError' => true, 'targetClass' => ReturnRecord::class, 'targetAttribute' => ['return_id' => 'id']],
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
            'return_id' => '收益',
            'fund_id' => '基金',
            'amount' => '分配金额(¥)',
            'percent' => '分配比例(%)',
            'created_at' => '分配时间',
        ];
    }

    /**
     * 获取收益记录
     */
    public function getReturn()
    {
        return $this->hasOne(ReturnRecord::class, ['id' => 'return_id']);
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

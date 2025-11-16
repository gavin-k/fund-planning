<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%investment_product}}".
 *
 * @property int $id
 * @property string $name 产品名称
 * @property string $type 产品类型
 * @property string|null $platform 平台名称
 * @property float $current_amount 当前投资总额
 * @property string|null $description 描述
 * @property int $status 状态:10=使用中,0=已停用
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Investment[] $investments
 * @property ReturnRecord[] $returnRecords
 */
class InvestmentProduct extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 10;

    const TYPE_ALIPAY = 'alipay';
    const TYPE_BANK = 'bank';
    const TYPE_STOCK = 'stock';
    const TYPE_FUND = 'fund';
    const TYPE_P2P = 'p2p';
    const TYPE_OTHER = 'other';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%investment_product}}';
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
            [['name', 'type'], 'required'],
            [['current_amount'], 'number'],
            [['current_amount'], 'number', 'min' => 0],
            [['description'], 'string'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['name', 'platform'], 'string', 'max' => 100],
            [['type'], 'string', 'max' => 50],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_INACTIVE, self::STATUS_ACTIVE]],
            [['type'], 'in', 'range' => array_keys(self::getTypeList())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '产品名称',
            'type' => '产品类型',
            'platform' => '平台名称',
            'current_amount' => '当前投资总额(¥)',
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
        return $this->hasMany(Investment::class, ['product_id' => 'id']);
    }

    /**
     * 获取生效中的投资
     */
    public function getActiveInvestments()
    {
        return $this->hasMany(Investment::class, ['product_id' => 'id'])
            ->where(['status' => Investment::STATUS_ACTIVE]);
    }

    /**
     * 获取收益记录
     */
    public function getReturnRecords()
    {
        return $this->hasMany(ReturnRecord::class, ['product_id' => 'id']);
    }

    /**
     * 获取状态列表
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_INACTIVE => '已停用',
            self::STATUS_ACTIVE => '使用中',
        ];
    }

    /**
     * 获取类型列表
     */
    public static function getTypeList()
    {
        return [
            self::TYPE_ALIPAY => '支付宝',
            self::TYPE_BANK => '银行理财',
            self::TYPE_STOCK => '股票',
            self::TYPE_FUND => '基金',
            self::TYPE_P2P => 'P2P',
            self::TYPE_OTHER => '其他',
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
     * 获取类型文本
     */
    public function getTypeText()
    {
        $list = self::getTypeList();
        return isset($list[$this->type]) ? $list[$this->type] : '未知';
    }

    /**
     * 计算投资总额（从投资记录计算）
     */
    public function calculateCurrentAmount()
    {
        return (float)$this->getActiveInvestments()->sum('amount');
    }

    /**
     * 更新当前投资总额
     */
    public function updateCurrentAmount()
    {
        $this->current_amount = $this->calculateCurrentAmount();
        return $this->save(false, ['current_amount', 'updated_at']);
    }
}

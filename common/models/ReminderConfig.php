<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%reminder_config}}".
 *
 * @property int $id
 * @property string $type 提醒类型
 * @property int $enabled 是否启用
 * @property string|null $frequency 频率
 * @property string $notification_method 通知方式
 * @property string|null $config_data 配置数据（JSON格式）
 * @property int|null $last_triggered_at 最后触发时间
 * @property int $created_at
 * @property int $updated_at
 */
class ReminderConfig extends ActiveRecord
{
    const TYPE_PRODUCT_MATURITY = 'product_maturity';  // 产品到期提醒
    const TYPE_INCOME_RECORD = 'income_record';        // 收益录入提醒
    const TYPE_GOAL_DELAY = 'goal_delay';              // 目标延期提醒
    const TYPE_MONTHLY_REPORT = 'monthly_report';      // 月度报表提醒
    const TYPE_BUDGET_ALERT = 'budget_alert';          // 预算预警
    const TYPE_BALANCE_LOW = 'balance_low';            // 余额不足提醒

    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_ONCE = 'once';

    const METHOD_EMAIL = 'email';
    const METHOD_PUSH = 'push';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%reminder_config}}';
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
            [['type'], 'required'],
            [['enabled', 'last_triggered_at', 'created_at', 'updated_at'], 'integer'],
            [['config_data'], 'string'],
            [['type'], 'string', 'max' => 50],
            [['frequency', 'notification_method'], 'string', 'max' => 20],
            [['enabled'], 'default', 'value' => 1],
            [['notification_method'], 'default', 'value' => self::METHOD_EMAIL],
            [['type'], 'in', 'range' => [
                self::TYPE_PRODUCT_MATURITY,
                self::TYPE_INCOME_RECORD,
                self::TYPE_GOAL_DELAY,
                self::TYPE_MONTHLY_REPORT,
                self::TYPE_BUDGET_ALERT,
                self::TYPE_BALANCE_LOW,
            ]],
            [['frequency'], 'in', 'range' => [
                self::FREQUENCY_DAILY,
                self::FREQUENCY_WEEKLY,
                self::FREQUENCY_MONTHLY,
                self::FREQUENCY_ONCE,
            ]],
            [['notification_method'], 'in', 'range' => [self::METHOD_EMAIL, self::METHOD_PUSH]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '提醒类型',
            'enabled' => '启用状态',
            'frequency' => '提醒频率',
            'notification_method' => '通知方式',
            'config_data' => '配置数据',
            'last_triggered_at' => '最后触发时间',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 获取提醒类型文本
     * @return string
     */
    public function getTypeText()
    {
        $typeList = self::getTypeList();
        return $typeList[$this->type] ?? '未知';
    }

    /**
     * 获取提醒类型列表
     * @return array
     */
    public static function getTypeList()
    {
        return [
            self::TYPE_PRODUCT_MATURITY => '产品到期提醒',
            self::TYPE_INCOME_RECORD => '收益录入提醒',
            self::TYPE_GOAL_DELAY => '目标延期提醒',
            self::TYPE_MONTHLY_REPORT => '月度报表提醒',
            self::TYPE_BUDGET_ALERT => '预算预警',
            self::TYPE_BALANCE_LOW => '余额不足提醒',
        ];
    }

    /**
     * 获取频率文本
     * @return string
     */
    public function getFrequencyText()
    {
        $frequencyList = self::getFrequencyList();
        return $frequencyList[$this->frequency] ?? '未设置';
    }

    /**
     * 获取频率列表
     * @return array
     */
    public static function getFrequencyList()
    {
        return [
            self::FREQUENCY_DAILY => '每天',
            self::FREQUENCY_WEEKLY => '每周',
            self::FREQUENCY_MONTHLY => '每月',
            self::FREQUENCY_ONCE => '一次性',
        ];
    }

    /**
     * 获取通知方式文本
     * @return string
     */
    public function getNotificationMethodText()
    {
        $methodList = self::getNotificationMethodList();
        return $methodList[$this->notification_method] ?? '未知';
    }

    /**
     * 获取通知方式列表
     * @return array
     */
    public static function getNotificationMethodList()
    {
        return [
            self::METHOD_EMAIL => '邮件',
            self::METHOD_PUSH => '推送',
        ];
    }

    /**
     * 获取配置数据（解析JSON）
     * @return array
     */
    public function getConfigArray()
    {
        if (empty($this->config_data)) {
            return [];
        }

        try {
            return Json::decode($this->config_data);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 设置配置数据（转为JSON）
     * @param array $data
     */
    public function setConfigArray($data)
    {
        $this->config_data = Json::encode($data);
    }

    /**
     * 检查是否需要触发（根据频率和最后触发时间）
     * @return bool
     */
    public function shouldTrigger()
    {
        if (!$this->enabled) {
            return false;
        }

        if (!$this->last_triggered_at) {
            return true;
        }

        $lastTriggered = $this->last_triggered_at;
        $now = time();
        $daysDiff = floor(($now - $lastTriggered) / 86400);

        switch ($this->frequency) {
            case self::FREQUENCY_DAILY:
                return $daysDiff >= 1;
            case self::FREQUENCY_WEEKLY:
                return $daysDiff >= 7;
            case self::FREQUENCY_MONTHLY:
                return $daysDiff >= 30;
            case self::FREQUENCY_ONCE:
                return false; // 一次性提醒只触发一次
            default:
                return false;
        }
    }

    /**
     * 标记为已触发
     * @return bool
     */
    public function markAsTriggered()
    {
        $this->last_triggered_at = time();
        return $this->save(false, ['last_triggered_at']);
    }

    /**
     * 启用提醒
     * @return bool
     */
    public function enable()
    {
        $this->enabled = 1;
        return $this->save(false, ['enabled']);
    }

    /**
     * 禁用提醒
     * @return bool
     */
    public function disable()
    {
        $this->enabled = 0;
        return $this->save(false, ['enabled']);
    }
}

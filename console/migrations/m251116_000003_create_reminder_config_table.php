<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%reminder_config}}`.
 * 提醒配置表 - 用于设定各类智能提醒
 */
class m251116_000003_create_reminder_config_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%reminder_config}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(50)->notNull()->comment('提醒类型:product_maturity=产品到期,income_record=收益录入,goal_delay=目标延期,monthly_report=月度报表'),
            'enabled' => $this->smallInteger()->notNull()->defaultValue(1)->comment('是否启用:1=启用,0=停用'),
            'frequency' => $this->string(20)->comment('频率:daily=每天,weekly=每周,monthly=每月,once=一次性'),
            'notification_method' => $this->string(20)->notNull()->defaultValue('email')->comment('通知方式:email=邮件,push=推送'),
            'config_data' => $this->text()->comment('配置数据（JSON格式）'),
            'last_triggered_at' => $this->integer()->comment('最后触发时间'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // 创建索引
        $this->createIndex('idx-reminder_config-type', '{{%reminder_config}}', 'type');
        $this->createIndex('idx-reminder_config-enabled', '{{%reminder_config}}', 'enabled');
        $this->createIndex('idx-reminder_config-frequency', '{{%reminder_config}}', 'frequency');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%reminder_config}}');
    }
}

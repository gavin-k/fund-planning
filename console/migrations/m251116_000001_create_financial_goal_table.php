<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%financial_goal}}`.
 * 财务目标表 - 用于设定和追踪理财目标
 */
class m251116_000001_create_financial_goal_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%financial_goal}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->comment('目标名称（如：买车、旅游）'),
            'target_amount' => $this->decimal(15, 2)->notNull()->comment('目标金额'),
            'current_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('当前金额'),
            'target_date' => $this->date()->notNull()->comment('目标日期'),
            'fund_id' => $this->integer()->comment('关联基金ID'),
            'description' => $this->text()->comment('目标描述'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('状态:10=进行中,20=已完成,0=已取消'),
            'completed_at' => $this->integer()->comment('完成时间'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // 创建索引
        $this->createIndex('idx-financial_goal-status', '{{%financial_goal}}', 'status');
        $this->createIndex('idx-financial_goal-target_date', '{{%financial_goal}}', 'target_date');
        $this->createIndex('idx-financial_goal-fund_id', '{{%financial_goal}}', 'fund_id');

        // 添加外键
        $this->addForeignKey(
            'fk-financial_goal-fund_id',
            '{{%financial_goal}}',
            'fund_id',
            '{{%fund}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // 删除外键
        $this->dropForeignKey('fk-financial_goal-fund_id', '{{%financial_goal}}');

        // 删除表
        $this->dropTable('{{%financial_goal}}');
    }
}

<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%budget}}`.
 * 预算表 - 用于设定和监控各基金的预算
 */
class m251116_000002_create_budget_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%budget}}', [
            'id' => $this->primaryKey(),
            'fund_id' => $this->integer()->comment('关联基金ID（NULL表示总预算）'),
            'period_type' => $this->string(20)->notNull()->comment('周期类型:month=月度,quarter=季度,year=年度'),
            'budget_amount' => $this->decimal(15, 2)->notNull()->comment('预算金额'),
            'actual_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('实际金额'),
            'start_date' => $this->date()->notNull()->comment('开始日期'),
            'end_date' => $this->date()->notNull()->comment('结束日期'),
            'description' => $this->text()->comment('预算说明'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('状态:10=生效中,20=已结束,0=已停用'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // 创建索引
        $this->createIndex('idx-budget-fund_id', '{{%budget}}', 'fund_id');
        $this->createIndex('idx-budget-period_type', '{{%budget}}', 'period_type');
        $this->createIndex('idx-budget-start_date', '{{%budget}}', 'start_date');
        $this->createIndex('idx-budget-status', '{{%budget}}', 'status');

        // 添加外键
        $this->addForeignKey(
            'fk-budget-fund_id',
            '{{%budget}}',
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
        $this->dropForeignKey('fk-budget-fund_id', '{{%budget}}');

        // 删除表
        $this->dropTable('{{%budget}}');
    }
}

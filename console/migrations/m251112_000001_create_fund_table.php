<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%fund}}`.
 */
class m251112_000001_create_fund_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%fund}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->comment('基金名称'),
            'allocation_percent' => $this->decimal(5, 2)->notNull()->defaultValue(0)->comment('分配比例'),
            'current_balance' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('当前余额'),
            'description' => $this->text()->comment('描述'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('状态:10=启用,0=禁用'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-fund-status', '{{%fund}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%fund}}');
    }
}

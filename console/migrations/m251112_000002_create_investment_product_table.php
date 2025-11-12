<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%investment_product}}`.
 */
class m251112_000002_create_investment_product_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%investment_product}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->comment('产品名称'),
            'type' => $this->string(50)->notNull()->comment('产品类型'),
            'platform' => $this->string(100)->comment('平台名称'),
            'current_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('当前投资总额'),
            'description' => $this->text()->comment('描述'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('状态:10=使用中,0=已停用'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-investment_product-type', '{{%investment_product}}', 'type');
        $this->createIndex('idx-investment_product-status', '{{%investment_product}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%investment_product}}');
    }
}

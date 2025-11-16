<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%income}}`.
 */
class m251112_000004_create_income_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%income}}', [
            'id' => $this->primaryKey(),
            'amount' => $this->decimal(15, 2)->notNull()->comment('收入金额'),
            'source' => $this->string(200)->comment('收入来源'),
            'income_date' => $this->date()->notNull()->comment('收入日期'),
            'is_distributed' => $this->smallInteger()->notNull()->defaultValue(0)->comment('是否已分配:1=是,0=否'),
            'notes' => $this->text()->comment('备注'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-income-income_date', '{{%income}}', 'income_date');
        $this->createIndex('idx-income-is_distributed', '{{%income}}', 'is_distributed');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%income}}');
    }
}

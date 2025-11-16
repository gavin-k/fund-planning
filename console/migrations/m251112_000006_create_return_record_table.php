<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%return_record}}`.
 */
class m251112_000006_create_return_record_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%return_record}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull()->comment('产品ID'),
            'total_amount' => $this->decimal(15, 2)->notNull()->comment('总收益金额'),
            'return_date' => $this->date()->notNull()->comment('收益日期'),
            'is_distributed' => $this->smallInteger()->notNull()->defaultValue(0)->comment('是否已分配:1=是,0=否'),
            'notes' => $this->text()->comment('备注'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-return_record-product_id', '{{%return_record}}', 'product_id');
        $this->createIndex('idx-return_record-return_date', '{{%return_record}}', 'return_date');
        $this->createIndex('idx-return_record-is_distributed', '{{%return_record}}', 'is_distributed');

        $this->addForeignKey(
            'fk-return_record-product_id',
            '{{%return_record}}',
            'product_id',
            '{{%investment_product}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-return_record-product_id', '{{%return_record}}');
        $this->dropTable('{{%return_record}}');
    }
}

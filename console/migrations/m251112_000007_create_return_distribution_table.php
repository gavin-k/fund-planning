<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%return_distribution}}`.
 */
class m251112_000007_create_return_distribution_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%return_distribution}}', [
            'id' => $this->primaryKey(),
            'return_id' => $this->integer()->notNull()->comment('收益ID'),
            'fund_id' => $this->integer()->notNull()->comment('基金ID'),
            'amount' => $this->decimal(15, 2)->notNull()->comment('分配金额'),
            'percent' => $this->decimal(5, 2)->notNull()->comment('分配比例'),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-return_distribution-return_id', '{{%return_distribution}}', 'return_id');
        $this->createIndex('idx-return_distribution-fund_id', '{{%return_distribution}}', 'fund_id');

        $this->addForeignKey(
            'fk-return_distribution-return_id',
            '{{%return_distribution}}',
            'return_id',
            '{{%return_record}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-return_distribution-fund_id',
            '{{%return_distribution}}',
            'fund_id',
            '{{%fund}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-return_distribution-fund_id', '{{%return_distribution}}');
        $this->dropForeignKey('fk-return_distribution-return_id', '{{%return_distribution}}');
        $this->dropTable('{{%return_distribution}}');
    }
}

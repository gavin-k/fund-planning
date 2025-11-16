<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%income_distribution}}`.
 */
class m251112_000005_create_income_distribution_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%income_distribution}}', [
            'id' => $this->primaryKey(),
            'income_id' => $this->integer()->notNull()->comment('收入ID'),
            'fund_id' => $this->integer()->notNull()->comment('基金ID'),
            'amount' => $this->decimal(15, 2)->notNull()->comment('分配金额'),
            'percent' => $this->decimal(5, 2)->notNull()->comment('分配比例'),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-income_distribution-income_id', '{{%income_distribution}}', 'income_id');
        $this->createIndex('idx-income_distribution-fund_id', '{{%income_distribution}}', 'fund_id');

        $this->addForeignKey(
            'fk-income_distribution-income_id',
            '{{%income_distribution}}',
            'income_id',
            '{{%income}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-income_distribution-fund_id',
            '{{%income_distribution}}',
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
        $this->dropForeignKey('fk-income_distribution-fund_id', '{{%income_distribution}}');
        $this->dropForeignKey('fk-income_distribution-income_id', '{{%income_distribution}}');
        $this->dropTable('{{%income_distribution}}');
    }
}

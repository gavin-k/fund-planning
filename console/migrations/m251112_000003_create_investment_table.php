<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%investment}}`.
 */
class m251112_000003_create_investment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%investment}}', [
            'id' => $this->primaryKey(),
            'fund_id' => $this->integer()->notNull()->comment('基金ID'),
            'product_id' => $this->integer()->notNull()->comment('产品ID'),
            'amount' => $this->decimal(15, 2)->notNull()->comment('投资金额'),
            'investment_date' => $this->date()->notNull()->comment('投资日期'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('状态:10=生效中,0=已赎回'),
            'notes' => $this->text()->comment('备注'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-investment-fund_id', '{{%investment}}', 'fund_id');
        $this->createIndex('idx-investment-product_id', '{{%investment}}', 'product_id');
        $this->createIndex('idx-investment-status', '{{%investment}}', 'status');

        $this->addForeignKey(
            'fk-investment-fund_id',
            '{{%investment}}',
            'fund_id',
            '{{%fund}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-investment-product_id',
            '{{%investment}}',
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
        $this->dropForeignKey('fk-investment-product_id', '{{%investment}}');
        $this->dropForeignKey('fk-investment-fund_id', '{{%investment}}');
        $this->dropTable('{{%investment}}');
    }
}

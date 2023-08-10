<?php
use yii\db\Migration;

/**
 * Handles the creation of table {{%rent}}.
 */
class m230809_171226_create_rent_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('rent', [
            'id' => $this->primaryKey(),
            'book_id' => $this->integer()->notNull(), // Change type to integer
            'reader_id' => $this->integer()->notNull(), // Change type to integer
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-rent-book_id', 'rent', 'book_id');
        $this->createIndex('idx-rent-reader_id', 'rent', 'reader_id');

        $this->addForeignKey('fk_rent_readers', 'rent', 'reader_id', 'readers', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_rent_readers', 'rent');

        $this->dropIndex('idx-rent-book_id', 'rent');
        $this->dropIndex('idx-rent-reader_id', 'rent');
        $this->dropTable('rent');
    }
}
<?php
use yii\db\Migration;

/**
 * Handles the creation of table {{%rent}}.
 */
class m230809_171230_create_index_rent_books extends Migration
{
    public function safeUp()
    {
        $this->addForeignKey('fk_rent_books', 'rent', 'book_id', 'books', 'id', 'CASCADE', 'CASCADE');

    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_rent_books', 'rent');
    }
}
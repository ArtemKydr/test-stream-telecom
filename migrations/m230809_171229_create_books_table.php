<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%books}}`.
 */
class m230809_171229_create_books_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('books', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'status_id' => $this->integer()->notNull(),
            'rent_id' => $this->integer(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Создаем индексы для полей author_id и status_id
        $this->createIndex('idx-books-author_id', 'books', 'author_id');
        $this->createIndex('idx-books-status_id', 'books', 'status_id');
        $this->createIndex('idx-books-rent_id', 'books', 'status_id');

        $this->addForeignKey('fk_books_authors', 'books', 'author_id', 'authors', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_books_statuses', 'books', 'status_id', 'statuses', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_books_rent', 'books', 'rent_id', 'rent', 'id', 'CASCADE', 'CASCADE');

    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_books_rent', 'books');
        $this->dropForeignKey('fk_books_statuses', 'books');
        $this->dropForeignKey('fk_books_authors', 'books');

        // Удаляем индексы
        $this->dropIndex('idx-books-status_id', 'books');
        $this->dropIndex('idx-books-rent_id', 'books');
        $this->dropIndex('idx-books-author_id', 'books');

        $this->dropTable('books');
    }
}

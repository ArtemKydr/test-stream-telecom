<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%write_offs}}`.
 */
class m230809_171237_create_write_offs_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('write_offs', [
            'id' => $this->primaryKey(),
            'book_id' => $this->integer()->notNull(),
            'reason_id' => $this->integer(),
            'write_off_date' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Создаем индекс для поля book_id
        $this->createIndex('idx-write_offs-book_id', 'write_offs', 'book_id');

        // Создаем внешний ключ для book_id
        $this->addForeignKey('fk_write_offs_books', 'write_offs', 'book_id', 'books', 'id', 'CASCADE', 'CASCADE');

        // Создаем внешний ключ для reason_id, если используется отдельная таблица write_off_reasons
        $this->addForeignKey('fk_write_offs_reasons', 'write_offs', 'reason_id', 'write_off_reasons', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        // Удаляем внешний ключ для reason_id
        $this->dropForeignKey('fk_write_offs_reasons', 'write_offs');

        // Удаляем внешний ключ для book_id
        $this->dropForeignKey('fk_write_offs_books', 'write_offs');

        // Удаляем индекс
        $this->dropIndex('idx-write_offs-book_id', 'write_offs');

        // Удаляем таблицу write_offs
        $this->dropTable('write_offs');
    }

}

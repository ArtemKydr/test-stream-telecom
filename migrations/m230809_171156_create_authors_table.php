<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%authors}}`.
 */
class m230809_171156_create_authors_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('authors', [
            'id' => $this->primaryKey(),
            'first_name' => $this->string()->notNull(),
            'last_name' => $this->string()->notNull(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Создаем индекс для поля name
        $this->createIndex('idx-authors-first_name', 'authors', 'first_name', true);
        $this->createIndex('idx-authors-last_name', 'authors', 'last_name', true);
    }

    public function safeDown()
    {
        // Удаляем индекс
        $this->dropIndex('idx-authors-first_name', 'first_name');
        $this->dropIndex('idx-authors-last_name', 'last_name');

        // Удаляем таблицу authors
        $this->dropTable('authors');
    }

}

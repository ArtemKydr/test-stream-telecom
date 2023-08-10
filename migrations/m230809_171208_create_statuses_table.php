<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%statuses}}`.
 */
class m230809_171208_create_statuses_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('statuses', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Добавляем стандартные статусы (available, rented, written_off)
        $this->batchInsert('statuses', ['name'], [
            ['available'],
            ['rented'],
            ['written_off'],
        ]);

        // Создаем индекс для поля name
        $this->createIndex('idx-statuses-name', 'statuses', 'name', true);
    }

    public function safeDown()
    {
        // Удаляем индекс
        $this->dropIndex('idx-statuses-name', 'statuses');

        // Удаляем таблицу statuses
        $this->dropTable('statuses');
    }

}

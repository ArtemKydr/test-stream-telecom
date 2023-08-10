<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%write_off_reasons}}`.
 */
class m230809_171221_create_write_off_reasons_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('write_off_reasons', [
            'id' => $this->primaryKey(),
            'reason' => $this->string()->notNull(),
        ]);

        // Создаем индекс для поля reason
        $this->createIndex('idx-write_off_reasons-reason', 'write_off_reasons', 'reason', true);
    }

    public function safeDown()
    {
        // Удаляем индекс
        $this->dropIndex('idx-write_off_reasons-reason', 'write_off_reasons');

        // Удаляем таблицу write_off_reasons
        $this->dropTable('write_off_reasons');
    }

}

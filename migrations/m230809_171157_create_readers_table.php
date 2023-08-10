<?php
use yii\db\Migration;

/**
 * Handles the creation of table {{%readers}}.
 */
class m230809_171157_create_readers_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('readers', [
            'id' => $this->primaryKey(),
            'first_name' => $this->string()->notNull(),
            'last_name' => $this->string()->notNull(),
            'email' => $this->string()->notNull(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-readers-email', 'readers', 'email', true);
    }

    public function safeDown()
    {
        $this->dropIndex('idx-readers-email', 'readers');
        $this->dropTable('readers');
    }
}
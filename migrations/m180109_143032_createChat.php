<?php

use yii\db\Migration;

/**
 * Class m180109_143032_createChat
 */
class m180109_143032_createChat extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('chat', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer(11)->notNull(),
            'user_id' => $this->integer(11)->notNull(),
            'user_tmp_id' => $this->string(88)->notNull(),
            'course_id' => $this->integer(11)->notNull(),
            'text' => $this->string(500)->notNull(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('chat');
    }
}

<?php

use yii\db\Migration;

/**
 * Class m180428_114831_createServer
 */
class m180428_114831_createServer extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('server', [
            'id' => $this->primaryKey(),
            'course' => $this->string(250)->null(),

            'opaqueIdCamera' => $this->string(250)->null(),
            'currentRoomCamera' => $this->string(120)->null(),

            'opaqueIdScreen' => $this->string(250)->null(),
            'currentRoomScreen' => $this->string(120)->null(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('server');
    }

}

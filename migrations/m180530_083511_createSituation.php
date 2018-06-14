<?php

use yii\db\Migration;

/**
 * Class m180530_083511_createSituation
 */
class m180530_083511_createSituation extends Migration
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

        $this->createTable('situation', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->notNull(),
            'last_time' =>  $this->integer(11)->null(),
        ], $tableOptions);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('situation');
    }

}
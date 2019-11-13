<?php

use yii\db\Migration;

/**
 * Class m190219_110109_createRecords
 */
class m190219_110109_createRecords extends Migration
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

        $this->createTable('records', [
            'id' => $this->primaryKey(),
            'filename' => $this->string(250)->notNull(),
            'new_name' => $this->string(250)->notNull(),
            'duration' => $this->string(88)->notNull(),
            'total_minutes' => $this->integer(11)->notNull(),
            'hours' => $this->integer(11)->notNull(),
            'minutes' => $this->integer(11)->notNull(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('records');
    }

}

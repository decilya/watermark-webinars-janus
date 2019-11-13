<?php

use yii\db\Migration;

/**
 * Class m190520_133509_fixRecords
 */
class m190520_133509_fixRecords extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('records', 'alt_new_name', $this->string(250)->null());
        $this->addColumn('user', 'record_time_min', $this->integer(11)->defaultValue(10)->notNull());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('records', 'alt_new_name');
        $this->dropColumn('user', 'record_time_min');
    }
}

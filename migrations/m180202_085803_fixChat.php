<?php

use yii\db\Migration;

/**
 * Class m180202_085803_fixMessage
 */
class m180202_085803_fixChat extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('chat', 'room_id', $this->string(88)->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('chat', 'room_id');
    }

}

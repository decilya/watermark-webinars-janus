<?php

use yii\db\Migration;

/**
 * Class m191018_092858_addTorRecordIDForBuild
 */
class m191018_092858_addToRecordIDForBuild extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('records', 'build_id', $this->integer(11)->null());
        $this->addColumn('records', 'room_id', $this->integer(11)->null());
        $this->addColumn('records', 'user_id', $this->integer(11)->defaultValue(1)->notNull());

        $this->addColumn('records', 'created_at', $this->integer(11)->null());
        $this->addColumn('records', 'finished_at', $this->integer(11)->null());

        $this->execute("UPDATE records SET build_id = 1 WHERE id > 0");
        $this->execute("UPDATE records SET room_id = 1 WHERE id > 0");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('records', 'build_id');
        $this->dropColumn('records', 'room_id');
        $this->dropColumn('records', 'user_id');
        $this->dropColumn('records', 'created_at');
        $this->dropColumn('records', 'finished_at');
    }
}

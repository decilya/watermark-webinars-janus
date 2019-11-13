<?php

use yii\db\Migration;

/**
 * Class m191004_114808_fixRecord
 */
class m191004_114808_fixRecord extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('records', 'alt_new_name');
        $this->addColumn('records', 'status_build', $this->integer(1)->defaultValue(0)->notNull());

        $this->execute('UPDATE `records` SET `status_build`=1 WHERE id > 0');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('records', 'alt_new_name', $this->string(250)->null());
        $this->dropColumn('records', 'status_build');
    }
}

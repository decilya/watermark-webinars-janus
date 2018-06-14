<?php

use yii\db\Migration;

/**
 * Class m180522_085153_fixChat
 */
class m180522_085153_fixChat extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('chat', 'user_tmp_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('chat', 'user_tmp_id', $this->string(88)->notNull());
    }
}

<?php

use yii\db\Migration;

/**
 * Class m180209_123010_fixUser
 */
class m180209_123010_fixUser extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('user', 'isLogin', $this->integer(11)->defaultValue(0)->notNull());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'isLogin');
    }
}

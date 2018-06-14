<?php

use yii\db\Migration;

/**
 * Class m180524_104020_fixServer
 */
class m180524_104020_fixServer extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('server', 'on', $this->integer(11)->defaultValue(0)->notNull());
        $this->addColumn('server', 'restart', $this->integer(1)->defaultValue(0)->notNull());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('server', 'on');
        $this->dropColumn('server', 'restart');
    }

}

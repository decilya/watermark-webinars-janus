<?php

use yii\db\Migration;

/**
 * Class m181213_124234_fixSever
 */
class m181213_124234_fixSever extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('server', 'now', $this->integer(11)->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('server', 'now');
    }
}

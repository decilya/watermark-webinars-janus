<?php

use yii\db\Migration;

/**
 * Class m181214_103941_fixSeverScreen
 */
class m181214_103941_fixSeverScreen extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('server', 'screen', $this->integer(11)->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('server', 'screen');
    }
}

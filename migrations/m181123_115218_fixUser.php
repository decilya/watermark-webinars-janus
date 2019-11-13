<?php

use yii\db\Migration;

/**
 * Class m181123_115218_fixUser
 */
class m181123_115218_fixUser extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('user', 'hash', $this->string(250)->notNull());

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('request_video_user', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->notNull(),
            'filename' => $this->string(50)->notNull(),
            'status_id' => $this->integer(1)->defaultValue(0)->notNull(),
        ], $tableOptions);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'hash');
        $this->dropTable('request_video_user');
    }

}

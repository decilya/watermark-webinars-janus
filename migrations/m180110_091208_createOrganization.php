<?php

use yii\db\Migration;

/**
 * Class m180110_091208_createOrganization
 */
class m180110_091208_createOrganization extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('organization', [
            'name' => $this->string(250)->notNull(),
            'email' => $this->string(250)->notNull(),
            'phone' => $this->string(250)->notNull(),
            'url' => $this->string(250)->notNull(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('organization');
    }
}

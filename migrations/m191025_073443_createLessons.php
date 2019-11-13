<?php

use yii\db\Migration;

/**
 * Class m191025_073443_createLessons
 */
class m191025_073443_createLessons extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('lessons', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->notNull(),
            'record_name' => $this->string(125)->notNull(),
            'room_id' => $this->integer(11)->notNull(),
            'created_at' => $this->integer(11)->notNull(),
            'finished_at' => $this->integer(11)->notNull()
        ], $tableOptions);

        $this->addColumn('records', 'lesson_id', $this->integer(11)->null());

        $this->createIndex(
            'idx-records-lesson_id',
            'records',
            'lesson_id'
        );

        // дописать тут addForeignKey
        $this->addForeignKey(
            'fk-records-lesson_id',
            'records',
            'lesson_id',
            'lessons',
            'id',
            'NO ACTION'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-records-lesson_id',
            'records'
        );

        $this->dropIndex(
            'idx-records-lesson_id',
            'records'
        );

        $this->dropTable('lessons');
        $this->dropColumn('records', 'lesson_id');
    }
}

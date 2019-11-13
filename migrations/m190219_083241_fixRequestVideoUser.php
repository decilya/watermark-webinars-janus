<?php

use yii\db\Migration;

/**
 * Class m190219_083241_fixRequestVideoUser
 */
class m190219_083241_fixRequestVideoUser extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('request_video_user', 'new_name', $this->string(250)->null());
        $this->addColumn('request_video_user', 'hours', $this->integer(2)->null());
        $this->addColumn('request_video_user', 'minutes', $this->integer(2)->null());
        $this->addColumn('request_video_user', 'duration', $this->string(250)->null());
        $this->addColumn('request_video_user', 'total_minutes', $this->integer(11)->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('request_video_user', 'new_name');
        $this->dropColumn('request_video_user', 'hours');
        $this->dropColumn('request_video_user', 'minutes');
        $this->dropColumn('request_video_user', 'duration');
        $this->dropColumn('request_video_user', 'total_minutes');
    }

}

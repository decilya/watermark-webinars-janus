<?php

namespace app\modules\admin;

use app\models\User;
use Yii;
use yii\web\HttpException;

/**
 * admin module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\admin\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!Yii::$app->user->isGuest) {
            if ((Yii::$app->user->identity->type == User::TYPE_USER_MANAGER) ||
                (Yii::$app->user->identity->type == User::TYPE_USER_ADMIN)) {
                return true;
            } else {
                throw new HttpException(403, 'Отказано в доступе :(');
            }
        }

        return false;

    }
}
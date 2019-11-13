<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use app\models\User;
use yii;
use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [
        'js/ads.js',
        '/js/lib/fuckadblock.js',
        'js/component/material.min.js',

        'js/janus.js',
    ];
    public $jsOptions = ['position' => yii\web\View::POS_HEAD];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'app\assets\BootboxAsset',
    ];

    public function init()
    {
        parent::init();

        $this->js[] = 'js/globalJs.js?' . time();

        if (isset(Yii::$app->user->identity->type)) {

            if (Yii::$app->user->identity->type !== \app\models\User::TYPE_USER_STUDENT) {
                $this->js[] = '/js/teacherScreenObserver.js';
                $this->js[] = '/js/teacherScreenStarter.js';
            }

        }


    }
}

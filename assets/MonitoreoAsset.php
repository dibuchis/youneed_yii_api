<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MonitoreoAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'http://fonts.googleapis.com/css?family=Varela+Round',
        'css/global.css',
        'css/admin.css',
    ];
    public $js = [
        'https://maps.googleapis.com/maps/api/js?key=AIzaSyAvnDL8PwDHrOivIbSY3vL4HRPVomgp0LU&libraries=places',
        'js/gmaps.js',
        'js/split.min.js',
        'js/js.js',
        'js/websocket.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}

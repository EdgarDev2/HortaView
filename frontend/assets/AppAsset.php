<?php

namespace frontend\assets;

use Yii;
use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
    public function init()
    {
        parent::init();

        // Escanear el directorio y agregar archivos JS
        $jsDir = Yii::getAlias('@webroot/js/');
        if (is_dir($jsDir)) {
            $files = glob($jsDir . '/*.js'); // Obtiene todos los archivos .js
            foreach ($files as $file) {
                $this->js[] = 'js/' . basename($file);
            }
        }
    }
}

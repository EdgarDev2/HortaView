<?php
$this->title = 'Gráficas';

use yii\helpers\Html;

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom');
?>

<div class="graficas-index">
    <h1 class="display-6 text-success text-left"><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_graficas', [
        'resultados' => $resultados,
        'fechaActual' => $fechaActual,
    ]) ?>
</div>
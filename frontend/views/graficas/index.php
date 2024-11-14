<?php
$this->title = 'Gráficas';

use yii\helpers\Html;

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');
?>

<div class="graficas-index">
    <h1 class="display-6 text-success text-left"><?= Html::encode($this->title) ?></h1>
    <p class="text-success text-left"><?= $fechaActual ?>: Promedio de Humedad por Hora</p>
    <hr class="border border-success">

    <!-- Incluir el archivo _graficas.php con los datos correspondientes -->
    <?= $this->render('_graficas', [
        'resultados' => $resultados,  // Pasar los datos completos
        'fechaActual' => $fechaActual, // Pasar la fecha
    ]) ?>
</div>
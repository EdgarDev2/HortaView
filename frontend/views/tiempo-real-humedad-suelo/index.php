<?php
$this->title = 'Tiempo real Humedad del suelo';

use yii\helpers\Html;
?>
<div class="tiempo-real-humeda-suelo-index">
    <h1 class="display-5 text-dark text-center"><?= Html::encode($this->title) ?></h1>
    <?php if ($cicloSeleccionado): ?>
        <p>Ciclo seleccionado: <?= Html::encode($cicloSeleccionado) ?></p>
    <?php else: ?>
        <p>No hay un ciclo seleccionado.</p>
    <?php endif; ?>
    <?php if ($fechaInicio): ?>
        <p>Fecha de inicio del ciclo: <?= Yii::$app->formatter->asDate($fechaInicio, 'php:d/m/Y') ?></p>
        <p>Fecha fin del ciclo: <?= Yii::$app->formatter->asDate($fechaFin, 'php:d/m/Y') ?></p>
    <?php else: ?>
        <p>No se encontró la fecha de inicio para el ciclo seleccionado.</p>
    <?php endif; ?>
</div>
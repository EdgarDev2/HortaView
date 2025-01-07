<?php
$this->title = 'Predicción peso produccion final';

use yii\helpers\Html;
?>
<div class="prediccion-peso-produccion-final-index">
    <h1 class="display-5 text-dark text-center"><?= Html::encode($this->title) ?></h1>
    <?php foreach ($lineasPorCama as $cultivoId => $lineas): ?>
        <h3>Cultivo: <?= Html::encode($cultivoId) ?></h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Línea</th>
                    <th>Gramaje Real</th>
                    <th>Gramaje Predicho</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lineas as $linea): ?>
                    <?php
                    $prediccion = array_filter($prediccionesGramaje, function ($pred) use ($linea, $cultivoId) {
                        return $pred['linea'] == $linea['numeroLinea'] && $pred['cultivoId'] == $cultivoId;
                    });
                    $prediccion = reset($prediccion);
                    ?>
                    <tr>
                        <td><?= Html::encode($linea['numeroLinea']) ?></td>
                        <td><?= Html::encode($linea['gramaje']) ?></td>
                        <td><?= Html::encode($prediccion['prediccion'] ?? 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

</div>
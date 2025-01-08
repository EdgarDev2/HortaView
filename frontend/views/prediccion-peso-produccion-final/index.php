<?php
$this->title = 'Predicción peso produccion final por line (siguiente ciclo)';

use yii\helpers\Html;
use practically\chartjs\widgets\Chart as WidgetsChart;
?>
<div class="prediccion-peso-produccion-final-index">
    <h1 class="display-5 text-dark text-center mb-4"><?= Html::encode($this->title) ?></h1>
    <table class="table table-hover table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Cultivo</th>
                <th>Línea</th>
                <th>Promedio de Surcos Germinados</th>
                <th>Promedio de Altura de Brote (cm)</th>
                <th>Gramaje Actual (kg)</th>
                <th>Predicción de Gramaje (kg)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lineasCultivo as $index => $linea): ?>
                <tr>
                    <td><?= Html::encode($linea['nombreCultivo']) ?></td>
                    <td><?= Html::encode($linea['numeroLinea']) ?></td>
                    <td><?= number_format($predictions[$index][0], 2) ?></td>
                    <td><?= number_format($predictions[$index][0], 2) ?></td>
                    <td><?= number_format($linea['gramaje'], 2) ?></td>
                    <td><?= number_format($predictions[$index], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>


    <h3>Gráfico de Lineas Cultivo</h3>
    <?= WidgetsChart::widget([
        'type' => WidgetsChart::TYPE_LINE,
        'datasets' => [
            [
                'label' => 'Predicción de Gramaje (kg)', // Título del dataset
                'data' => array_column($lineasCultivo, 'gramaje'), // Predicción de gramaje de cada línea (índice 2)
                'fill' => false, // No llenar el área bajo la línea
                'tension' => 0.4, // Curvatura de la línea
            ],
        ],
        'clientOptions' => [
            'maintainAspectRatio' => true,
            'plugins' => [
                'title' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Líneas de la cama Ka\'anche\' 1, 2, 3, 4',
                    ],
                    'type' => 'category',
                    'labels' => array_column($lineasCultivo, 'numeroLinea'), // Usar los números de línea como etiquetas
                ],
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Porcentaje de Gramaje para el siguiente ciclo',
                    ],
                    'beginAtZero' => false,
                ],
            ],
        ],
    ]); ?>

</div>
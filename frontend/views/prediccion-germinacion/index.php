<?php

use yii\helpers\Html;
use practically\chartjs\widgets\Chart as WidgetsChart;

$this->title = 'Predicción porcentaje de germinación';

?>
<div class="prediccion-germinacion-index">
    <h1 class="display-5 text-secondary text-center"><?= Html::encode($this->title) ?></h1>

    <?php foreach ($cultivos as $cultivo): ?>
        <div class="cultivo mb-5">
            <h3 class="text-secondary mb-3"><?= Html::encode($cultivo['nombreCultivo']) ?></h3>

            <?php if (isset($prediccionesPorCultivo[$cultivo['cultivoId']])): ?>
                <!-- Gráfica de Predicciones -->
                <?= WidgetsChart::widget([
                    'type' => WidgetsChart::TYPE_LINE, // Tipo de gráfico
                    'datasets' => [
                        [
                            'data' => array_column($prediccionesPorCultivo[$cultivo['cultivoId']], 'prediccion'), // Datos de la predicción
                            'label' => 'Predicción % de Germinación por linea para el siguiente ciclo',
                            'fill' => false,
                            'tension' => 0.2,
                        ]
                    ],
                    'clientOptions' => [
                        //'responsive' => true,
                        'maintainAspectRatio' => true,
                        'plugins' => [
                            'title' => [
                                'display' => true,
                                //'text' => 'Predicción % de Germinación',
                            ],
                        ],
                        'scales' => [
                            'x' => [
                                'title' => [
                                    'display' => true,
                                    'text' => 'Líneas',
                                ],
                                'type' => 'category',
                                'labels' => range(1, count($prediccionesPorCultivo[$cultivo['cultivoId']])), // Etiquetas de X comenzando desde 1
                            ],
                            'y' => [
                                'title' => [
                                    'display' => true,
                                    'text' => 'Porcentaje de Germinación',
                                ],
                                'beginAtZero' => false,
                            ],
                        ],
                    ],
                ]); ?>
            <?php else: ?>
                <p>No hay predicciones para este cultivo.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</div>
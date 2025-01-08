<?php

use yii\helpers\Html;
use practically\chartjs\widgets\Chart as WidgetsChart;

$this->title = 'Predicción porcentaje de germinación por línea para el siguiente ciclo';

?>
<div class="prediccion-germinacion-index container py-5">
    <h1 class="display-5 text-secondary text-center mb-5"><?= Html::encode($this->title) ?></h1>

    <?php foreach ($cultivos as $cultivo): ?>
        <div class="cultivo mb-5 p-4 border rounded shadow-sm bg-white">
            <h3 class="text-secondary mb-3 text-center"><?= Html::encode($cultivo['nombreCultivo']) ?></h3>

            <?php if (isset($prediccionesPorCultivo[$cultivo['cultivoId']])): ?>
                <div class="chart-container mb-4">
                    <!-- Gráfica de Predicciones -->
                    <?= WidgetsChart::widget([
                        'type' => WidgetsChart::TYPE_LINE,
                        'datasets' => [
                            [
                                'data' => array_column($prediccionesPorCultivo[$cultivo['cultivoId']], 'prediccion'),
                                'label' => 'Predicción % de Germinación por línea (Siguiente ciclo)',
                                'fill' => false,
                                'tension' => 0.4,

                            ],
                            [
                                'data' => array_column($datosHistoricos[$cultivo['cultivoId']], 'alturas'),
                                'label' => 'Altura máxima de brotes (Histórico)',
                                'fill' => false,
                                'tension' => 0.4,

                            ],
                            [
                                'data' => array_column($datosHistoricos[$cultivo['cultivoId']], 'surcosGerminados'),
                                'label' => 'Promedio de surcos germinados (Histórico)',
                                'fill' => false,
                                'tension' => 0.4,

                            ],
                            [
                                'data' => array_column($datosHistoricos[$cultivo['cultivoId']], 'totalSurcos'),
                                'label' => 'Total de surcos germinados (Histórico)',
                                'fill' => false,
                                'tension' => 0.4,

                            ]
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
                                        'text' => 'Líneas de la cama Ka\'anche\'',
                                    ],
                                    'type' => 'category',
                                    'labels' => range(1, count($prediccionesPorCultivo[$cultivo['cultivoId']])),
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
                </div>
            <?php else: ?>
                <p class="text-danger text-center">No hay predicciones disponibles para este cultivo.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
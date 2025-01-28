<?php
$this->title = 'Eficiencia de sistemas de riego manual vs automático';

use yii\helpers\Html;
use practically\chartjs\widgets\Chart as WidgetsChart;

$descripcionCiclo = $datosGrafico['descripcionCiclo'];
?>
<div class="estadisticas-generales-index">
    <div class="container mt-4">
        <div class="text-center mt-4 mb-4">
            <button id="downloadPdf" class="btn btn-success">Descargar en PDF</button>
        </div>
        <!-- Resumen del Ciclo -->
        <div class="alert alert-info shadow-sm rounded p-4">
            <h1 class="display-5 text-dark text-center">Eficiencia de sistemas de riego manual vs automático</h1>
        </div>

        <!-- Tabla de métricas -->
        <div class="card shadow-sm rounded mt-4 mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Métricas del: <?= Html::encode($descripcionCiclo) ?> ( <?= Html::encode($fechaInicio) ?> - <?= Html::encode($fechaFin) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0 text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th>Cultivo</th>
                            <th>Promedio General de Volumen (L)</th>
                            <th>Mínimos (L)</th> <!--Promedio de Mínimos (L)-->
                            <th>Máximos (L)</th> <!--Promedio de Máximos (L)-->
                            <th>Total consumido (L)</th>
                            <th>Desviación Estándar (L)</th>
                            <th>Índice de eficiencia ponderado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cultivos as $cultivo): ?>
                            <tr>
                                <td><?= Html::encode($cultivo['nombreCultivo']) ?></td>
                                <td><?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['promedio'], 4) ?> l</td>
                                <td><?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['minimo'], 4) ?> l</td><!--promedioMinimos-->
                                <td><?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['maximo'], 4) ?> l</td><!--promedioMaximos-->
                                <td><?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['total'], 4) ?> l</td>
                                <td><?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['desviacion'], 4) ?> l</td>
                                <td><?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['indiceEficiencia'], 4) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php if (!empty($datosGrafico['labels']) && !empty($datosGrafico['promedios'])): ?>
        <!-- Gráfica de Promedios -->
        <?= WidgetsChart::widget([
            'type' => WidgetsChart::TYPE_LINE, // Tipo de gráfico (barra)
            'datasets' => [
                [
                    'data' => $datosGrafico['promedios'], // Datos de promedios
                    'label' => 'Promedio General de Volumen (L)', // Etiqueta del conjunto de datos

                ],
                [
                    'data' => $datosGrafico['minimos'], // Datos de promedios promedioMinimos
                    'label' => 'Mínimos (L)', // Etiqueta del conjunto de datos era Promedio de mínimos (L)

                ],
                [
                    'data' => $datosGrafico['maximos'], // Datos de promedios promedioMaximos
                    'label' => 'Máximos (L)', // Etiqueta del conjunto de datos era 'Promedio de máximos (L)

                ],
                [
                    'data' => $datosGrafico['desviaciones'], // Datos de promedios
                    'label' => 'Desviación estándar (L)', // Etiqueta del conjunto de datos

                ],
                [
                    'data' => $datosGrafico['totales'], // Datos de promedios
                    'label' => 'Total de agua consumida (L)', // Etiqueta del conjunto de datos

                ],
                [
                    'data' => $datosGrafico['indicesEficiencia'], // Datos de promedios
                    'label' => 'Indice de eficiencia ponderado', // Etiqueta del conjunto de datos

                ]
            ],
            'clientOptions' => [
                'maintainAspectRatio' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Consumo de Agua por Cultivo: ' . $descripcionCiclo,
                    ],
                ],
                'scales' => [
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Cultivos que pertenencen al ciclo: ' . $descripcionCiclo,
                        ],
                        'type' => 'category',
                        'labels' => $datosGrafico['labels'], // Etiquetas para el eje X
                    ],
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Volumen (L) de agua consumido por ciclo',
                        ],
                        'beginAtZero' => true,
                    ],
                ],
            ],
        ]); ?>
    <?php else: ?>
        <p>No hay datos disponibles para mostrar el gráfico.</p>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.16/jspdf.plugin.autotable.min.js"></script>

<script>
    document.getElementById('downloadPdf').addEventListener('click', function() {
        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF();

        // Título de la página
        doc.setFontSize(16);
        doc.text('Eficiencia de sistemas de riego manual vs automático', 10, 20);

        // Título de la tabla
        doc.setFontSize(12);
        doc.text('Métricas del: ' + '<?= Html::encode($descripcionCiclo) ?>', 10, 30);

        const tableHeaders = [
            'Cultivo',
            'Promedio General de Volumen (L)',
            'Mínimos (L)',
            'Máximos (L)',
            'Total consumido (L)',
            'Desviación Estándar (L)',
            'Índice de eficiencia ponderado'
        ];

        const tableRows = [];

        <?php foreach ($cultivos as $cultivo): ?>
            tableRows.push([
                "<?= Html::encode($cultivo['nombreCultivo']) ?>",
                "<?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['promedio'], 2) ?>", // Promedio General de Volumen (L)
                "<?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['minimo'], 2) ?>", // Mínimos (L)
                "<?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['maximo'], 2) ?>", // Máximos (L)
                "<?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['total'], 2) ?>", // Total consumido (L)
                "<?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['desviacion'], 2) ?>", // Desviación Estándar (L)
                "<?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['indiceEficiencia'], 2) ?>" // Índice de eficiencia ponderado
            ]);
        <?php endforeach; ?>

        // Insertar la tabla
        doc.autoTable({
            head: [tableHeaders],
            body: tableRows,
            startY: 40,
        });

        // Gráfica (ejemplo: exportación de la imagen de la gráfica generada)
        // Si existe un canvas, exportamos su imagen
        const canvas = document.querySelector("canvas");
        if (canvas) {
            const imgData = canvas.toDataURL('image/png');
            doc.addImage(imgData, 'PNG', 10, doc.lastAutoTable.finalY + 10, 180, 100);
        }

        // Guardar el archivo PDF
        doc.save('estadisticas_ciclo.pdf');
    });
</script>
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
            <h1 class="display-5 text-dark text-center">Eficiencia de sistemas de riego manual vs automático, <?= Html::encode($descripcionCiclo) ?></h1>
            <p><strong>Fecha de inicio del ciclo:</strong> <?= Html::encode($fechaInicio) ?></p>
            <p><strong>Fecha de fin del ciclo:</strong> <?= Html::encode($fechaFin) ?></p>
        </div>

        <!-- Tabla de métricas -->
        <div class="card shadow-sm rounded mt-4 mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Métricas del Ciclo</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Cultivo</th>
                            <th>Promedio General de Volumen (L)</th>
                            <th>Mínimos (L)</th> <!--Promedio de Mínimos (L)-->
                            <th>Máximos (L)</th> <!--Promedio de Máximos (L)-->
                            <th>Desviación Estándar (L)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cultivos as $cultivo): ?>
                            <tr>
                                <td><?= Html::encode($cultivo['nombreCultivo']) ?></td>
                                <td><?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['promedio'], 2) ?> l</td>
                                <td><?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['minimo'], 2) ?> l</td><!--promedioMinimos-->
                                <td><?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['maximo'], 2) ?> l</td><!--promedioMaximos-->
                                <td><?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['desviacion'], 2) ?> l</td>
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

                ]
            ],
            'clientOptions' => [
                'maintainAspectRatio' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Consumo de Agua por Cultivo',
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

        // Tabla de métricas
        doc.setFontSize(12);
        doc.text('Métricas del Ciclo', 10, 30);

        const tableHeaders = ['Cultivo', 'Promedio General de Volumen (L)', 'Promedio de Mínimos (L)', 'Promedio de Máximos (L)', 'Desviación Estándar (L)'];
        const tableRows = [];

        <?php foreach ($cultivos as $cultivo): ?>
            tableRows.push([
                "<?= Html::encode($cultivo['nombreCultivo']) ?>",
                "<?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['promedio'], 2) ?>",
                "<?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['minimo'], 2) ?>", //promedioMinimos
                "<?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['maximo'], 2) ?>", //promedioMaximos
                "<?= number_format($metricasPorCultivo[$cultivo['cultivoId']]['desviacion'], 2) ?>"
            ]);
        <?php endforeach; ?>

        // Insertar la tabla
        doc.autoTable({
            head: [tableHeaders],
            body: tableRows,
            startY: 40,
        });

        // Gráfica (ejemplo: puedes exportar la imagen de la gráfica generada)
        // Para exportar la imagen de la gráfica, usa la opción de exportación de Chart.js
        const canvas = document.querySelector("canvas");
        if (canvas) {
            const imgData = canvas.toDataURL('image/png');
            doc.addImage(imgData, 'PNG', 10, doc.lastAutoTable.finalY + 10, 180, 100);
        }

        // Guardar el archivo PDF
        doc.save('estadisticas_ciclo.pdf');
    });
</script>
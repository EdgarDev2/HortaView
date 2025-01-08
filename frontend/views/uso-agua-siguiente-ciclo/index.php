<?php
$this->title = 'Predicción uso agua general siguiente ciclo';

use yii\helpers\Html;
?>
<div class="uso-agua-siguiente-ciclo-index">
    <h1 class="display-5 text-dark text-center mb-4"><?= Html::encode($this->title) ?></h1>

    <!-- Contenedor del gráfico -->
    <canvas id="volumenChart" width="400" height="200"></canvas>

</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Datos pasados desde el controlador
    var dias = <?= json_encode($dias) ?>; // Los días históricos
    var volumenesValvula = <?= json_encode($prediccionVolumenValvula) ?>; // Volúmenes predichos por válvula
    var volumenesRiegoManual = <?= json_encode($prediccionVolumenRiegoManual) ?>; // Volúmenes predichos por riego manual
    var diasFuturos = <?= json_encode($dias) ?>; // Los días futuros para las predicciones

    // Configuración del gráfico
    var ctx = document.getElementById('volumenChart').getContext('2d');
    var volumenChart = new Chart(ctx, {
        type: 'line', // Tipo de gráfico (línea)
        data: {
            labels: dias, // Concatenamos los días históricos con los futuros
            datasets: [{
                    label: 'Predicción Volumen por Válvula (L)',
                    data: volumenesValvula, // Datos de volúmenes predichos por válvula
                    borderColor: '#FF9F40',
                    fill: false
                },
                {
                    label: 'Predicción Volumen por Riego Manual (L)',
                    data: volumenesRiegoManual, // Datos de volúmenes predichos por riego manual
                    borderColor: '#36A2EB',
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Volumen (litros)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Predicción consumo de agua por día para los proximos 33 días'
                    }
                }
            }
        }
    });
</script>
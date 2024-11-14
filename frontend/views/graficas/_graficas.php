<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
?>

<div class="container">
    <h2>Promedio de Humedad por Hora</h2>
    <h4>Fecha: <?= Html::encode($fechaActual) ?></h4>

    <div class="row">
        <!-- Gráfico de Cama 1 -->
        <div class="col-md-6">
            <h5>Cama 1</h5>
            <canvas id="graficoCama1"></canvas>
        </div>

        <!-- Gráfico de Cama 2 -->
        <div class="col-md-6">
            <h5>Cama 2</h5>
            <canvas id="graficoCama2"></canvas>
        </div>
    </div>
    <div class="row">
        <!-- Gráfico de Cama 3 -->
        <div class="col-md-6">
            <h5>Cama 3</h5>
            <canvas id="graficoCama3"></canvas>
        </div>

        <!-- Gráfico de Cama 4 -->
        <div class="col-md-6">
            <h5>Cama 4</h5>
            <canvas id="graficoCama4"></canvas>
        </div>
    </div>

    <?php
    $this->registerJs("
        // Datos de las gráficas
        const resultados = " . json_encode($resultados) . ";

        // Función para crear un gráfico radar
        function crearGraficoRadar(canvasId, data) {
            new Chart(document.getElementById(canvasId), {
                type: 'radar',
                data: {
                    labels: data.horas, // Horas del día (0-23)
                    datasets: [{
                        label: 'Promedio de Humedad',
                        data: data.humedades,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        r: {
                            min: 0,
                            max: 100,
                            beginAtZero: true,
                            ticks: {
                                stepSize: 10
                            }
                        }
                    },
                    responsive: true
                }
            });
        }

        // Preparar los datos para cada gráfico
        const horas = Array.from({ length: 24 }, (_, i) => i); // Horas del día (0-23)

        const dataCama1 = {
            horas: horas,
            humedades: horas.map(hora => resultados[hora]?.promedio_humedad_cama1 ?? 0)
        };
        const dataCama2 = {
            horas: horas,
            humedades: horas.map(hora => resultados[hora]?.promedio_humedad_cama2 ?? 0)
        };
        const dataCama3 = {
            horas: horas,
            humedades: horas.map(hora => resultados[hora]?.promedio_humedad_cama3 ?? 0)
        };
        const dataCama4 = {
            horas: horas,
            humedades: horas.map(hora => resultados[hora]?.promedio_humedad_cama4 ?? 0)
        };

        // Crear los gráficos de radar
        crearGraficoRadar('graficoCama1', dataCama1);
        crearGraficoRadar('graficoCama2', dataCama2);
        crearGraficoRadar('graficoCama3', dataCama3);
        crearGraficoRadar('graficoCama4', dataCama4);
    ");
    ?>
</div>
<?php
$this->title = 'Predicción de humedad de tierra';
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js');
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/simple-statistics/7.8.0/simple-statistics.min.js');
$this->registerCssFile('@web/css/chart_card.css');
// Clases comunes bootstrap
$btnClass = 'btn btn-outline-success btn-sm border-0 shadow-none';
$btnDownloadClass = 'btn btn-outline-primary btn-sm border-0 shadow-none';
$cardInputDate = 'input-group input-group-sm d-flex align-items-center gap-2 bg-light rounded px-2';
$inputDate = 'form-control placeholder-wave bg-transparent text-secondary';
$selectPlace = 'form-select placeholder-wave border-0 text-secondary bg-light rounded';

use yii\helpers\Html;
?>

<div class="filtrar-humedad-por-rango-index">
    <h1 class="display-6 text-secondary text-left mb-3"><?= Html::encode($this->title) ?></h1>
    <div class="row">
        <!-- Botones de gráfico y filtros -->
        <div class="col-md-12 d-flex flex-wrap align-items-center gap-2">
            <!-- Botones de tipo de gráfico -->
            <div class="btn-group" role="group" aria-label="Gráficos">
                <button class="<?= $btnDownloadClass ?>" type="button" title="Descargar gráfico como imagen" onclick="descargarImagen('graficoPredicciones', 'grafico_cama.png')">
                    <i class="fas fa-download"></i> Descargar img del gráfico
                </button>
            </div>
            <!-- Filtros de fecha -->
            <div class="<?= $cardInputDate ?>" style="max-width: 250px;">
                <label for="fechaInicio" class="form-label mb-0 text-secondary">Fecha Inicio:</label>
                <input type="date" id="fechaInicio" class="<?= $inputDate ?>" style="width: 140px; border: none;" min="<?= $fechaInicio ?>" max="<?= $fechaFin ?>">
            </div>
            <div class=" <?= $cardInputDate ?>" style="max-width: 250px;">
                <label for="fechaFin" class="form-label mb-0 text-secondary">Fecha Fin:</label>
                <input type="date" id="fechaFin" class="<?= $inputDate ?>" style="width: 140px; border: none;" min="<?= $fechaInicio ?>" max="<?= $fechaFin ?>">
            </div>
            <!-- Selector de cama -->
            <div class="input-group input-group-sm" style="max-width: 162px;">
                <select id="camaId" class="<?= $selectPlace ?>" title="Selecciona cama">
                    <option value="" disabled selected>Seleccionar cama</option>
                    <option value="1">Ka'anche' 1</option>
                    <option value="2">Ka'anche' 2</option>
                    <option value="3">Ka'anche' 3</option>
                    <option value="4">Ka'anche' 4</option>
                </select>
            </div>
            <!-- Botón Filtrar -->
            <div>
                <button id="btnFiltrar" class="btn btn-outline-primary btn-sm border-0 shadow-none">Filtrar datos</button>
            </div>
        </div>
        <div class="chartCard">
            <div class="chartBox">
                <canvas id="graficoPredicciones" class="mt-4"></canvas>
            </div>
        </div>
    </div>
    <!-- Pasamos los datos de la sesión a JS -->
    <div id="data-container"
        data-ciclo="<?= $cicloSeleccionado ?>"
        data-fecha-inicio="<?= $fechaInicio ?>"
        data-fecha-fin="<?= $fechaFin ?>">
    </div>

</div>

<!-- Para que trabajemos las peticiones AJAX -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
<script type="module">
    import {
        linearRegression,
        linearRegressionLine
    } from 'https://cdn.jsdelivr.net/npm/simple-statistics@7.8.3/index.js';
    let chartInstance = null; // Variable global para almacenar la instancia del gráfico

    // Obtener fecha actual en formato YYYY-MM-DD
    function obtenerFechaActual() {
        const hoy = new Date();
        const year = hoy.getFullYear();
        const month = String(hoy.getMonth() + 1).padStart(2, '0');
        const day = String(hoy.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const fechaActual = obtenerFechaActual();
        let dataContainer = document.getElementById('data-container');
        let fechaInicioo = dataContainer.dataset.fechaInicio; // Solo la fecha
        let fechaFinn = dataContainer.dataset.fechaFin; // Solo la fecha
        document.getElementById('fechaInicio').value = fechaInicioo || fechaActual;
        document.getElementById('fechaFin').value = fechaFinn || fechaActual;

        const camaIdPredeterminada = '1';
        document.getElementById('camaId').value = camaIdPredeterminada;

        $(document).ready(function() {
            setTimeout(clickbutton, 10);

            function clickbutton() {
                $("#btnFiltrar").click();
            }
        });
    });

    $(document).ready(function() {
        $('#btnFiltrar').on('click', function() {
            const fechaInicio = $('#fechaInicio').val();
            const fechaFin = $('#fechaFin').val();
            const camaId = $('#camaId').val();

            // Realizar solicitud AJAX
            $.ajax({
                url: 'index.php?r=predicciones/predecir',
                type: 'POST',
                data: {
                    camaId: camaId,
                    fechaInicio: fechaInicio,
                    fechaFin: fechaFin
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        console.log('Datos históricos:', response.datos_historicos);

                        const datosHistoricos = response.datos_historicos;
                        const prediccioness = response.predicciones;

                        // Procesar datos para regresión lineal
                        const fechas = datosHistoricos.map((_, index) => index);
                        const valores = datosHistoricos.map(d => parseFloat(d.promedio_humedad));

                        // Datos para el gráfico
                        const labels = datosHistoricos.map(d => d.fecha);
                        const datosOriginales = valores;
                        const datosPredichos = [...datosOriginales, ...prediccioness];

                        // Generar nuevas etiquetas dinámicas
                        const nuevasLabels = labels.slice();
                        for (let i = 1; i <= 35; i++) {
                            const fechaNueva = new Date(labels[labels.length - 1]);
                            fechaNueva.setDate(fechaNueva.getDate() + i);
                            nuevasLabels.push(fechaNueva.toISOString().split('T')[0]);
                        }

                        // Verificar si el canvas existe
                        const ctx = document.getElementById('graficoPredicciones')?.getContext('2d');
                        if (ctx) {
                            // Destruir la instancia del gráfico anterior si existe
                            if (chartInstance) {
                                chartInstance.destroy();
                            }

                            // Crear nuevo gráfico
                            chartInstance = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: nuevasLabels,
                                    datasets: [{
                                            label: 'Humedad Histórica',
                                            data: datosOriginales,
                                            borderColor: 'blue',
                                            fill: false,
                                        },
                                        {
                                            label: 'Humedad Predicha (35 días)',
                                            data: datosPredichos,
                                            borderColor: 'orange',
                                            borderDash: [5, 5],
                                            tension: 0.3,
                                            fill: false,
                                        },
                                    ],
                                },
                                options: {
                                    animations: {
                                        /*tension: {
                                            duration: 4000,
                                            easing: 'easeOutBounce', //easeOutBounce, easeInOut, easeInOutQuad,
                                            from: 1,
                                            to: 0,
                                            loop: true
                                        }*/
                                    },
                                    responsive: true,
                                    maintainAspectRatio: false, // Mantiene la proporción de aspecto
                                    scales: {
                                        x: {
                                            beginAtZero: true,
                                            ticks: {
                                                stepSize: 1,
                                            },
                                            title: {
                                                display: true,
                                                text: 'Fechas',
                                            },
                                        },
                                        y: {
                                            min: 0,
                                            max: 100,
                                            beginAtZero: true,
                                            ticks: {
                                                stepSize: 10,
                                            },
                                            title: {
                                                display: true,
                                                text: 'Humedad (%)',
                                            },
                                        },
                                    },
                                    plugins: {
                                        zoom: {
                                            pan: {
                                                enabled: true,
                                                mode: 'xy',
                                            },
                                            zoom: {
                                                wheel: {
                                                    enabled: true,
                                                },
                                            },

                                        },
                                    },
                                },
                            });
                        } else {
                            console.error('El canvas no se encuentra disponible.');
                        }
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al filtrar los datos:', error);
                    alert('Ocurrió un error al filtrar los datos. Por favor, intenta nuevamente.');
                }
            });
        });
    });
    // Función para descargar el gráfico
    window.descargarImagen = function(canvasId, nombreArchivo) {
        let link = document.createElement('a');
        link.href = document.getElementById(canvasId).toDataURL();
        link.download = nombreArchivo;
        link.click();
    };
</script>
<?php

use yii\helpers\Html;

$this->title = 'Predicción peso produccion final por line (siguiente ciclo)';
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js');

// Clases comunes bootstrap
$btnClass = 'btn btn-outline-success btn-sm border-0 shadow-none';
$btnDownloadClass = 'btn btn-outline-primary btn-sm border-0 shadow-none';
$cardInputDate = 'input-group input-group-sm d-flex align-items-center gap-2 bg-light rounded px-2';
$inputDate = 'form-control placeholder-wave bg-transparent text-secondary';
$selectPlace = 'form-select placeholder-wave border-0 text-secondary bg-light rounded';
?>
<div class="prediccion-peso-produccion-final-index">
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Peso de la producción final (siguiente ciclo).</h4>
        </div>

        <!-- Filtros y opciones de gráficos -->
        <div class="row mt-4">
            <div class="col-md-12 d-flex flex-wrap align-items-center gap-2">
                <!-- Botones para tipo de gráfico -->
                <div class="btn-group" role="group" aria-label="Opciones de gráficos">
                    <button class="<?= $btnClass ?>" type="button" title="Gráfico de tipo Lineal" onclick="cambiarTipoGrafico('line')">
                        <i class="fas fa-chart-line"></i> Lineal
                    </button>
                    <button class="<?= $btnClass ?>" type="button" title="Gráfico de tipo Barra" onclick="cambiarTipoGrafico('bar')">
                        <i class="fas fa-chart-bar"></i> Barra
                    </button>
                    <button class="<?= $btnClass ?>" type="button" title="Gráfico de tipo Radar" onclick="cambiarTipoGrafico('radar')">
                        <i class="fas fa-chart-pie"></i> Radar
                    </button>
                    <button class="<?= $btnDownloadClass ?>" type="button" title="Descargar gráfico como imagen" onclick="descargarImagen('graficoCama', 'grafico_cama.png')">
                        <i class="fas fa-download"></i> Descargar
                    </button>
                </div>
                <!-- Selector de cama -->
                <div class="input-group input-group-sm" style="max-width: 226px;">
                    <select id="camaId" class="<?= $selectPlace ?>" title="Selecciona cama">
                        <option value="" disabled selected>Seleccionar cama</option>
                        <option value="Cama 1 cilantro automático">Cama 1 cilantro automático</option>
                        <option value="Cama 2 rábano automático">Cama 2 rábano automático</option>
                        <option value="Cama 3 cilantro manual">Cama 3 cilantro manual</option>
                        <option value="Cama 4 rábano manual"> Cama 4 rábano manual</option>
                    </select>
                </div>
                <!-- Botón Filtrar -->
                <div>
                    <button id="btnFiltrar" class="btn btn-outline-primary btn-sm border-0 shadow-none">Filtrar datos</button>
                </div>
            </div>
        </div>

        <!-- Gráfico -->
        <div class="card-body">
            <div class="">
                <div class="chart-container" style="position: relative; height: 70vh; width: 100%;">
                    <canvas id="grafico-consolidado"></canvas>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnFiltrar = document.getElementById('btnFiltrar');
            const selectCama = document.getElementById('camaId');
            let chartInstance = null;

            // Función para destruir el gráfico actual
            function destruirGrafico() {
                if (chartInstance) {
                    chartInstance.destroy();
                    chartInstance = null;
                }
            }

            function cargarDatos(camaId) {
                console.log(camaId);

                $.ajax({
                    type: 'POST',
                    url: 'index.php?r=prediccion-peso-produccion-final/filtrar',
                    data: {
                        camaId: camaId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Destruir el gráfico actual
                            destruirGrafico();

                            // Datos históricos por línea
                            const datosHistoricos = response.datos_historicos;
                            // Predicciones por línea
                            const predicciones = response.predicciones;
                            console.log(response.predicciones);
                            // Preparar los datos para las gráficas
                            const etiquetas = Object.keys(datosHistoricos); // Las líneas (Línea 1, Línea 2...)
                            const datasets = [];

                            // Preparar los datasets para cada línea
                            etiquetas.forEach((linea) => {
                                const datos = datosHistoricos[linea];
                                const prediccion = predicciones[linea] ?? null;
                                const ciclos = datos[0]; // Los ciclos están en el índice 0

                                // Agregar los datos históricos (promedios, o lo que tengas)
                                datasets.push({
                                    label: "Datos históricos " + linea,
                                    data: datos[1], // Los gramajes están en el índice 1
                                    borderColor: '#36A2EB', // Color de la línea
                                    backgroundColor: 'rgba(54, 162, 235, 0.2)', // Color del fondo
                                    fill: false, // Rellenar bajo la curva
                                    borderWidth: 2
                                });

                                // Agregar las predicciones (un solo valor, puede ser un punto)
                                if (prediccion !== null) {
                                    datasets.push({
                                        label: "Predicción " + linea,
                                        data: [...datos[1], prediccion], // Añadir el valor de la predicción al final de los datos históricos
                                        borderColor: '#FF9F40', // Color de la línea de la predicción
                                        backgroundColor: 'rgba(255, 159, 64, 0.2)', // Color del fondo de la predicción
                                        fill: false, // No rellenar la curva de la predicción
                                        borderWidth: 2,
                                        pointRadius: 5, // Tamaño del punto para la predicción
                                        pointBackgroundColor: '#FF9F40' // Color del punto de la predicción
                                    });
                                }
                            });

                            // Crear el gráfico
                            const ctx = document.getElementById('grafico-consolidado').getContext('2d');
                            chartInstance = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: [...datosHistoricos['Línea 1'][0], 'Predicción'], // Las etiquetas X deben incluir los ciclos y el ciclo de predicción
                                    datasets: datasets
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        x: {
                                            title: {
                                                display: true,
                                                text: 'Ciclos'
                                            }
                                        },
                                        y: {
                                            title: {
                                                display: true,
                                                text: 'Gramaje (kg)'
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            position: 'top',
                                        },
                                        tooltip: {
                                            mode: 'index',
                                            intersect: false,
                                        }
                                    }
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Hubo un error al obtener los datos.');
                    }
                });
            }

            // Evento de click en el botón de filtrar
            btnFiltrar.addEventListener('click', function() {
                const camaSeleccionada = selectCama.value;

                if (!camaSeleccionada) {
                    alert('Por favor, selecciona una cama.');
                    return;
                }

                cargarDatos(camaSeleccionada);
            });
        });
    </script>

</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
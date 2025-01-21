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
                <div class="input-group input-group-sm" style="max-width: 162px;">
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

            function destruirGrafico() {
                if (chartInstance) {
                    chartInstance.destroy();
                    chartInstance = null;
                }
            }

            function cargarDatos(camaId) {
                console.log('Cama seleccionada:', camaId);

                $.ajax({
                    type: 'POST',
                    url: 'index.php?r=prediccion-peso-produccion-final/filtrar',
                    data: {
                        camaId: camaId
                    },
                    success: function(response) {
                        if (response.success) {
                            const datos = response.datos_historicos;
                            const predicciones = response.predicciones;

                            // Generar etiquetas del eje X con nombres de ciclos y predicción
                            const ciclos = Object.keys(datos).map(ciclo => ciclo);
                            ciclos.push('Predicción'); // Añadir "Predicción"

                            console.log("Ciclos: ", ciclos);

                            // Procesar datos históricos
                            const lineas = {};
                            Object.keys(datos).forEach(ciclo => {
                                const cultivos = datos[ciclo];
                                Object.values(cultivos).forEach(lineasCultivo => {
                                    lineasCultivo.forEach(({
                                        linea,
                                        gramaje
                                    }) => {
                                        if (!lineas[linea]) {
                                            lineas[linea] = [];
                                        }
                                        lineas[linea].push(gramaje);
                                    });
                                });
                            });

                            // Agregar las predicciones a las líneas
                            predicciones.forEach(({
                                linea,
                                gramajePredicho
                            }) => {
                                if (!lineas[linea]) {
                                    lineas[linea] = Array(ciclos.length - 1).fill(0); // Rellenar con ceros si no existían datos
                                }
                                lineas[linea].push(gramajePredicho); // Añadir predicción al final
                            });

                            // Configurar los datasets
                            const datasets = [];
                            const colores = ['#FF9F40', '#36A2EB', '#FFCD56', '#9966FF', '#4BC0C0'];
                            let colorIndex = 0;

                            Object.keys(lineas).forEach(linea => {
                                const datosLinea = lineas[linea];
                                datasets.push({
                                    label: `Dato historico línea ${linea}`,
                                    data: datosLinea,
                                    backgroundColor: colores[colorIndex % colores.length],
                                    borderColor: colores[colorIndex % colores.length],
                                    borderWidth: 2,
                                    fill: false,
                                });
                                colorIndex++;
                            });

                            // Destruir el gráfico anterior
                            destruirGrafico();

                            // Crear el nuevo gráfico
                            const ctx = document.getElementById('grafico-consolidado').getContext('2d');
                            chartInstance = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: ciclos,
                                    datasets: datasets,
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: true,
                                        },
                                        title: {
                                            display: true,
                                            text: `Cama seleccionada: ${camaId}`
                                        }
                                    },
                                    scales: {
                                        x: {
                                            title: {
                                                display: true,
                                                text: 'Ciclos realizados'
                                            }
                                        },
                                        y: {
                                            beginAtZero: true,
                                            title: {
                                                display: true,
                                                text: 'Gramaje (kg) final por línea'
                                            }
                                        }
                                    }
                                }
                            });
                        } else {
                            alert(response.message || 'Error al cargar los datos.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud:', error);
                        alert('Hubo un error al obtener los datos.');
                    }
                });
            }

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
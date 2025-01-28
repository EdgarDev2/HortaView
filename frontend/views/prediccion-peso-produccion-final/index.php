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
    <div class="card mt-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Peso final de la producción (siguiente ciclo).</h4>
        </div>

        <!-- Filtros y opciones de gráficos -->
        <div class="row p-2 mt-0 border-bottom">
            <div class="col-md-12 d-flex flex-wrap align-items-center gap-2">
                <!-- Botones para tipo de gráfico -->
                <div class="btn-group" role="group" aria-label="Opciones de gráficos">
                    <button class="<?= $btnDownloadClass ?>" type="button" title="Descargar gráfico como imagen" onclick="descargarImagen('grafico-consolidado', 'predicción_peso_producción.png')">
                        <i class="fas fa-download"></i> Descargar gráfica
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
        <div class="card-body bg-light">
            <div class="">
                <div class="chart-container" style="position: relative; height: 70vh; width: 100%;">
                    <canvas id="grafico-consolidado"></canvas>
                </div>
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
                        destruirGrafico(); // Destruir el gráfico actual

                        const datosHistoricos = response.datos_historicos; // Datos históricos por línea
                        const predicciones = response.predicciones; // Predicciones por línea
                        console.log(response.predicciones);

                        const etiquetas = Object.keys(datosHistoricos);
                        const datasets = [];
                        const colores = ['#36A2EB', '#FF6384', '#4BC0C0', '#FF9F40', '#9966FF', '#FFCD56'];

                        etiquetas.forEach((linea, index) => {
                            const datos = datosHistoricos[linea];
                            const prediccion = predicciones[linea] ?? null;

                            // Combinar datos históricos con la predicción
                            const datosCombinados = [...datos[1], prediccion];

                            // Dataset único con puntos históricos y predicción conectados
                            datasets.push({
                                label: "" + linea,
                                data: datosCombinados,
                                borderColor: colores[index % colores.length], // Color de la línea
                                fill: false, // Rellenar bajo la curva
                                borderWidth: 2,
                                pointRadius: datosCombinados.map((_, idx) =>
                                    idx === datosCombinados.length - 1 ? 7 : 4 // Si es la predicción, tamaño 6, si no, tamaño 4
                                ), // Tamaño del punto
                                pointBackgroundColor: datosCombinados.map((_, idx) =>
                                    idx === datosCombinados.length - 1 ? '#FF4500' : '#FFE0E6' // Naranja rojo para la predicción, blanco para los históricos
                                ) // Color del punto prediccion y datos historico
                            });
                        });

                        // Crear el gráfico
                        const ctx = document.getElementById('grafico-consolidado').getContext('2d');
                        chartInstance = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: [...datosHistoricos['Línea 1'][2], 'Predicción siguiente ciclo'], // Etiquetas del eje X
                                datasets: datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Ciclos realizados',
                                            font: {
                                                size: 15, // Tamaño de la fuente para todas las etiquetas
                                                weight: 'bold'
                                            },
                                        },
                                        ticks: {
                                            font: {
                                                size: 14, // Tamaño de la fuente para todas las etiquetas
                                                //weight: 'bold'
                                            },
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Gramaje (g) final por línea',
                                            font: {
                                                size: 15, // Tamaño de la fuente para todas las etiquetas
                                                weight: 'bold'
                                            },
                                        },
                                        ticks: {
                                            font: {
                                                size: 14, // Tamaño de la fuente para todas las etiquetas
                                                //weight: 'bold'
                                            },
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    title: {
                                        display: true,
                                        text: 'Ka\'anche\' seleccionado: ' + camaId, // Mostrar la cama seleccionada en el título
                                        font: {
                                            size: 15, // Tamaño de la fuente para todas las etiquetas
                                            weight: 'bold',
                                        },
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(tooltipItem) {
                                                const dataset = tooltipItem.dataset;
                                                const index = tooltipItem.dataIndex;
                                                const linea = dataset.label.split(' ')[1]; // Obtener el número de línea
                                                const tipo =
                                                    index === dataset.data.length - 1 ?
                                                    'Predicción,' :
                                                    'Dato histórico,';
                                                return `${tipo} Línea ${linea}: ${tooltipItem.raw} g`;
                                            }
                                        }
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
    // Función para descargar el gráfico
    window.descargarImagen = function(canvasId, nombreArchivo) {
        let link = document.createElement('a');
        link.href = document.getElementById(canvasId).toDataURL();
        link.download = nombreArchivo;
        link.click();
    };
</script>
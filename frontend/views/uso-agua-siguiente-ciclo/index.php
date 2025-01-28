<?php

use yii\helpers\Html;

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js');

// Clases comunes bootstrap
$btnClass = 'btn btn-outline-success btn-sm border-0 shadow-none';
$btnDownloadClass = 'btn btn-outline-primary btn-sm border-0 shadow-none';
$cardInputDate = 'input-group input-group-sm d-flex align-items-center gap-2 bg-light rounded px-2';
$inputDate = 'form-control placeholder-wave bg-transparent text-secondary';
$selectPlace = 'form-select placeholder-wave border-0 text-secondary bg-light rounded';

$this->title = 'Predicción total agua requerida para el siguiente ciclo';

?>
<div class="prediccion-uso-agua-index bg-light">
    <div class="card mt-0">
        <div class="card-header bg-primary text-white text-start">
            <h4 class="mb-0">Predicción agua requerida (siguiente ciclo).</h4>
        </div>
        <!-- Filtros y opciones de gráficos -->
        <div class="row p-2 border-bottom">
            <div class="col-md-12 d-flex flex-wrap align-items-center gap-2">
                <!-- Botones para tipo de gráfico -->
                <div class="btn-group" role="group" aria-label="Opciones de gráficos">
                    <button class="<?= $btnDownloadClass ?>" type="button" title="Descargar gráfico como imagen" onclick="descargarImagen('grafico-consolidado', 'predicción_uso_agua.png')">
                        <i class="fas fa-download"></i> Descargar gráfica
                    </button>
                </div>
                <!-- Selector de sistema de riego -->
                <div class="input-group input-group-sm" style="max-width: 231px;">
                    <select id="tipoRiego" class="<?= $selectPlace ?>" title="Selecciona sistema de riego">
                        <option value="" disabled selected>Seleccionar tipo de riego</option>
                        <option value="valvula">Riego automático</option>
                        <option value="riegomanual">Riego manual</option>
                    </select>
                </div>

                <!-- Selector de tipo de cultivo -->
                <div class="input-group input-group-sm" style="max-width: 231px;">
                    <select id="tipoCultivo" class="<?= $selectPlace ?>" title="Selecciona cultivo">
                        <option value="" disabled selected>Seleccionar cultivo</option>
                    </select>
                </div>
                <!-- Botón Filtrar -->
                <div>
                    <button id="btnFiltrar" class="btn btn-outline-primary btn-sm border-0 shadow-none">Filtrar datos</button>
                </div>
            </div>
        </div>
        <!-- Gráfico -->
        <div class="card-body bg-light"> <!--container border mb-3-->
            <div class="">
                <div class="chart-container" style="position: relative; height: 70vh; width: 100%;">
                    <canvas id="grafico-consolidado"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>
    // Opciones de cultivo según el sistema de riego
    const opcionesCultivo = {
        valvula: [{
                value: "Cama 1 cilantro automático",
                text: "Cama 1 cilantro automático"
            },
            {
                value: "Cama 2 rábano automático",
                text: "Cama 2 rábano automático"
            },
        ],
        riegomanual: [{
                value: "Cama 3 cilantro manual",
                text: "Cama 3 cilantro manual"
            },
            {
                value: "Cama 4 rábano manual",
                text: "Cama 4 rábano manual"
            },
        ],
    };

    // Elementos del DOM
    const sistemaRiegoSelect = document.getElementById("tipoRiego");
    const tipoCultivoSelect = document.getElementById("tipoCultivo");

    // Evento para cambiar las opciones del selector de cultivo
    sistemaRiegoSelect.addEventListener("change", function() {
        const seleccion = sistemaRiegoSelect.value;

        // Limpiar las opciones actuales del selector de cultivo
        tipoCultivoSelect.innerHTML = `<option value="" disabled selected>Seleccionar cultivo</option>`;

        // Agregar las nuevas opciones según la selección del sistema de riego
        if (seleccion && opcionesCultivo[seleccion]) {
            opcionesCultivo[seleccion].forEach(cultivo => {
                const option = document.createElement("option");
                option.value = cultivo.value;
                option.textContent = cultivo.text;
                tipoCultivoSelect.appendChild(option);
            });
        }
    });

    // Elementos del DOM
    const btnFiltrar = document.getElementById('btnFiltrar');
    let chartInstance = null;

    // Función para destruir el gráfico actual
    function destruirGrafico() {
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }
    }

    function cargarDatos(tipoCultivo, tipoRiego) {
        console.log('Enviando datos:', {
            tipoCultivo,
            tipoRiego
        });

        $.ajax({
            type: 'POST',
            url: 'index.php?r=uso-agua-siguiente-ciclo/filtrar',
            data: {
                tipoCultivo: tipoCultivo,
                tipoRiego: tipoRiego
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const datosHistoricos = response.datos_historicos; // Datos históricos por línea
                    const predicciones = response.predicciones; // Predicciones por línea
                    console.log('datos historicos: ', datosHistoricos);
                    console.log('predicciones: ', predicciones);

                    destruirGrafico(); // Destruir el gráfico actual

                    const etiquetas = Object.keys(datosHistoricos);
                    const datasets = [];
                    const colores = ['#36A2EB', '#FF6384', '#4BC0C0', '#FF9F40', '#9966FF', '#FFCD56'];

                    etiquetas.forEach((linea, index) => {
                        const datos = datosHistoricos[linea]; // Datos históricos para una línea
                        const prediccion = predicciones[linea] ?? 0; // Predicción para la línea, si no existe, usa 0

                        // Combinar datos históricos con la predicción
                        const datosCombinados = [...datos[1], prediccion];

                        // Dataset único con puntos históricos y predicción conectados
                        datasets.push({
                            label: "" + linea,
                            data: datosCombinados,
                            borderColor: colores[index % colores.length], // Color de la línea
                            fill: true, // No rellenar bajo la curva
                            borderWidth: 2,
                            pointRadius: datosCombinados.map((_, idx) =>
                                idx === datosCombinados.length - 1 ? 7 : 4 // Si es la predicción, tamaño 7, si no, tamaño 4
                            ),
                            pointBackgroundColor: datosCombinados.map((_, idx) =>
                                idx === datosCombinados.length - 1 ? '#FF4500' : '#FFE0E6' // Naranja rojo para la predicción, blanco para los históricos
                            )
                        });
                    });

                    // Tomamos la primera cama para las etiquetas, puedes modificar esto según tu lógica
                    const primeraCama = Object.keys(datosHistoricos)[0];
                    const etiquetasFinales = [...datosHistoricos[primeraCama][2], 'Predicción siguiente ciclo'];

                    // Crear el gráfico
                    const ctx = document.getElementById('grafico-consolidado').getContext('2d');
                    chartInstance = new Chart(ctx, {
                        type: 'line', // Tipo de gráfico
                        data: {
                            labels: etiquetasFinales, // Etiquetas con la predicción añadida
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
                                            size: 15,
                                            weight: 'bold'
                                        }
                                    },
                                    ticks: {
                                        font: {
                                            size: 14
                                        }
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Litros totales consumidos por ciclo',
                                        font: {
                                            size: 15,
                                            weight: 'bold'
                                        }
                                    },
                                    ticks: {
                                        font: {
                                            size: 14
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top'
                                },
                                title: {
                                    display: true,
                                    text: 'Cultivo seleccionado: ' + tipoCultivo, // Título estático
                                    font: {
                                        size: 15,
                                        weight: 'bold'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            const dataset = tooltipItem.dataset;
                                            const index = tooltipItem.dataIndex;
                                            const linea = dataset.label.split(' ')[1];
                                            const tipo = index === dataset.data.length - 1 ? 'Predicción,' : 'Dato histórico,';
                                            return `${tipo} ${tooltipItem.raw} Litros`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud:', {
                    xhr,
                    status,
                    error
                });
                alert('Hubo un error al obtener los datos.');
            }
        });
    }



    btnFiltrar.addEventListener('click', function() {
        const tipoCultivoo = tipoCultivoSelect.value;
        const riegoSeleccionada = sistemaRiegoSelect.value;

        if (!tipoCultivoo || !riegoSeleccionada) {
            alert('Por favor, selecciona un tipo de riego y un cultivo.');
            return;
        }

        cargarDatos(tipoCultivoo, riegoSeleccionada);
    });
    // Función para descargar el gráfico
    window.descargarImagen = function(canvasId, nombreArchivo) {
        let link = document.createElement('a');
        link.href = document.getElementById(canvasId).toDataURL();
        link.download = nombreArchivo;
        link.click();
    };
</script>
<?php
$this->title = 'Tiempo real presión barométrica';

use yii\helpers\Html;

$this->registerJsFile('https://code.jquery.com/jquery-3.6.0.min.js', [
    'depends' => [\yii\web\JqueryAsset::class],
]);

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js');
$this->registerCssFile('@web/css/chart_card.css');
// Clases comunes bootstrap
$btnClass = 'btn btn-outline-success btn-sm border-0 shadow-none';
$btnDownloadClass = 'btn btn-outline-primary btn-sm border-0 shadow-none';
$cardInputDate = 'input-group input-group-sm d-flex align-items-center gap-2 bg-light rounded px-2';
$inputDate = 'form-control placeholder-wave bg-transparent text-secondary';
$selectPlace = 'form-select placeholder-wave border-0 text-secondary bg-light rounded';
?>
<div class="tiempo-real-presion-barometrica-index">
    <!-- Mostrar el gráfico de predicciones vs valores reales -->
    <div class="card mt-0">
        <div class="card-header bg-primary text-white">
            <h4>Tiempo real presión barométrica</h4>
        </div>
        <div class="row mt-0 p-2">
            <!-- Botones de gráfico y filtros -->
            <div class="col-md-12 d-flex flex-wrap align-items-center gap-2">
                <!-- Botones de tipo de gráfico -->
                <div class="btn-group" role="group" aria-label="Gráficos">
                    <button class="<?= $btnClass ?>" type="button" title="Gráfico de tipo Lineal" onclick="cambiarTipoGrafico('line')">
                        <i class="fas fa-chart-line"></i> Lineal
                    </button>
                    <button class="<?= $btnClass ?>" type="button" title="Gráfico de tipo Barra" onclick="cambiarTipoGrafico('bar')">
                        <i class="fas fa-chart-bar"></i> Barra
                    </button>
                    <button class="<?= $btnClass ?>" type="button" title="Gráfico de tipo Radar" onclick="cambiarTipoGrafico('radar')">
                        <i class="fas fa-chart-pie"></i> Radar
                    </button>
                    <button class="<?= $btnDownloadClass ?>" type="button" title="Descargar gráfico como imagen" onclick="descargarImagen('graficoPresionBarometrica', 'grafico_presion_barometrica.png')">
                        <i class="fas fa-download"></i> Descargar
                    </button>
                </div>
                <!-- Filtro por fecha -->
                <div class="<?= $cardInputDate ?>" style="max-width: 340px;">
                    <label for="fecha" class="form-label mb-0 text-secondary">Seleccionar Fecha:</label>
                    <input type="date" id="fecha" class="<?= $inputDate ?>" style="width: 140px; border: none;" min="<?= $fechaInicio ?>" max="<?= $fechaFin ?>">
                </div>
                <!-- Botón Filtrar -->
                <div>
                    <button id="btnFiltrar" class="btn btn-outline-primary btn-sm border-0 shadow-none">Filtrar datos</button>
                </div>
            </div>
            <!--<div class="chartCard">
            <div class="chartBox">
                <canvas id="graficoPresionBarometrica" class="mt-4"></canvas>
            </div>
        </div>-->
        </div>
        <div class="card-body bg-light">
            <canvas id="graficoPresionBarometrica" class="mt-0"></canvas>
        </div>
    </div>
    <!-- Pasamos los datos de la sesión a JS -->
    <div id="data-container"
        data-fecha-minima="<?= $fechaInicio ?>"
        data-fecha-maxima="<?= $fechaFin ?>">
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('graficoPresionBarometrica').getContext('2d');
        let chart;
        let tipoGrafico = 'line'; // Tipo de gráfico inicial

        // Función para inicializar el gráfico
        function inicializarGrafico(data) {
            if (chart) {
                chart.destroy(); // Destruir gráfico previo si existe
            }

            chart = new Chart(ctx, {
                type: tipoGrafico,
                data: {
                    labels: data.labels, // Fechas u horas para el eje X
                    datasets: [{
                        label: 'Presión (hPa)',
                        data: data.presion,
                        borderColor: '#36A2EB',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderWidth: 2,
                        tension: 0.4,
                    }, {
                        label: 'Temperatura (°C)',
                        data: data.temperatura,
                        borderColor: '#FF9F40',
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        borderWidth: 2,
                        tension: 0.4,
                    }, {
                        label: 'Altitud (m)',
                        data: data.altitud,
                        borderColor: '#9966FF',
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderWidth: 2,
                        tension: 0.4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: tipoGrafico === 'radar' ? {
                        r: {
                            beginAtZero: true,
                            min: 0,
                            max: 100,
                            ticks: {
                                stepSize: 10,
                            },
                        },
                    } : {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                            },
                            title: {
                                display: true,
                                text: 'Horas de día',
                            },
                        },
                        y: {
                            min: -100,
                            max: 100,
                            beginAtZero: true,
                            ticks: {
                                stepSize: 10,
                            },
                            title: {
                                display: true,
                                text: 'Variables ambientales barométricas',
                            },
                        },
                    },
                    /*plugins: {
                        zoom: {
                            zoom: {
                                wheel: {
                                    enabled: true, // Habilitar zoom con rueda del ratón
                                    speed: 0.1, // Velocidad del zoom
                                },
                                pinch: {
                                    enabled: true, // Habilitar zoom con pinch
                                    threshold: 2, // Número de dedos para activar el zoom
                                },
                                drag: {
                                    enabled: true, // Habilitar desplazamiento
                                },
                            },
                        },
                    },*/
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
        }

        // Función para cargar datos desde el servidor
        function cargarDatos(fecha) {
            $.ajax({
                type: 'POST',
                url: 'index.php?r=tiempo-real-barometrica/ajax', // Ajustar según la URL del controlador
                data: {
                    fecha
                },
                success: function(response) {
                    if (response.success) {
                        const procesados = procesarDatos(response.data);
                        inicializarGrafico(procesados);
                    } else {
                        alert(response.message || 'Error al cargar los datos.');
                    }
                },
                error: function() {
                    alert('Error en la solicitud al servidor.');
                },
            });
        }

        // Función para procesar los datos recibidos del servidor
        function procesarDatos(datos) {
            const labels = [];
            const presion = [];
            const temperatura = [];
            const altitud = [];

            datos.forEach((dato) => {
                labels.push(dato.hora); //dato.fecha + ' ' + 
                presion.push(dato.presion);
                temperatura.push(dato.temperatura);
                altitud.push(dato.altitud);
            });

            return {
                labels,
                presion,
                temperatura,
                altitud
            };
        }

        // Función para cambiar el tipo de gráfico
        window.cambiarTipoGrafico = function(nuevoTipo) {
            tipoGrafico = nuevoTipo;
            cargarDatos(document.getElementById('fecha').value); // Recargar gráfico con el nuevo tipo
        };

        // Configurar el botón de filtrar
        document.getElementById('btnFiltrar').addEventListener('click', function() {
            const fecha = document.getElementById('fecha').value;

            if (!fecha) {
                alert('Por favor, selecciona una fecha.');
                return;
            }

            cargarDatos(fecha);
        });

        // Descargar el gráfico como imagen
        function descargarImagen(canvasId, nombreArchivo) {
            const link = document.createElement('a');
            link.href = document.getElementById(canvasId).toDataURL();
            link.download = nombreArchivo;
            link.click();
        }

        const fechaPredeterminada = document.getElementById('data-container').dataset.fechaMaxima;
        document.getElementById('fecha').value = fechaPredeterminada;
        $(document).ready(function() {
            setTimeout(clickbutton, 10);

            function clickbutton() {
                $("#btnFiltrar").click();
            }
        });
        iniciarActualizacionAutomatica();
        cargarDatos(fechaPredeterminada);
    });
</script>
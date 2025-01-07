<?php

use yii\helpers\Html;

$this->title = 'Temperatura ambiente';
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

<div class="tiempo-real-temperatura-ambiente-index">
    <h1 class="display-6 text-secondary text-left mb-3"><?= Html::encode($this->title) ?></h1>
    <div class="row">
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
                <button class="<?= $btnDownloadClass ?>" type="button" title="Descargar gráfico como imagen" onclick="descargarImagen('graficoTemperatura', 'grafico_temperatura.png')">
                    <i class="fas fa-download"></i> Descargar
                </button>
            </div>
            <!-- Filtro por fecha -->
            <div class="<?= $cardInputDate ?>" style="max-width: 340px;">
                <label for="fecha" class="form-label mb-0 text-secondary">Seleccionar Fecha:</label>
                <input type="date" id="fecha" class="<?= $inputDate ?>" style="width: 140px; border: none;" min="<?= $fechaMinima ?>" max="<?= $fechaMaxima ?>">
            </div>
            <!-- Botón Filtrar -->
            <div>
                <button id="btnFiltrar" class="btn btn-outline-primary btn-sm border-0 shadow-none">Filtrar datos</button>
            </div>
        </div>
        <div class="chartCard">
            <div class="chartBox">
                <canvas id="graficoTemperatura" class="mt-4"></canvas>
            </div>
        </div>
    </div>
    <!-- Pasamos los datos de la sesión a JS -->
    <div id="data-container"
        data-fecha-minima="<?= $fechaMinima ?>"
        data-fecha-maxima="<?= $fechaMaxima ?>">
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
<script>
    let chart; // Variable global para el gráfico
    let tipoGrafico = 'line'; // Tipo de gráfico inicial

    // Función para inicializar el gráfico
    function inicializarGrafico(data) {
        const ctx = document.getElementById('graficoTemperatura').getContext('2d');

        if (chart) {
            chart.destroy(); // Elimina el gráfico anterior si existe
        }

        chart = new Chart(ctx, {
            type: tipoGrafico,
            data: {
                labels: data.labels, // Ejes X (horas o fechas)
                datasets: [{
                        label: 'Temperatura (°C)',
                        data: data.temperatura,
                        borderColor: '#FF6384',
                        backgroundColor: 'rgba(255, 99, 132, 0.4)',
                        fill: tipoGrafico === 'line' ? false : true,
                        tension: 0.4,
                        borderWidth: 2,
                    },
                    {
                        label: 'Humedad (%)',
                        data: data.humedad,
                        borderColor: '#36A2EB',
                        backgroundColor: 'rgba(54, 162, 235, 0.4)',
                        fill: tipoGrafico === 'line' ? false : true,
                        tension: 0.4,
                        borderWidth: 2,
                    },
                ],
            },
            options: {
                animations: {
                    tension: {
                        duration: 4000,
                        easing: 'linear',
                        from: 1,
                        to: 0,
                        loop: true
                    }
                },
                responsive: true,
                //maintainAspectRatio: false, // Permite ajustar el tamaño del gráfico
                maintainAspectRatio: false, // Mantiene la proporción de aspecto
                scales: tipoGrafico === 'radar' ? {
                    r: {
                        beginAtZero: true,
                        min: 0,
                        max: 100,
                        ticks: {
                            stepSize: 10,
                        },
                        angleLines: {
                            borderDash: [0, 0, 0, 55, 250]
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
                        min: 0,
                        max: 100,
                        beginAtZero: true,
                        ticks: {
                            stepSize: 10,
                        },
                        title: {
                            display: true,
                            text: '% de temperatura y humedad del ambiente',
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
    }

    // Función para realizar la solicitud AJAX
    function cargarDatos(fecha) {
        $.ajax({
            type: 'POST',
            url: 'index.php?r=tiempo-real-temperatura-ambiente/ajax',
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

    // Procesar los datos obtenidos del servidor
    function procesarDatos(datos) {
        const labels = [];
        const temperatura = [];
        const humedad = [];

        datos.forEach((dato) => {
            labels.push(`${dato.hora}`); // Formato de hora
            temperatura.push(dato.temperatura);
            humedad.push(dato.humedad);
        });

        return {
            labels,
            temperatura,
            humedad
        };
    }

    // Cambiar el tipo de gráfico
    function cambiarTipoGrafico(nuevoTipo) {
        tipoGrafico = nuevoTipo;
        const fecha = document.getElementById('fecha').value || null;
        cargarDatos(fecha);
    }

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

    // Cargar datos al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
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
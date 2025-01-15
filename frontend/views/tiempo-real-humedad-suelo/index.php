<?php
$this->title = 'Tiempo real Humedad del suelo';

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
<div class="tiempo-real-humeda-suelo-index">
    <!-- Mostrar el gráfico de predicciones vs valores reales -->
    <div class="card mt-4">
        <div class="card-header bg-warning text-dark">
            <h4>Tiempo real humedad del suelo</h4>
        </div>
        <div class="row mt-4">
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
                    <button class="<?= $btnDownloadClass ?>" type="button" title="Descargar gráfico como imagen" onclick="descargarImagen('graficoHumedadSuelo', 'grafico_humedad_suelo.png')">
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

        </div>
        <div class="card-body">
            <canvas id="graficoHumedadSuelo" class="mt-4"></canvas>
        </div>
    </div>

    <!-- Pasamos los datos de la sesión a JS -->
    <div id="data-container"
        data-fecha-minima="<?= $fechaInicio ?>"
        data-fecha-maxima="<?= $fechaFin ?>">
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
<script>
    let chart; // Variable global para el gráfico
    let tipoGrafico = 'line'; // Tipo de gráfico inicial

    // Función para inicializar el gráfico
    function inicializarGrafico(data) {
        const ctx = document.getElementById('graficoHumedadSuelo').getContext('2d');

        if (chart) {
            chart.destroy(); // Elimina el gráfico anterior si existe
        }

        chart = new Chart(ctx, {
            type: tipoGrafico,
            data: {
                labels: data.labels, // Ejes X (fechas u horas)
                datasets: Object.keys(data.datasets).map((cama) => ({
                    label: cama, // Nombre de la cama (Cama1, Cama2, Cama3, Cama4)
                    data: data.datasets[cama], // Datos de humedad para cada cama
                    borderColor: getNextColor(), // Color aleatorio para cada cama
                    //backgroundColor: '#FF9F40',
                    fill: tipoGrafico === 'line' ? false : false,
                    tension: 0.4,
                    borderWidth: 2,
                })),
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
                        min: 0,
                        max: 100,
                        beginAtZero: true,
                        ticks: {
                            stepSize: 10,
                        },
                        title: {
                            display: true,
                            text: '% de humedad del suelo Ka\'anche\'s, 1, 2, 3 y 4',
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

    // Función para generar colores aleatorios para los datasets
    let colorIndex = 0;

    function getNextColor() {
        // Colores base
        const baseColors = ['#36A2EB', '#FF6384', '#9966FF', '#4BC0C0'];

        // Seleccionamos el color actual y luego avanzamos al siguiente
        const color = baseColors[colorIndex];

        // Actualizamos el índice para el próximo color
        colorIndex = (colorIndex + 1) % baseColors.length;

        return color;
    }

    // Función para realizar la solicitud AJAX
    function cargarDatos(fecha) {
        $.ajax({
            type: 'POST',
            url: 'index.php?r=tiempo-real-humedad-suelo/ajax', // Ajusta la URL según tu controlador
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

    function procesarDatos(datos) {
        const labels = [];
        const datasets = {};

        // Iteramos sobre cada tabla (Cama1, Cama2, Cama3, Cama4)
        for (const cama in datos) {
            if (datos.hasOwnProperty(cama)) {
                datasets[cama] = [];

                // Verificar que los datos para cada cama sean un arreglo antes de intentar recorrerlo
                if (Array.isArray(datos[cama])) {
                    datos[cama].forEach((dato) => {
                        // Usamos la fecha y hora como etiqueta (X) en el gráfico
                        if (!labels.includes(dato.hora)) { //dato.fecha + ' ' +
                            labels.push(dato.hora); //dato.fecha + ' ' + 
                        }
                        // Añadir los datos de humedad a su correspondiente cama
                        datasets[cama].push(dato.humedad);
                    });
                } else {
                    console.error('Datos no son un arreglo para la cama ' + cama);
                }
            }
        }

        return {
            labels,
            datasets
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
<?php
$this->title = 'Gráficas';

use yii\helpers\Html;

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js');
date_default_timezone_set('America/Mexico_City');
$fechaActual = date('Y-m-d');
?>

<div class="graficas-index">
    <!--<h1 class="display-6 text-success text-left">Filtrar datos de humedad del suelo</h1>
    <h5 class="text-success fw-normal text-end">Fecha actual: <span class="text-secondary"><?= Html::encode($fechaActual) ?></span></h5>
    <hr class="border border-success">-->
    <div class="row mt-1">
        <?php for ($i = 1; $i <= 4; $i++): ?>
            <div class="col-md-6 mt-3"> <!-- Clase de margen inferior -->
                <h5 class="text-secondary mb-3">Humedad por día cama Ka'anche' <?= $i ?></h5>
                <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
                    <div class="btn-group" role="group" aria-label="First group"> <!--me-2-->
                        <button class="btn btn-outline-success border-0" type="button" title="Gráfico de tipo Lineal" onclick="cambiarTipoGrafico('line', 'graficoCama<?= $i ?>')">
                            <i class="fas fa-chart-line"></i>
                        </button>
                        <button class="btn btn-outline-success border-0" type="button" title="Gráfico de tipo Barra" onclick="cambiarTipoGrafico('bar', 'graficoCama<?= $i ?>')">
                            <i class="fas fa-chart-bar"></i>
                        </button>
                        <button class="btn btn-outline-success border-0" type="button" title="Gráfico de tipo Radar" onclick="cambiarTipoGrafico('radar', 'graficoCama<?= $i ?>')">
                            <i class="fas fa-chart-pie"></i>
                        </button>
                        <button class="btn btn-outline-primary border-0" type="button"
                            title="Descargar gráfico como imagen"
                            onclick="descargarImagen('graficoCama<?= $i ?>', 'grafico_cama<?= $i ?>.png')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    <div class="input-group">
                        <input type="date"
                            class="form-control placeholder-wave bg-transparent text-secondary"
                            id="fechaCama<?= $i ?>"
                            name="fechaCama<?= $i ?>"
                            style="width: 140px; border: none;"
                            title="Selecciona una fecha para filtrar los datos"
                            onchange="actualizarGrafico(<?= $i ?>)">
                    </div>
                </div>
                <canvas id="graficoCama<?= $i ?>" style="width: 100%; height: 315px;"></canvas>
            </div>
        <?php endfor; ?>
    </div>

</div>
<?php
$this->title = 'Gráficas';
date_default_timezone_set('America/Mexico_City');
$fechaActual = date('Y-m-d');
?>
<script>
    const fechaActual = <?= json_encode($fechaActual) ?>;
    console.log(fechaActual);
    const graficos = {};

    // Inicializar gráficos al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        for (let i = 1; i <= 4; i++) {
            inicializarGrafico('graficoCama' + i, [], 'radar');
        }

    });


    function inicializarGrafico(id, data, tipo = 'radar') {
        const ctx = document.getElementById(id).getContext('2d');

        // Destruir el gráfico previo si ya existe
        if (graficos[id]) {
            graficos[id].chart.destroy();
        }

        // Guardar el tipo de gráfico
        graficos[id] = {
            tipo,
            chart: new Chart(ctx, {
                type: tipo,
                data: {
                    labels: Array.from({
                        length: 24
                    }, (_, i) => i + ':00 hrs'), // Horas del día
                    datasets: [{
                            label: 'Promedio Humedad',
                            data: data.promedios || [],
                            borderColor: '#4BC0C0',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            //pointBackgroundColor: '#4BC0C0'
                        },
                        {
                            label: 'Mínimo Humedad',
                            data: data.minimos || [],
                            borderColor: '#36A2EB',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            //pointBackgroundColor: '#36A2EB'
                        },
                        {
                            label: 'Máximo Humedad',
                            data: data.maximos || [],
                            borderColor: '#FF6384',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            //pointBackgroundColor: '#FF6384'
                        },
                    ],
                },
                options: {
                    plugins: {
                        zoom: {
                            zoom: {
                                wheel: {
                                    enabled: true,
                                    speed: 0.1
                                },
                                pinch: {
                                    enabled: true,
                                    threshold: 2
                                },
                                drag: {
                                    enabled: true
                                }
                            }
                        }
                    },
                    scales: tipo === 'radar' ? {
                        r: {
                            min: 0,
                            max: 100,
                            beginAtZero: true,
                            ticks: {
                                stepSize: 10
                            }
                        }
                    } : {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        },
                        y: {
                            min: 0,
                            max: 100,
                            beginAtZero: true,
                            ticks: {
                                stepSize: 10
                            }
                        }
                    },
                    responsive: true,
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutElastic'
                    }
                }

            }),
        };
    }

    // Cambiar tipo de gráfico
    cambiarTipoGrafico = function(tipo, id) {
        const data = graficos[id].chart.data; // Mantener los datos
        inicializarGrafico(id, {
            promedios: data.datasets[0].data,
            maximos: data.datasets[1].data,
            minimos: data.datasets[2].data,
        }, tipo);
    };

    // Actualizar gráfico al filtrar por fecha
    actualizarGrafico = function(Id) {
        const fechaInput = document.getElementById('fechaCama' + Id);
        const fecha = fechaInput.value;

        if (!fecha) {
            alert('Por favor selecciona una fecha válida.');
            return;
        }
        // Enviar solicitud AJAX
        ajax(fecha, Id);
    };

    function ajax(fecha, idcama) {
        $.ajax({
            url: 'index.php?r=graficas-por-dia/ajax',
            type: 'POST',
            data: {
                fecha: fecha,
                camaId: 'fechaCama' + idcama // Usar idcama, no 'Id'
            },
            success: function(response) {
                if (response.success) {
                    const idGrafico = `graficoCama${idcama}`; // Aquí solo quise aplicar interpolación de cadenas.
                    inicializarGrafico(idGrafico, {
                        promedios: response.promedios,
                        maximos: response.maximos,
                        minimos: response.minimos,
                    }, graficos[idGrafico].tipo); // Usar el tipo actual del gráfico
                } else {
                    alert(response.message || 'No se encontraron datos para la fecha seleccionada.');
                }
            },
            error: function() {
                alert('Error al conectar con el servidor.');
            }
        });
    }

    function descargarImagen(canvasId, fileName) {
        const canvas = document.getElementById(canvasId);
        if (canvas) {
            const link = document.createElement('a'); // Crear un enlace
            link.href = canvas.toDataURL('image/png'); // Convertir el canvas a una URL de imagen
            link.download = fileName; // Asignar un nombre al archivo descargable
            link.click(); // Simular el clic para iniciar la descarga
        } else {
            alert('No se pudo encontrar el gráfico para descargar.');
        }
    }
</script>
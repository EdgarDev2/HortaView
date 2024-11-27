<?php
$this->title = 'Gráficas';

use yii\helpers\Html;

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js');

?>

<div class="graficas-index">
    <h1 class="display-6 text-success text-left">Filtrar datos de humedad del suelo</h1>
    <h5 class="text-success fw-normal text-end">Fecha actual: <span class="text-secondary"><?= Html::encode($fechaActual) ?></span></h5>
    <hr class="border border-success">
    <?= $this->render('_graficas', [
        'resultados' => $resultados,
    ]) ?>
    <div class="row">
        <?= $this->render('_search', []) ?>
        <?php for ($i = 1; $i <= 4; $i++): ?>
            <div class="col-md-6">
                <h5 class="text-secondary">Humedad por día cama Ka'anche' <?= $i ?></h5>
                <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
                    <div class="btn-group me-2" role="group" aria-label="First group">
                        <div class="btn-group me-2" role="group" aria-label="First group">
                            <button class="btn btn-outline-success border-0" type="button" onclick="window.cambiarTipoGrafico('line', 'graficoCama<?= $i ?>')">
                                <i class="fas fa-chart-line"></i>
                            </button>
                            <button class="btn btn-outline-success border-0" type="button" onclick="window.cambiarTipoGrafico('bar', 'graficoCama<?= $i ?>')">
                                <i class="fas fa-chart-bar"></i>
                            </button>
                            <button class="btn btn-outline-success border-0" type="button" onclick="window.cambiarTipoGrafico('radar', 'graficoCama<?= $i ?>')">
                                <i class="fas fa-chart-pie"></i>
                            </button>
                            <button class="btn btn-outline-success border-0" type="button" onclick="window.cambiarTipoGrafico('radar', 'graficoCama<?= $i ?>')">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>

                    </div>
                    <div class="input-group">
                        <input type="date"
                            class="form-control placeholder-wave bg-transparent text-secondary"
                            id="fechaCama<?= $i ?>"
                            name="fechaCama<?= $i ?>"
                            style="width: 140px; border: none;"
                            title="Selecciona una fecha para filtrar los datos">
                    </div>
                </div>
                <canvas id="graficoCama<?= $i ?>"></canvas>
            </div>
        <?php endfor; ?>
    </div>
</div>

<?php
$promedios = json_encode($resultados);
$maximos = json_encode($resultados);
$minimos = json_encode($resultados);

$this->registerJs(<<<JS
    const prome = JSON.parse('{$promedios}');
    const max = JSON.parse('{$maximos}');
    const min = JSON.parse('{$minimos}');

    const horas = Array.from({ length: 24 }, (_, i) => {
        const hora = i < 10 ? '0' + i + ':00' : i + ':00'; // Formato HH:00
        return hora;
    });

    const obtenerDatosCama = (cama) => ({
        horas: horas,
        humedades: horas.map(hora => prome[hora]?.['promedio_humedad_' + cama] ?? 0),
        maxHumedades: horas.map(hora => max[hora]?.['max_humedad_' + cama] ?? 0),
        minHumedades: horas.map(hora => min[hora]?.['min_humedad_' + cama] ?? 0)
    });

    // Almacenar los datos de las camas
    const datosCamas = {
        Cama1: obtenerDatosCama('Cama1'),
        Cama2: obtenerDatosCama('Cama2'),
        Cama3: obtenerDatosCama('Cama3'),
        Cama4: obtenerDatosCama('Cama4')
    };

    // Almacenar las instancias de los gráficos
    const graficos = {};

    function crearGrafico(canvasId, data, tipo = 'radar') {
        // Eliminar completamente el gráfico existente
        if (graficos[canvasId]) {
            graficos[canvasId].destroy();
        }

        // Crear un nuevo gráfico
        const ctx = document.getElementById(canvasId).getContext('2d');
        graficos[canvasId] = new Chart(ctx, {
            type: tipo,
            data: {
                labels: data.horas,
                datasets: [
                    {
                        label: 'Promedio de Humedad',
                        data: data.humedades,
                        backgroundColor: 'rgba(25, 135, 84, 0.3)', 
                        borderColor: 'rgba(25, 135, 84, 1)', 
                        pointBackgroundColor: 'rgba(25, 135, 84, 1)', 
                        borderWidth: 1
                    },
                    {
                        label: 'Humedad Máxima',
                        data: data.maxHumedades,
                        backgroundColor: 'rgba(255, 99, 132, 0.3)', 
                        borderColor: 'rgba(255, 99, 132, 1)', 
                        pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Humedad Mínima',
                        data: data.minHumedades,
                        backgroundColor: 'rgba(54, 162, 235, 0.3)',
                        borderColor: 'rgba(54, 162, 235, 1)', 
                        pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }
                ]
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
                responsive: true,
                animation: {
                    duration: 1000,
                    easing: 'easeInOutElastic'
                }
            }
        });
    }

    // Exponer la función globalmente
    window.cambiarTipoGrafico = function(tipo, canvasId) {
        const cama = canvasId.replace('graficoCama', 'Cama'); // Obtener el nombre de la cama
        const data = datosCamas[cama];
        crearGrafico(canvasId, data, tipo); // Recrear el gráfico con el nuevo tipo
    };

    // Crear gráficos iniciales
    crearGrafico('graficoCama1', datosCamas['Cama1'], 'radar');
    crearGrafico('graficoCama2', datosCamas['Cama2'], 'radar');
    crearGrafico('graficoCama3', datosCamas['Cama3'], 'radar');
    crearGrafico('graficoCama4', datosCamas['Cama4'], 'radar');
JS);

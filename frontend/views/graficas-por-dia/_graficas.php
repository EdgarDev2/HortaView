<?php

/** @var array $promedios */
/** @var array $maximos */
/** @var array $minimos */

$this->render('_search', []);

/**
 * Se soluciona el problema json_encode() que se origina dentro del bloque <<<JS o heredoc.
 * Razón del problema: PHP no interpreta variables dentro de un bloque heredoc.
 * Solución: Codificar los arreglos en JSON e inyectarlo dentro de heredoc.
 */
$promedios = json_encode($promedios);
$maximos = json_encode($maximos);
$minimos = json_encode($minimos);

$this->registerJs(<<<JS
        //Se inyectan los datos a las gráficas.
        const prome = JSON.parse('{$promedios}');
        const max = JSON.parse('{$maximos}');
        const min = JSON.parse('{$minimos}');

        function crearGrafico(canvasId, data, tipo) {
            new Chart(document.getElementById(canvasId), {
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
                        duration: 2500, // Duración de la transición en milisegundos
                        easing: 'easeInOutElastic' // Efecto de la transición
                    }
                }
            });
        }

        // Preparar los datos para cada gráfico
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

        crearGrafico('graficoCama1', obtenerDatosCama('Cama1'), 'radar');
        crearGrafico('graficoCama2', obtenerDatosCama('Cama2'), 'radar');
        crearGrafico('graficoCama3', obtenerDatosCama('Cama3'), 'radar');
        crearGrafico('graficoCama4', obtenerDatosCama('Cama4'), 'radar');
    JS);

<?php
date_default_timezone_set('America/Mexico_City');
$fechaActual = date('Y-m-d');
$this->registerJs(<<<JS
    const fechaActual = '{$fechaActual}';
    // Se envía una solicitud Ajax al backend. Específicamente a la acción 'actionAjax' del controlador 'GraficasController'.
    function cargarDatos(camaId, fechaSeleccionada) {
        $.ajax({
            url: 'index.php?r=graficas-por-dia/ajax',
            type: 'POST',
            data: {
                fecha: fechaSeleccionada,
                camaId: camaId
            },
            success: function(response) {
                if (response.success) {
                    actualizarGrafica(camaId, response.promedios, response.maximos, response.minimos);// Se procesa la respuesta.
                } else {
                    console.error(response.message || 'Error al obtener los datos');
                }
            },
            error: function() {
                console.error('Error en la solicitud AJAX');
            }
        });
    }

    // Listener genérico para cambios en los inputs de fecha
    $('input[id^="fechaCama"]').on('change', function() {
        const camaId = $(this).attr('id'); 
        const fechaSeleccionada = $(this).val(); 
        cargarDatos(camaId, fechaSeleccionada);
    });

    // Función para actualizar la gráfica específica con los nuevos datos.
    function actualizarGrafica(camaId, promedios, maximos, minimos) {
        let chartId = '';
        switch(camaId) {
            case 'fechaCama1': chartId = 'graficoCama1'; break;
            case 'fechaCama2': chartId = 'graficoCama2'; break;
            case 'fechaCama3': chartId = 'graficoCama3'; break;
            case 'fechaCama4': chartId = 'graficoCama4'; break;
        }
        
        // Busca la instancia de la gráfica previamente inicializada con el ID.
        const chart = Chart.getChart(chartId); // Al encontrar la gráfica, devuelve su instancia para modificarla.
        if (chart) {
            chart.data.datasets[0].data = promedios; // Modificación de los datos.
            chart.data.datasets[1].data = maximos;   
            chart.data.datasets[2].data = minimos;   
            chart.update(); // Se actualiza la instancia existente.
        } else {
            console.warn('No se encontro la gráfica con ID: ', chartId);
        }
    }

    // Actualización automática cada 5 minutos (300000 ms)
    setInterval(function() {
        cargarDatos('fechaCama1', fechaActual);
        cargarDatos('fechaCama2', fechaActual);
        cargarDatos('fechaCama3', fechaActual);
        cargarDatos('fechaCama4', fechaActual);
    }, 300000); // 5 minutos

    // Cargar automáticamente los datos de la fecha actual al ejecutar página.
    cargarDatos('fechaCama1', fechaActual);
    cargarDatos('fechaCama2', fechaActual);
    cargarDatos('fechaCama3', fechaActual);
    cargarDatos('fechaCama4', fechaActual);
JS);

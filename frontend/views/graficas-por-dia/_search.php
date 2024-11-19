<?php
$fechaActual = date('Y-m-d');
$this->registerJs(<<<JS
    const fechaActual = '{$fechaActual}';
    // Se envía una solicitud Ajax al backend. Específicamente a la acción 'actionAjax' del controlador 'GraficasController'.
    function cargarDatos(camaId, fechaSeleccionada) {
        console.log('Datos enviados:', { fecha: fechaSeleccionada, camaId: camaId });
        $.ajax({
            url: 'index.php?r=graficas-por-dia/ajax',
            type: 'POST',
            data: {
                fecha: fechaSeleccionada,
                camaId: camaId
            },
            success: function(response) {
                if (response.success) {
                    console.log();
                    console.log('Response');
                    console.log('Promedios:', response.promedios);
                    console.log('Máximos:', response.maximos);
                    console.log('Mínimos:', response.minimos);
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

    // Listener para los cambios en los inputs de fecha con jQuery y funciones anónimas.
    $('#fechaCama1').on('change', function() {
        cargarDatos('fechaCama1', $(this).val()); // Se llama a la función cargarDatos con dos argumentos camaId y fechaSeleccionada.
    });

    $('#fechaCama2').on('change', function() {
        cargarDatos('fechaCama2', $(this).val());
    });

    $('#fechaCama3').on('change', function() {
        cargarDatos('fechaCama3', $(this).val());
    });

    $('#fechaCama4').on('change', function() {
        cargarDatos('fechaCama4', $(this).val());
    });

    // Función para actualizar la gráfica específica con los nuevos datos.
    function actualizarGrafica(camaId, promedios, maximos, minimos) {
        let chartId = '';
        switch(camaId) {
            case 'fechaCama1':
                chartId = 'graficoCama1';
                break;
            case 'fechaCama2':
                chartId = 'graficoCama2';
                break;
            case 'fechaCama3':
                chartId = 'graficoCama3';
                break;
            case 'fechaCama4':
                chartId = 'graficoCama4';
                break;
        }
        
        // Busca la instancia de la gráfica previamente inicializada con el ID.
        const chart = Chart.getChart(chartId); // Al encontrar la gráfica, devuelve su instancia para modificarla.
        chart.data.datasets[0].data = promedios; // Modificación de los datos.
        chart.data.datasets[1].data = maximos;   
        chart.data.datasets[2].data = minimos;   
        chart.update(); // Se actualiza la instancia existente.
    }
    // Cargar automáticamente los datos de la fecha actual para cada cama al cargar la página.
    cargarDatos('fechaCama1', fechaActual);
    cargarDatos('fechaCama2', fechaActual);
    cargarDatos('fechaCama3', fechaActual);
    cargarDatos('fechaCama4', fechaActual);
JS);

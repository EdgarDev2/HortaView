<?php

namespace common\components;

use Yii;

class DbHandler
{
    public static function obtenerCicloYFechas()
    {
        date_default_timezone_set('America/Mexico_City');
        $fechaActual = date('Y-m-d');

        // Obtener todos los ciclos para el dropdown mostrado en todas las vistas "seleccionar ciclo".
        $ciclos = Yii::$app->db->createCommand("
        SELECT 
            cicloId, 
            descripcion, 
            ciclo, 
            DATE_FORMAT(fechaInicio, '%Y-%m-%d %H:%i:%s') as fechaInicio, 
            DATE_FORMAT(fechaFin, '%Y-%m-%d %H:%i:%s') as fechaFin
        FROM CicloSiembra
        ORDER BY ciclo ASC")->queryAll();

        if (empty($ciclos)) { // Si la base de datos esta vacío.
            Yii::$app->session->setFlash('error', 'No hay ciclos disponibles en este momento.');
        }

        // Pasar los ciclos al layout disponible en frontend/views/layouts/main.php.
        Yii::$app->view->params['ciclos'] = $ciclos;

        // Recuperar el ciclo seleccionado de la sesión actual.
        $cicloSeleccionado = Yii::$app->session->get('cicloSeleccionado');

        // Buscar el ciclo seleccionado directamente en la base de datos.
        $ciclo = Yii::$app->db->createCommand("
        SELECT 
            cicloId, 
            DATE_FORMAT(fechaInicio, '%Y-%m-%d') as fechaInicio, 
            DATE_FORMAT(fechaFin, '%Y-%m-%d') as fechaFin
        FROM CicloSiembra
        WHERE cicloId = :cicloId
        LIMIT 1")->bindValue(':cicloId', $cicloSeleccionado)->queryOne();

        // Determinar las fechas de inicio y final.
        if ($ciclo) {
            $fechaInicio = $ciclo['fechaInicio'];
            $fechaFinal = $ciclo['fechaFin'];
        } else {
            $fechaInicio = $fechaActual;
            $fechaFinal = $fechaActual;
        }
        // Retornar los valores a los controladores de las vistas.
        return [
            'cicloSeleccionado' => $cicloSeleccionado,
            'fechaInicio' => $fechaInicio,
            'fechaFinal' => $fechaFinal,
        ];
    }


    public static function guardarCicloEnSesion($cicloId)
    {
        if ($cicloId) {
            $ciclo = Yii::$app->db->createCommand("
                SELECT cicloId, descripcion, fechaInicio, fechaFin, ciclo
                FROM CicloSiembra
                WHERE cicloId = :cicloId
                LIMIT 1
            ")->bindValue(':cicloId', $cicloId)->queryOne();
            if ($ciclo) {
                // Guardar el ciclo seleccionado en la sesión
                Yii::$app->session->set('cicloSeleccionado', $ciclo['cicloId']);
                Yii::$app->session->setFlash('success', 'Se seleccionó: ' . $ciclo['descripcion']);
                return true;
            } else {
                Yii::$app->session->setFlash('error', 'El ciclo seleccionado no es válido.');
                return false;
            }
        } else {
            Yii::$app->session->setFlash('error', 'No se envió un ciclo válido.');
            return false;
        }
    }

    public static function obtenerMetricasHumedad($camaId, $fecha)
    {
        // Validar que los parámetros no estén vacíos
        if (!$camaId || !$fecha) {
            throw new \InvalidArgumentException('Los parámetros $camaId y $fecha son obligatorios.');
        }

        // Realizar la consulta usando el Query Builder
        $data = Yii::$app->db->createCommand("
            SELECT 
                HOUR(hora) as hora, 
                AVG(humedad) as promedio_humedad, 
                MAX(humedad) as max_humedad, 
                MIN(humedad) as min_humedad
            FROM {$camaId}
            WHERE fecha = :fecha
            GROUP BY HOUR(hora)
            ORDER BY hora ASC
        ")
            ->bindValue(':fecha', $fecha)
            ->queryAll();

        // Inicializar el arreglo de resultados
        $resultados = [
            'promedios' => array_fill(0, 24, null),
            'maximos' => array_fill(0, 24, null),
            'minimos' => array_fill(0, 24, null),
        ];

        // Procesar los datos obtenidos
        foreach ($data as $entry) {
            $hora = (int)$entry['hora']; // Convertir la hora a entero
            $resultados['promedios'][$hora] = (float)$entry['promedio_humedad'];
            $resultados['maximos'][$hora] = (float)$entry['max_humedad'];
            $resultados['minimos'][$hora] = (float)$entry['min_humedad'];
        }

        return $resultados;
    }
}

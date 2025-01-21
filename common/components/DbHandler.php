<?php

namespace common\components;

use Yii;

use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;

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

    public static function obtenerDatosPorCama($camaId, $segundoParametro)
    {
        // Construir la consulta SQL con parámetros dinámicos
        $query = "
        SELECT 
            cs.descripcion AS ciclo,
            cs.descripcion AS descripcionCiclo,
            cs.fechaInicio AS fechaInicioCiclo,
            cs.fechaFin AS fechaFinCiclo,
            c.cultivoId AS cultivoId, -- Id a guardar
            c.nombreCultivo AS cultivo,
            c.tipoRiego AS tipoRiego,
            lc.numeroLinea AS linea,
            lc.gramaje AS gramajeLinea
        FROM ciclosiembra cs
        LEFT JOIN cultivo c ON cs.cicloId = c.cicloId
        LEFT JOIN lineacultivo lc ON c.cultivoId = lc.cultivoId
        WHERE c.nombreCultivo = :camaId
        AND (:segundoParametro = '' OR c.tipoRiego = :segundoParametro) -- Considerar el tipo de riego solo si se especifica
        ORDER BY cs.fechaInicio, cs.cicloId, c.nombreCultivo, lc.numeroLinea;";

        // Ejecutar la consulta con los parámetros
        $datos = Yii::$app->db->createCommand($query, [
            ':camaId' => $camaId,
            ':segundoParametro' => $segundoParametro,
        ])->queryAll();

        // Organizar la información
        $resultados = [];
        foreach ($datos as $dato) {
            $ciclo = $dato['ciclo'];
            $cultivo = $dato['cultivo'];
            $linea = $dato['linea'];
            $gramaje = $dato['gramajeLinea'];

            // Crear entrada para el ciclo si no existe
            if (!isset($resultados[$ciclo])) {
                $resultados[$ciclo] = [];
            }

            // Crear entrada para el cultivo si no existe
            if (!isset($resultados[$ciclo][$cultivo])) {
                $resultados[$ciclo][$cultivo] = [];
            }

            // Añadir línea y su gramaje
            $resultados[$ciclo][$cultivo][] = [
                'linea' => $linea,
                'gramaje' => $gramaje
            ];
        }

        // Retornar los resultados organizados
        return $resultados;
    }


    public static function predecirPesoLineas($camaId, $segundoParametro)
    {
        // Obtener los datos organizados
        $datos = self::obtenerDatosPorCama($camaId, $segundoParametro);

        // Inicializar arrays para muestras y objetivos
        $samples = [];
        $targets = [];

        // Recorrer los datos para construir el conjunto de entrenamiento
        foreach ($datos as $ciclo => $cultivos) {
            foreach ($cultivos as $cultivo => $lineas) {
                foreach ($lineas as $lineaData) {
                    // Calcular duración de la siembra (si las fechas están disponibles)
                    $fechaInicio = isset($lineaData['fechaInicio']) ? strtotime($lineaData['fechaInicio']) : null;
                    $fechaFin = isset($lineaData['fechaFin']) ? strtotime($lineaData['fechaFin']) : null;
                    $duracion = ($fechaInicio && $fechaFin) ? ($fechaFin - $fechaInicio) / (60 * 60 * 24) : 0;

                    // Validar que la duración sea positiva
                    if ($duracion < 0) {
                        $duracion = 0;
                    }

                    // Construir la muestra con línea y duración
                    $samples[] = [
                        (float)$lineaData['linea'], // Línea
                        (float)$duracion,          // Duración de la siembra
                    ];

                    // Agregar el objetivo (gramaje)
                    $targets[] = (float)$lineaData['gramaje'];
                }
            }
        }

        // Verificar si hay suficientes datos para entrenar el modelo
        if (count($samples) < 2) {
            return [
                'error' => 'No hay suficientes datos históricos para realizar la predicción.'
            ];
        }

        // Crear el modelo SVR
        $regression = new SVR(Kernel::LINEAR);

        // Entrenar el modelo con los datos históricos
        $regression->train($samples, $targets);

        // Preparar las líneas para las predicciones con duraciones estimadas
        $lineasParaPredecir = [
            [1, 40], // Línea 1 con duración de 30 días
            [2, 30], // Línea 2 con duración de 30 días
            [3, 30],
            [4, 30],
            [5, 30],
            [6, 30],
        ];

        // Realizar las predicciones
        $predicciones = $regression->predict($lineasParaPredecir);

        // Construir el resultado con las líneas y sus predicciones
        $resultados = [];
        foreach ($lineasParaPredecir as $indice => $linea) {
            $resultados[] = [
                'linea' => $linea[0],
                'gramajePredicho' => round($predicciones[$indice], 5), // Redondear para claridad
            ];
        }

        return $resultados;
    }





    /*public static function obtenerGramajeCultivoTotal()
    {
        $query = "
            SELECT 
                cs.cicloId AS ciclo,
                cs.descripcion AS descripcionCiclo,
                cs.fechaInicio AS fechaInicioCiclo,
                cs.fechaFin AS fechaFinCiclo,
                c.cultivoId AS cultivoId, -- Id a guardar
                c.nombreCultivo AS cultivo,
                c.tipoRiego AS tipoRiego,
                c.gramaje AS gramajeCultivoTotal, -- Gramaje directo de la tabla cultivo
                lc.numeroLinea AS linea,
                lc.gramaje AS gramajeLinea
            FROM ciclosiembra cs
            LEFT JOIN cultivo c ON cs.cicloId = c.cicloId
            LEFT JOIN lineacultivo lc ON c.cultivoId = lc.cultivoId
            ORDER BY cs.fechaInicio, cs.cicloId, c.nombreCultivo, lc.numeroLinea;
        ";

        return Yii::$app->db->createCommand($query)->queryAll();
    }*/

    public static function obtenerDatosConPrediccion()
    {
        /*// Obtener los datos históricos
        $data = self::obtenerGramajeCultivoTotal();

        $datosPorCama = [];
        $prediccionesPorCama = [];

        foreach ($data as $entry) {
            $cama = $entry['ciclo'];
            $linea = $entry['linea'];
            $gramaje = (float)$entry['gramajeCultivoTotal'];
            $diasTranscurridos = (strtotime($entry['fechaFinCiclo']) - strtotime($entry['fechaInicioCiclo'])) / (60 * 60 * 24);

            $datosPorCama[$cama][$linea][] = [
                'dias' => $diasTranscurridos,
                'gramaje' => $gramaje,
            ];
        }

        // Generar predicción para cada cama
        foreach ($datosPorCama as $cama => $lineas) {
            foreach ($lineas as $linea => $registros) {
                // Usar el último dato histórico para predecir
                $ultimoRegistro = end($registros);
                $dias = $ultimoRegistro['dias'];
                $gramaje = $ultimoRegistro['gramaje'];

                // Predecir (esto es un ejemplo; usa tu lógica de predicción real)
                $prediccion = $gramaje * 1.1; // Incremento estimado del 10%

                $prediccionesPorCama[$cama][$linea] = [
                    'dias' => $dias + 30, // Asume 30 días adicionales
                    'gramaje' => $prediccion,
                ];
            }
        }

        return ['historicos' => $datosPorCama, 'predicciones' => $prediccionesPorCama];*/
    }
}

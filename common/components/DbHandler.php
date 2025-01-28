<?php

namespace common\components;

use Phpml\Regression\LeastSquares;
use Yii;

//use Phpml\Regression\SVR;
//use Phpml\SupportVectorMachine\Kernel;

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

        if (empty($ciclos)) { // Si la base de datos está vacío.
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
            descripcion,
            DATE_FORMAT(fechaInicio, '%Y-%m-%d') as fechaInicio, 
            DATE_FORMAT(fechaFin, '%Y-%m-%d') as fechaFin
        FROM CicloSiembra
        WHERE cicloId = :cicloId
        LIMIT 1")->bindValue(':cicloId', $cicloSeleccionado)->queryOne();

        // Determinar las fechas de inicio, final y la descripción.
        if ($ciclo) {
            $fechaInicio = $ciclo['fechaInicio'];
            $fechaFinal = $ciclo['fechaFin'];
            $descripcion = $ciclo['descripcion'];
        } else {
            $fechaInicio = $fechaActual;
            $fechaFinal = $fechaActual;
            $descripcion = null; // No hay descripción si no se encuentra el ciclo.
        }

        // Retornar los valores a los controladores de las vistas.
        return [
            'cicloSeleccionado' => $cicloSeleccionado,
            'fechaInicio' => $fechaInicio,
            'fechaFinal' => $fechaFinal,
            'descripcion' => $descripcion,
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

    public static function obtenerDatosPorCama($nombreCultivo)
    {
        // Construir la consulta SQL con los parámetros proporcionados
        $query = "
        SELECT 
            cs.descripcion AS descripcionCiclo,
            cs.ciclo AS numeroCiclo,
            cs.fechaInicio AS inicioCiclo,
            c.nombreCultivo,
            c.tipoRiego,
            lc.numeroLinea,
            lc.gramaje
        FROM ciclosiembra cs
        LEFT JOIN cultivo c ON cs.cicloId = c.cicloId
        LEFT JOIN lineacultivo lc ON c.cultivoId = lc.cultivoId
        WHERE c.nombreCultivo = :nombreCultivo
        AND lc.numeroLinea IS NOT NULL
        AND lc.gramaje IS NOT NULL
        ORDER BY cs.fechaInicio, c.nombreCultivo ASC, lc.numeroLinea;
        ";

        // Ejecutar la consulta con el parámetro
        $resultados = Yii::$app->db->createCommand($query, [
            ':nombreCultivo' => $nombreCultivo,
        ])->queryAll();
        // Organizar los datos por numeroLinea
        $data = [];
        foreach ($resultados as $row) {
            $linea = 'Línea ' . $row['numeroLinea'];  // Formatear como 'Línea X'
            $numeroCiclo = (int)$row['numeroCiclo'];
            $gramaje = (int)$row['gramaje'];
            $descripcionCiclo = $row['descripcionCiclo'];

            // Inicializar la línea si no existe en el array $data
            if (!isset($data[$linea])) {
                $data[$linea] = [
                    [],  // Ciclos
                    [],  // Gramajes
                    []   // Descripciones
                ];
            }

            // Verificar si el ciclo ya existe en el array para esa línea
            if (!in_array($numeroCiclo, $data[$linea][0])) {
                // Agregar los datos del ciclo, gramaje y descripción en el array correspondiente
                $data[$linea][0][] = $numeroCiclo;        // Ciclo
                $data[$linea][1][] = $gramaje;           // Gramaje
                $data[$linea][2][] = $descripcionCiclo;  // Descripción del ciclo
            }
        }

        // Devolver los datos organizados
        return $data;
    }



    public static function predecirPesoLineas($nombreCultivo)
    {
        // Obtener los datos organizados
        $data = self::obtenerDatosPorCama($nombreCultivo);

        $predicciones = [];

        // Iterar sobre cada línea
        foreach ($data as $line => [$samples, $targets]) {
            // Validar que los datos sean correctos, que haya al menos 2 ciclos y que los targets no contengan valores nulos
            if (count($samples) > 1 && count($samples) == count($targets)) {
                // Filtrar los valores nulos tanto en samples como en targets
                $validSamples = array_filter($samples, function ($ciclo, $index) use ($targets) {
                    // Asegurarse de que tanto el ciclo (sample) como el gramaje (target) no sean nulos
                    return $ciclo !== null && $targets[$index] !== null;
                }, ARRAY_FILTER_USE_BOTH);

                $validTargets = array_filter($targets, function ($target) {
                    return $target !== null;
                });

                // Si después de filtrar quedan suficientes datos, proceder
                if (count($validSamples) > 1 && count($validSamples) == count($validTargets)) {
                    // Crear una nueva instancia de la regresión por mínimos cuadrados
                    $regression = new LeastSquares();

                    // Convertir las muestras a formato adecuado (matriz 2D)
                    $validSamples = array_map(function ($ciclo) {
                        return [$ciclo]; // Cada ciclo es una muestra individual
                    }, $validSamples);

                    // Entrenar el modelo con las muestras y los targets (gramajes)
                    $regression->train($validSamples, $validTargets);

                    // Predecir el gramaje para el ciclo 5
                    $prediction = $regression->predict([[5]]); // Ciclo 5 como entrada

                    // Guardar la predicción en el array
                    $predicciones[$line] = round($prediction[0], 2);
                } else {
                    // Si no hay suficientes datos válidos, se asigna un valor nulo
                    $predicciones[$line] = null;
                }
            } else {
                // Si los datos no son válidos, se asigna un valor nulo
                $predicciones[$line] = null;
            }
        }

        return $predicciones;
    }

    public static function obtenerDatosGerminacion($nombreCultivo)
    {
        // Construcción de la consulta SQL
        $query = "
            SELECT 
                cs.descripcion AS descripcionCiclo,
                cs.ciclo AS numeroCiclo,
                c.nombreCultivo,
                rg.linea AS numeroLinea,
                MAX(rg.numeroZurcosGerminados) AS maximoZurcosGerminados,
                c.surcosSembrados,
                (MAX(rg.numeroZurcosGerminados) / c.surcosSembrados) * 100 AS porcentajeGerminacion
            FROM ciclosiembra cs
            LEFT JOIN cultivo c ON cs.cicloId = c.cicloId
            LEFT JOIN registrogerminacion rg ON c.cultivoId = rg.cultivoId
            WHERE c.nombreCultivo = :nombreCultivo
            AND rg.linea IS NOT NULL
            AND rg.numeroZurcosGerminados IS NOT NULL
            AND c.surcosSembrados > 0
            GROUP BY cs.descripcion, cs.ciclo, c.nombreCultivo, rg.linea, c.surcosSembrados
            ORDER BY cs.descripcion ASC, rg.linea ASC;
        ";


        try {
            // Ejecutar la consulta con el parámetro proporcionado
            $resultados = Yii::$app->db->createCommand($query, [
                ':nombreCultivo' => $nombreCultivo,
            ])->queryAll();

            // Procesar los resultados
            $data = [];
            foreach ($resultados as $row) {
                $linea = 'Línea ' . $row['numeroLinea'];  // Formatear como 'Línea X'
                $numeroCiclo = (int)$row['numeroCiclo'];
                $porcentajeGerminacion = (float)$row['porcentajeGerminacion']; // Convertir a float
                $descripcionCiclo = $row['descripcionCiclo'];

                // Inicializar la línea si no existe en el array $data
                if (!isset($data[$linea])) {
                    $data[$linea] = [
                        [],  // Ciclos
                        [],  // Porcentajes de germinación
                        []   // Descripciones
                    ];
                }

                // Verificar si el ciclo ya existe en el array para esa línea
                if (!in_array($numeroCiclo, $data[$linea][0])) {
                    // Agregar los datos del ciclo, porcentaje de germinación y descripción en el array correspondiente
                    $data[$linea][0][] = $numeroCiclo;           // Ciclo
                    $data[$linea][1][] = $porcentajeGerminacion; // Porcentaje de germinación
                    $data[$linea][2][] = $descripcionCiclo;      // Descripción del ciclo
                }
            }

            // Devolver los datos organizados
            return $data;
        } catch (\Exception $e) {
            // Manejo de errores
            Yii::error("Error al obtener datos de germinación: " . $e->getMessage(), __METHOD__);
            return []; // Retornar un arreglo vacío en caso de error
        }
    }

    public static function predecirPorcentajeDeGerminacionByLineas($nombreCultivo)
    {
        // Obtener los datos organizados
        $data = self::obtenerDatosGerminacion($nombreCultivo);

        $predicciones = [];

        // Iterar sobre cada línea
        foreach ($data as $line => [$samples, $targets]) {
            // Validar que los datos sean correctos, que haya al menos 2 ciclos y que los targets no contengan valores nulos
            if (count($samples) > 1 && count($samples) == count($targets)) {
                // Filtrar los valores nulos tanto en samples como en targets
                $validSamples = array_filter($samples, function ($ciclo, $index) use ($targets) {
                    // Asegurarse de que tanto el ciclo (sample) como el gramaje (target) no sean nulos
                    return $ciclo !== null && $targets[$index] !== null;
                }, ARRAY_FILTER_USE_BOTH);

                $validTargets = array_filter($targets, function ($target) {
                    return $target !== null;
                });

                // Si después de filtrar quedan suficientes datos, proceder
                if (count($validSamples) > 1 && count($validSamples) == count($validTargets)) {
                    // Crear una nueva instancia de la regresión por mínimos cuadrados
                    $regression = new LeastSquares();

                    // Convertir las muestras a formato adecuado (matriz 2D)
                    $validSamples = array_map(function ($ciclo) {
                        return [$ciclo]; // Cada ciclo es una muestra individual
                    }, $validSamples);

                    // Entrenar el modelo con las muestras y los targets (gramajes)
                    $regression->train($validSamples, $validTargets);

                    // Predecir el gramaje para el ciclo 5
                    $prediction = $regression->predict([[5]]); // Ciclo 5 como entrada

                    // Guardar la predicción en el array
                    $predicciones[$line] = round($prediction[0], 2);
                } else {
                    // Si no hay suficientes datos válidos, se asigna un valor nulo
                    $predicciones[$line] = null;
                }
            } else {
                // Si los datos no son válidos, se asigna un valor nulo
                $predicciones[$line] = null;
            }
        }

        return $predicciones;
    }

    public static function obtenerConsumoTotalAgua($nombreCultivo, $tabla)
    {
        // Construcción de la consulta SQL
        $query = "
        SELECT 
            cs.descripcion AS descripcionCiclo, 
            cs.fechaInicio AS inicioCiclo, 
            cs.ciclo AS numeroCiclo, 
            cultivo.nombreCultivo AS NombreSembrado, 
            SUM($tabla.volumen) AS totalLitrosConsumidos
        FROM 
            $tabla
        INNER JOIN 
            cultivo ON $tabla.cultivoId = cultivo.cultivoId
        INNER JOIN 
            ciclosiembra cs ON cultivo.cicloId = cs.cicloId
        WHERE 
            cultivo.nombreCultivo = :nombreCultivo
        GROUP BY 
            cs.descripcion, cs.fechaInicio, cs.ciclo, cultivo.nombreCultivo
        ORDER BY 
            cs.fechaInicio ASC;
        ";

        try {
            // Ejecutar la consulta con el parámetro
            $resultados = Yii::$app->db->createCommand($query, [
                ':nombreCultivo' => $nombreCultivo,
            ])->queryAll();

            // Procesar los resultados
            $data = [];
            foreach ($resultados as $row) {
                $linea = $row['NombreSembrado'];  // El nombre del cultivo como "Línea X"
                $numeroCiclo = (int)$row['numeroCiclo']; // Número del ciclo
                $totalLitrosConsumidos = (float)$row['totalLitrosConsumidos']; // Litros consumidos
                $descripcionCiclo = $row['descripcionCiclo']; // Descripción del ciclo

                // Inicializar la línea si no existe en el array $data
                if (!isset($data[$linea])) {
                    $data[$linea] = [
                        [],  // Ciclos
                        [],  // Litros consumidos
                        []   // Descripciones
                    ];
                }

                // Agregar los datos en el array correspondiente
                $data[$linea][0][] = $numeroCiclo;            // Ciclo
                $data[$linea][1][] = $totalLitrosConsumidos;  // Litros consumidos
                $data[$linea][2][] = $descripcionCiclo;       // Descripción del ciclo
            }

            // Devolver los datos organizados
            return $data;
        } catch (\Exception $e) {
            // Manejo de errores
            Yii::error("Error al obtener datos de consumo de agua: " . $e->getMessage(), __METHOD__);
            return []; // Retornar un arreglo vacío en caso de error
        }
    }


    public static function predecirConsumoAgua($nombreCultivo, $tabla)
    {
        $data = self::obtenerConsumoTotalAgua($nombreCultivo, $tabla);

        $predicciones = [];

        // Iterar sobre cada línea
        foreach ($data as $line => [$samples, $targets]) {
            // Validar que los datos sean correctos, que haya al menos 2 ciclos y que los targets no contengan valores nulos
            if (count($samples) > 1 && count($samples) == count($targets)) {
                // Filtrar los valores nulos tanto en samples como en targets
                $validSamples = array_filter($samples, function ($ciclo, $index) use ($targets) {
                    // Asegurarse de que tanto el ciclo (sample) como el gramaje (target) no sean nulos
                    return $ciclo !== null && $targets[$index] !== null;
                }, ARRAY_FILTER_USE_BOTH);

                $validTargets = array_filter($targets, function ($target) {
                    return $target !== null;
                });

                // Si después de filtrar quedan suficientes datos, proceder
                if (count($validSamples) > 1 && count($validSamples) == count($validTargets)) {
                    // Crear una nueva instancia de la regresión por mínimos cuadrados
                    $regression = new LeastSquares();

                    // Convertir las muestras a formato adecuado (matriz 2D)
                    $validSamples = array_map(function ($ciclo) {
                        return [$ciclo]; // Cada ciclo es una muestra individual
                    }, $validSamples);

                    // Entrenar el modelo con las muestras y los targets (gramajes)
                    $regression->train($validSamples, $validTargets);

                    // Predecir el gramaje para el ciclo 5
                    $prediction = $regression->predict([[5]]); // Ciclo 5 como entrada

                    // Guardar la predicción en el array
                    $predicciones[$line] = round($prediction[0], 2);
                } else {
                    // Si no hay suficientes datos válidos, se asigna un valor nulo
                    $predicciones[$line] = null;
                }
            } else {
                // Si los datos no son válidos, se asigna un valor nulo
                $predicciones[$line] = null;
            }
        }

        return $predicciones;
    }







    /*public static function obtenerDatosPorCama($camaId, $segundoParametro)
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
    }*/

    /*public static function predecirPesoLineas($camaId, $segundoParametro)
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
    }*/





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

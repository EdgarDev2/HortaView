<?php

namespace frontend\controllers;

use frontend\models\Cama1;
use frontend\models\Cama2;
use frontend\models\Cama3;
use frontend\models\Cama4;
//use frontend\models\CicloSiembra;
use common\components\DbHandler;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * frontend/views/predicciones/
 * **/

class FiltrarHumedadPorRangoController extends Controller
{
    public function behaviors()
    {
        return [
            // Filtro de control de acceso
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index'], // Acciones a restringir
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // '@' solo usuarios autenticados tienen acceso
                    ],
                ],
            ],
            // Filtro de control de verbos HTTP
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'], // La acción 'delete' solo puede ser accedida mediante POST
                ],
            ],
        ];
    }

    protected function obtenerDatosHumedad($modelClass, $fechaInicio, $fechaFin)
    {
        // Consulta para obtener los datos agrupados por fecha y hora
        $data = $modelClass::find()
            ->select([
                'DATE(fecha) as fecha',   // Extrae la fecha
                'HOUR(hora) as hora',    // Extrae la hora
                'AVG(humedad) as avg_humedad', // Promedio de humedad por hora y día
                'MAX(humedad) as max_humedad', // Máximo por hora y día
                'MIN(humedad) as min_humedad', // Mínimo por hora y día
            ])
            ->where(['between', 'fecha', $fechaInicio, $fechaFin]) // Filtra por rango de fechas
            ->groupBy(['fecha', 'HOUR(hora)']) // Agrupa por fecha y hora
            ->orderBy(['fecha' => SORT_ASC, 'hora' => SORT_ASC]) // Ordena por fecha y hora
            ->asArray()
            ->all();

        // Inicializa resultados intermedios
        $resultadosPorHora = [];
        foreach (range(0, 23) as $hora) {
            $resultadosPorHora[$hora] = [
                'max_humedad' => [],
                'min_humedad' => [],
                'avg_humedad' => []
            ];
        }

        // Organiza los datos por hora
        foreach ($data as $entry) {
            $hora = (int)$entry['hora'];
            $resultadosPorHora[$hora]['max_humedad'][] = (float)$entry['max_humedad'];
            $resultadosPorHora[$hora]['min_humedad'][] = (float)$entry['min_humedad'];
            $resultadosPorHora[$hora]['avg_humedad'][] = (float)$entry['avg_humedad'];
        }

        // Calcula promedios globales por hora
        $resultadosFinales = [
            'promedios_maximos' => array_fill(0, 24, null),
            'promedios_minimos' => array_fill(0, 24, null),
            'promedios_humedad' => array_fill(0, 24, null),
        ];

        foreach ($resultadosPorHora as $hora => $valores) {
            if (!empty($valores['max_humedad'])) {
                $resultadosFinales['promedios_maximos'][$hora] = array_sum($valores['max_humedad']) / count($valores['max_humedad']);
            }
            if (!empty($valores['min_humedad'])) {
                $resultadosFinales['promedios_minimos'][$hora] = array_sum($valores['min_humedad']) / count($valores['min_humedad']);
            }
            if (!empty($valores['avg_humedad'])) {
                $resultadosFinales['promedios_humedad'][$hora] = array_sum($valores['avg_humedad']) / count($valores['avg_humedad']);
            }
        }

        return $resultadosFinales;
    }



    public function actionIndex()
    {
        $resultados = DbHandler::obtenerCicloYFechas();
        $cicloSeleccionado = $resultados['cicloSeleccionado'];
        $fechaInicio = $resultados['fechaInicio'];
        $fechaFinal = $resultados['fechaFinal'];

        return $this->render('index', [
            'cicloSeleccionado' => $cicloSeleccionado,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFinal,
        ]);
    }

    public function actionAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $fechaInicio = Yii::$app->request->post('fechaInicio');
        $fechaFin = Yii::$app->request->post('fechaFin');
        $camaId = Yii::$app->request->post('camaId');

        // Determinar el modelo según el camaId
        $modelClass = match ($camaId) {
            '1' => Cama1::class,
            '2' => Cama2::class,
            '3' => Cama3::class,
            '4' => Cama4::class,
            default => null,
        };

        // Validar parámetros
        if ($modelClass === null || !strtotime($fechaInicio) || !strtotime($fechaFin)) {
            return [
                'success' => false,
                'message' => 'Datos inválidos. Asegúrese de que todos los parámetros sean correctos.',
            ];
        }

        try {
            // Obtener los datos de humedad
            $resultados = $this->obtenerDatosHumedad($modelClass, $fechaInicio, $fechaFin);

            return [
                'success' => true,
                'promedios' => $resultados['promedios_humedad'], // Promedios generales por hora
                'promedios_maximos' => $resultados['promedios_maximos'], // Promedios de máximos por hora
                'promedios_minimos' => $resultados['promedios_minimos'], // Promedios de mínimos por hora
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage(),
            ];
        }
    }
}

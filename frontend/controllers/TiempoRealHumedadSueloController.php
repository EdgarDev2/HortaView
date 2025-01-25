<?php

namespace frontend\controllers;

use common\components\DbHandler;
//use frontend\models\CicloSiembra;
use frontend\models\Cama1;
use frontend\models\Cama2;
use frontend\models\Cama3;
use frontend\models\Cama4;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * frontend/views/tiempo-real/
 * **/

class TiempoRealHumedadSueloController extends Controller
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

        $fecha = Yii::$app->request->post('fecha'); // Recibe el parámetro de fecha desde el frontend

        try {
            // Obtener los datos de las cuatro camas, filtrando por fecha si está presente
            $resultados = $this->obtenerDatos($fecha);
            return [
                'success' => true,
                'data' => $resultados,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage(),
            ];
        }
    }

    protected function obtenerDatos($fecha = null)
    {
        $tablas = [Cama1::class, Cama2::class, Cama3::class, Cama4::class];
        $resultados = [];

        foreach ($tablas as $modelo) {
            // Obtener el nombre de la clase sin el namespace
            $nombreTabla = basename(str_replace("\\", "/", $modelo));

            // Definir el nombre de la columna 'id' correspondiente a cada tabla
            $columnaId = 'id' . $nombreTabla;

            // Obtener los datos de cada tabla usando su modelo correspondiente
            if (class_exists($modelo)) {
                $datos = $modelo::find()
                    ->select([$columnaId, 'humedad', 'fecha', 'hora']) // Usar la columna de ID correspondiente
                    ->andFilterWhere(['fecha' => $fecha]) // Aplica el filtro por fecha si se proporciona
                    ->orderBy(['fecha' => SORT_ASC, 'hora' => SORT_ASC])
                    ->asArray()
                    ->all();
                // Agregar los resultados al arreglo, etiquetados por la tabla
                $resultados[$nombreTabla] = $datos;
            } else {
                throw new \Exception("Modelo no encontrado: " . $modelo);
            }
        }

        return $resultados;
    }
}

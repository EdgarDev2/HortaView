<?php

namespace frontend\controllers;

//use frontend\models\Cama1;
//use frontend\models\Cama2;
//use frontend\models\Cama3;
//use frontend\models\Cama4;
//use frontend\models\CicloSiembra;
use common\components\DbHandler;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * frontend/views/graficas/
 * **/

class FiltrarHumedadPorDiaController extends Controller
{
    public function behaviors()
    {
        return [
            // Filtro de control de acceso
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'solicitud'], // Incluye 'solicitud' para aplicar las reglas
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Solo usuarios autenticados pueden acceder
                    ],
                ],
            ],
            // Filtro de control de verbos HTTP
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'], // La acción 'delete' solo puede ser accedida mediante POST
                    'solicitud' => ['POST'], // Asegura que 'solicitud' solo acepte solicitudes POST
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

    public function actionObtenerDatos()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $fecha = Yii::$app->request->post('fechaInicio');
        $camaId = Yii::$app->request->post('camaId');

        $tablaCama = match ($camaId) {
            '1' => 'cama1',
            '2' => 'cama2',
            '3' => 'cama3',
            '4' => 'cama4',
            default => null,
        };

        if ($tablaCama === null || !strtotime($fecha)) {
            return ['success' => false, 'message' => 'Datos inválidos'];
        }

        try {
            $resultados = \common\components\DbHandler::obtenerMetricasHumedad($tablaCama, $fecha);
            return [
                'success' => true,
                'promedios' => $resultados['promedios'],
                'maximos' => $resultados['maximos'],
                'minimos' => $resultados['minimos'],
            ];
        } catch (\Exception $e) {
            Yii::error("Error en actionSolicitud: " . $e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => 'Error al procesar la solicitud'];
        }
    }
}

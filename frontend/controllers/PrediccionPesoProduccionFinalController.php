<?php

namespace frontend\controllers;

use common\components\DbHandler;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

class PrediccionPesoProduccionFinalController extends Controller
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
        DbHandler::obtenerCicloYFechas();
        return $this->render('index');
    }

    public function actionFiltrar()
    {
        if (Yii::$app->request->isAjax) {
            $camaId = Yii::$app->request->post('camaId');
            // Verificar que el camaId no esté vacío
            if ($camaId) {
                $datosHistoricos = DbHandler::obtenerDatosPorCama($camaId);
                $predicciones = DbHandler::predecirPesoLineas($camaId);
                // Devolver los datos históricos y las predicciones en formato JSON
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return [
                    'success' => true,
                    'datos_historicos' => $datosHistoricos,
                    'predicciones' => $predicciones,
                ];
            }
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'success' => false,
            'message' => 'Cama no seleccionada.',
        ];
    }
    //Ejemplo de lo que retorna JSON:
    /*
        {
            "success": true,
            "datos_historicos": {
                "Línea 1": [[1, 2, 3, 4], [234, 783, 652, 410], ["Ciclo 1 febrero - abril", "Ciclo 2 junio - julio", "Ciclo 3 septiembre - nov", "Ciclo actual de siembra"]],
                "Línea 2": [[1, 2, 3, 4], [100, 250, 500, 600], ["Ciclo 1 febrero - abril", "Ciclo 2 junio - julio", "Ciclo 3 septiembre - nov", "Ciclo actual de siembra"]],
                "Línea 3": [[1, 2], [150, 180], ["Ciclo 1 febrero - abril", "Ciclo 2 junio - julio"]],
                "Línea 4": [[1, 2, 3, 4], [300, 400, 500, 600], ["Ciclo 1 febrero - abril", "Ciclo 2 junio - julio", "Ciclo 3 septiembre - nov", "Ciclo actual de siembra"]]
            },
            "predicciones": {
                "Línea 1": 315.65,
                "Línea 2": 525.10,
                "Línea 3": null,
                "Línea 4": 650.20
            }
        }

     */
}

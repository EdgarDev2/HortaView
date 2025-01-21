<?php

namespace frontend\controllers;

use common\components\DbHandler;
use frontend\controllers\ComunController;
//use frontend\models\CicloSiembra;
use frontend\models\Cultivo;
use frontend\models\LineaCultivo;
use frontend\models\RegistroGerminacion;
use frontend\models\Temperatura;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;
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

    /*public function actionIndex()
    {
        DbHandler::obtenerCicloYFechas();

        //$datos = DbHandler::obtenerDatosConPrediccion();
        //return $this->render('index', ['datos' => $datos]);
    }*/
    public function actionIndex()
    {
        // Pasar los datos a la vista
        return $this->render('index');
    }

    public function actionFiltrar()
    {
        if (Yii::$app->request->isAjax) {
            // Obtener el ID de la cama desde la solicitud
            $camaId = Yii::$app->request->post('camaId');

            // Verificar que el camaId no esté vacío
            if ($camaId) {
                // Obtener el tipo de riego asociado a la cama
                $tipoRiego = $this->obtenerTipoRiegoPorCama($camaId);

                // Si no se encuentra el tipo de riego, devolver un error
                if ($tipoRiego === null) {
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return [
                        'success' => false,
                        'message' => 'Cama no reconocida.',
                    ];
                }

                // Obtener los datos históricos
                $datosHistoricos = DbHandler::obtenerDatosPorCama($camaId, $tipoRiego);

                // Obtener las predicciones de peso para las líneas
                $predicciones = DbHandler::predecirPesoLineas($camaId, $tipoRiego);
                // Devolver los datos históricos y las predicciones en formato JSON
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return [
                    'success' => true,
                    'datos_historicos' => $datosHistoricos,
                    'predicciones' => $predicciones, // Aquí retornamos las predicciones organizadas por línea
                ];
            }
        }

        // Respuesta en caso de error
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'success' => false,
            'message' => 'Cama no seleccionada.',
        ];
    }

    private function obtenerTipoRiegoPorCama($camaId)
    {
        // Asegurarse de que los nombres de las camas coincidan con los valores del frontend
        $tiposRiego = [
            'Cama 1 cilantro automático' => 'Por goteo',
            'Cama 2 rábano automático' => 'Por aspersores',
            'Cama 3 cilantro manual' => 'Por goteo',
            'Cama 4 rábano manual' => '', // Cadena vacía
        ];

        // Retorna el tipo de riego si existe la cama, si no retorna null
        return $tiposRiego[$camaId] ?? null;
    }
}

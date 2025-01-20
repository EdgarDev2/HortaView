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
            $camaId = Yii::$app->request->post('camaId');
            if ($camaId) {
                // Obtener el segundo parámetro según la cama
                $tipoRiego = $this->obtenerTipoRiegoPorCama($camaId);
                if ($tipoRiego === null) {
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return [
                        'success' => false,
                        'message' => 'Cama no reconocida.',
                    ];
                }

                // Obtener los datos filtrados
                $datosHistoricos = DbHandler::obtenerDatosPorCama($camaId, $tipoRiego);

                // Devolver los datos en formato JSON
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return [
                    'success' => true,
                    'datos_historicos' => $datosHistoricos,
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
        $tiposRiego = [
            'Cama 1 cilantro automático' => 'Por goteo',
            'Cama 2 rábano automático' => 'Por aspersores',
            'Cama 3 cilantro manual' => 'Por goteo',
            'Cama 4 rábano manual' => '', // Cadena vacía
        ];

        return $tiposRiego[$camaId] ?? null;
    }
}

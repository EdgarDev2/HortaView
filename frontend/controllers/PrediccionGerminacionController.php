<?php

namespace frontend\controllers;

use common\components\DbHandler;
use yii;
//use frontend\models\CicloSiembra;
use frontend\models\Cultivo;
use frontend\models\RegistroGerminacion;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;


class PrediccionGerminacionController extends Controller
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
                $datosHistoricos = DbHandler::obtenerDatosGerminacion($camaId);
                $predicciones = DbHandler::predecirPorcentajeDeGerminacionByLineas($camaId);
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
}

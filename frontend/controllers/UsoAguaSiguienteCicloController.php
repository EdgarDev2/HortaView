<?php

namespace frontend\controllers;

use common\components\DbHandler;
//use frontend\models\CicloSiembra;
use frontend\models\RiegoManual;
use frontend\models\Valvula;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;
use Phpml\Regression\LinearRegression;
use Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;

class UsoAguaSiguienteCicloController extends Controller
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
        // $cicloSeleccionado = $resultados['cicloSeleccionado'];
        $fechaInicio = $resultados['fechaInicio'];
        $fechaFinal = $resultados['fechaFinal'];

        /* return $this->render('index', [
            'cicloSeleccionado' => $cicloSeleccionado,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFinal,
        ]);*/

        // Obtener los volúmenes de las válvulas
        $volumenesValvula = Valvula::find()
            ->select('volumen')
            ->where(['between', 'fechaEncendido', $fechaInicio, $fechaFinal])
            ->orWhere(['between', 'fechaApagado', $fechaInicio, $fechaFinal])
            ->column();

        // Obtener los volúmenes del riego manual
        $volumenesRiegoManual = RiegoManual::find()
            ->select('volumen')
            ->where(['between', 'fechaEncendido', $fechaInicio, $fechaFinal])
            ->orWhere(['between', 'fechaApagado', $fechaInicio, $fechaFinal])
            ->column();

        // Verifica que haya datos
        if (empty($volumenesValvula) || empty($volumenesRiegoManual)) {
            Yii::$app->session->setFlash('error', 'No se encontraron datos suficientes para realizar la predicción.');
            //return $this->render('index');
        }

        // Preprocesamiento: Creamos las características y las etiquetas (volúmenes)
        $dias = range(1, count($volumenesValvula)); // Los días (eje X)

        // Dataset para válvula
        $datasetValvula = [];
        foreach ($dias as $dia) {
            $datasetValvula[] = [$dia];  // Características (día)
        }

        // Dataset para riego manual
        $datasetRiegoManual = [];
        foreach ($dias as $dia) {
            $datasetRiegoManual[] = [$dia];  // Características (día)
        }

        // Crear el modelo de regresión SVR con kernel radial (RBF)
        $svrValvula = new SVR(Kernel::RBF, 1000, 0.1);  // C=1000, epsilon=0.1
        $svrRiegoManual = new SVR(Kernel::RBF, 1000, 0.1);  // C=1000, epsilon=0.1

        // Entrenar el modelo con los datos de válvula
        $svrValvula->train($datasetValvula, $volumenesValvula);

        // Entrenar el modelo con los datos de riego manual
        $svrRiegoManual->train($datasetRiegoManual, $volumenesRiegoManual);

        // Predecir los volúmenes para los próximos 33 días
        $prediccionesValvula = [];
        $prediccionesRiegoManual = [];
        // Generar los días futuros para los próximos 33 días
        $diasFuturos = range(1, 33); // Días 1 a 33


        foreach ($diasFuturos as $diaFuturo) {
            $prediccionesValvula[] = $svrValvula->predict([$diaFuturo]);
            $prediccionesRiegoManual[] = $svrRiegoManual->predict([$diaFuturo]);
        }

        // Pasar las predicciones y los días a la vista
        return $this->render('index', [
            'prediccionVolumenValvula' => $prediccionesValvula,
            'prediccionVolumenRiegoManual' => $prediccionesRiegoManual,
            'dias' => $diasFuturos, // Pasamos los días futuros
        ]);
    }
}

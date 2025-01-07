<?php

namespace frontend\controllers;

use frontend\models\CicloSiembra;
use frontend\models\Cultivo;
use frontend\models\LineaCultivo;
use frontend\models\RegistroGerminacion;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;
use Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;

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
        // Obtener los ciclos disponibles
        $ciclos = CicloSiembra::find()
            ->select(['cicloId', 'descripcion', 'ciclo'])
            ->orderBy(['ciclo' => SORT_ASC])
            ->asArray()
            ->all();

        if (empty($ciclos)) {
            Yii::$app->session->setFlash('error', 'No hay ciclos disponibles en este momento.');
        }

        Yii::$app->view->params['ciclos'] = $ciclos;
        $cicloSeleccionado = Yii::$app->session->get('cicloSeleccionado');
        $ciclo = CicloSiembra::findOne($cicloSeleccionado);

        date_default_timezone_set('America/Mexico_City');
        $fechaActual = date('Y-m-d');

        // Establecer las fechas de inicio y fin del ciclo
        if ($ciclo) {
            $fechaInicio = $ciclo->fechaInicio;
            $fechaFinal = $ciclo->fechaFin;
        } else {
            $fechaInicio = $fechaActual;
            $fechaFinal = $fechaActual;
        }

        // Obtener cultivos asociados al ciclo seleccionado
        $cultivos = Cultivo::find()
            ->select(['cultivoId', 'nombreCultivo', 'germinacion'])
            ->where(['cicloId' => $cicloSeleccionado])
            ->orderBy(['nombreCultivo' => SORT_ASC])
            ->asArray()
            ->all();

        // Obtener los datos de lineacultivo
        $lineasCultivo = LineaCultivo::find()
            ->select(['numeroLinea', 'gramaje', 'cultivoId'])
            ->where(['cultivoId' => array_column($cultivos, 'cultivoId')])
            ->orderBy(['numeroLinea' => SORT_ASC])
            ->asArray()
            ->all();

        // Obtener registros de germinación
        $registros = RegistroGerminacion::find()
            ->select(['numeroZurcosGerminados', 'broteAlturaMaxima', 'linea', 'cultivoId', 'fechaRegistro'])
            ->where(['cultivoId' => array_column($cultivos, 'cultivoId')])
            ->orderBy(['fechaRegistro' => SORT_ASC])
            ->asArray()
            ->all();

        // Agrupar las líneas de cultivo por camaId
        $lineasPorCama = [];
        foreach ($lineasCultivo as $linea) {
            $lineasPorCama[$linea['cultivoId']][] = $linea;
        }

        // Generar predicciones con SVR
        $prediccionesGramaje = $this->prediccionGramajeSVR($lineasCultivo, $registros);

        return $this->render('index', [
            'cicloSeleccionado' => $cicloSeleccionado,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFinal,
            'cultivos' => $cultivos,
            'lineasPorCultivo' => $lineasCultivo, // Pasar datos de lineacultivo
            'prediccionesGramaje' => $prediccionesGramaje,
            'lineasPorCama' => $lineasPorCama,  // Agrupado por cama
        ]);
    }


    private function prediccionGramajeSVR($lineasCultivo, $registros)
    {
        // Agrupar registros por línea y cultivoId
        $registrosAgrupados = [];
        foreach ($registros as $registro) {
            $linea = $registro['linea'];
            $cultivoId = $registro['cultivoId'];
            $clave = $cultivoId . '-' . $linea; // Clave única por cultivo y línea
            if (!isset($registrosAgrupados[$clave])) {
                $registrosAgrupados[$clave] = [];
            }
            $registrosAgrupados[$clave][] = $registro;
        }

        $predicciones = [];
        foreach ($lineasCultivo as $lineaCultivo) {
            $linea = $lineaCultivo['numeroLinea'];
            $gramajeReal = $lineaCultivo['gramaje'];
            $cultivoId = $lineaCultivo['cultivoId']; // Asegúrate de tener cultivoId
            $clave = $cultivoId . '-' . $linea;

            if (!isset($registrosAgrupados[$clave])) {
                // Si no hay registros para esta línea y cultivo, saltar
                continue;
            }

            // Preparar datos de entrenamiento
            $samples = [];
            $targets = [];
            foreach ($registrosAgrupados[$clave] as $registro) {
                $samples[] = [
                    $registro['numeroZurcosGerminados'],
                    $registro['broteAlturaMaxima']
                ];
                $targets[] = $gramajeReal;
            }

            // Crear y entrenar el modelo SVR
            $regression = new SVR(Kernel::RBF); // Cambiar kernel según sea necesario
            $regression->train($samples, $targets);

            // Realizar predicción para la línea actual
            $promedioZurcos = array_sum(array_column($registrosAgrupados[$clave], 'numeroZurcosGerminados')) / count($registrosAgrupados[$clave]);
            $promedioAltura = array_sum(array_column($registrosAgrupados[$clave], 'broteAlturaMaxima')) / count($registrosAgrupados[$clave]);

            $sampleGeneral = [$promedioZurcos, $promedioAltura];
            $prediccion = $regression->predict($sampleGeneral);

            // Guardar resultado con cultivoId
            $predicciones[] = [
                'cultivoId' => $cultivoId, // Asociar la predicción con el cultivoId
                'linea' => $linea,
                'prediccion' => round($prediccion, 2), // Redondear a 2 decimales
                'gramaje_real' => $gramajeReal
            ];
        }

        return $predicciones;
    }
}

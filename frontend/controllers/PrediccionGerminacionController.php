<?php

namespace frontend\controllers;

use frontend\models\CicloSiembra;
use frontend\models\Cultivo;
use frontend\models\RegistroGerminacion;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;
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
        $fechaInicio = date('Y-m-d', strtotime($fechaInicio));
        $fechaFinal = date('Y-m-d', strtotime($fechaFinal));

        // Obtener los cultivos asociados al ciclo seleccionado
        $cultivos = Cultivo::find()
            ->select(['cultivoId', 'nombreCultivo'])
            ->where(['cicloId' => $cicloSeleccionado])
            ->orderBy(['nombreCultivo' => SORT_ASC])
            ->asArray()
            ->all();

        // Obtener los registros de germinación para los cultivos seleccionados
        $registros = RegistroGerminacion::find()
            ->select(['numeroZurcosGerminados', 'broteAlturaMaxima', 'linea', 'cultivoId', 'fechaRegistro'])
            ->where(['cultivoId' => array_column($cultivos, 'cultivoId')])
            ->andWhere(['>=', 'fechaRegistro', $fechaInicio])
            ->andWhere(['<=', 'fechaRegistro', $fechaFinal])
            ->orderBy(['fechaRegistro' => SORT_ASC])
            ->asArray()
            ->all();

        // Organizar los registros por cultivoId para fácil acceso en la vista
        $registrosPorCultivo = [];
        foreach ($registros as $registro) {
            $cultivoId = $registro['cultivoId'];
            if (!isset($registrosPorCultivo[$cultivoId])) {
                $registrosPorCultivo[$cultivoId] = [];
            }
            $registrosPorCultivo[$cultivoId][] = $registro;
        }

        // Realizar predicciones usando regresión SVR por línea
        $totalSurcosPosibles = 7; // Establecer el número total de surcos posibles
        $prediccionesPorCultivo = [];
        foreach ($registrosPorCultivo as $cultivoId => $registros) {
            $prediccionesPorCultivo[$cultivoId] = $this->regresionSVRPorLinea($registros, $totalSurcosPosibles);
        }

        // Obtener datos históricos de altura para cada cultivo
        $datosHistoricos = [];
        foreach ($registrosPorCultivo as $cultivoId => $registros) {
            foreach ($registros as $registro) {
                $linea = $registro['linea'];
                if (!isset($datosHistoricos[$cultivoId])) {
                    $datosHistoricos[$cultivoId] = [];
                }
                if (!isset($datosHistoricos[$cultivoId][$linea])) {
                    $datosHistoricos[$cultivoId][$linea] = [
                        'alturas' => [],
                        'fechas' => [],
                        'surcosGerminados' => 0,  // Contador de surcos germinados
                        'totalSurcos' => 0,        // Total de surcos en la línea
                    ];
                }

                // Agregar la altura máxima del brote y la fecha
                $datosHistoricos[$cultivoId][$linea]['alturas'][] = $registro['broteAlturaMaxima'];
                $datosHistoricos[$cultivoId][$linea]['fechas'][] = $registro['fechaRegistro'];

                // Contabilizar si el surco está germinado (suponiendo que 'broteAlturaMaxima' mayor a 0 indica germinación)
                if ($registro['broteAlturaMaxima'] > 0) {
                    $datosHistoricos[$cultivoId][$linea]['surcosGerminados']++;
                }

                // Contabilizar el total de surcos (esto puede depender de la estructura de los datos, por ejemplo, si 'totalSurcos' es un dato fijo por línea)
                $datosHistoricos[$cultivoId][$linea]['totalSurcos']++;
            }
        }

        // Calcular el promedio de surcos germinados por línea
        foreach ($datosHistoricos as $cultivoId => &$lineas) {
            foreach ($lineas as $linea => &$data) {
                if ($data['totalSurcos'] > 0) {
                    // Calcular el promedio de surcos germinados
                    $data['promedioSurcosGerminados'] = $data['surcosGerminados'] / $data['totalSurcos'];
                } else {
                    $data['promedioSurcosGerminados'] = 0;  // Si no hay surcos, el promedio es 0
                }
            }
        }


        return $this->render('index', [
            'cicloSeleccionado' => $cicloSeleccionado,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFinal,
            'cultivos' => $cultivos,
            'registrosPorCultivo' => $registrosPorCultivo,
            'prediccionesPorCultivo' => $prediccionesPorCultivo,
            'datosHistoricos' => $datosHistoricos, // Pasar los datos históricos a la vista
        ]);
    }



    /**
     * Método para realizar regresión lineal múltiple.
     * @param array $registros
     * @return array Predicciones por línea.
     */

    private function regresionSVRPorLinea($registros, $totalSurcosPosibles)
    {
        $registrosAgrupados = [];
        foreach ($registros as $registro) {
            $linea = $registro['linea'];
            if (!isset($registrosAgrupados[$linea])) {
                $registrosAgrupados[$linea] = [];
            }
            $registrosAgrupados[$linea][] = $registro;
        }

        $predicciones = [];
        foreach ($registrosAgrupados as $linea => $registrosDeLinea) {
            $samples = [];
            $targets = [];
            foreach ($registrosDeLinea as $registro) {
                $samples[] = [
                    $registro['broteAlturaMaxima'],
                    $registro['linea']
                ];
                $targets[] = ($registro['numeroZurcosGerminados'] / $totalSurcosPosibles) * 100;
            }

            $regression = new SVR(Kernel::RBF, $degree = 3, $epsilon = 0.1, $cost = 1.0, $gamma = null);
            $regression->train($samples, $targets);

            // Predicción para la línea en general: usa la media de las alturas y la línea
            $promedioAltura = array_sum(array_column($registrosDeLinea, 'broteAlturaMaxima')) / count($registrosDeLinea);
            $sampleGeneral = [$promedioAltura, $linea];
            $prediccion = $regression->predict($sampleGeneral);

            $predicciones[] = [
                'linea' => $linea,
                'prediccion' => round($prediccion, 2) // Redondear al 2º decimal
            ];
        }

        return $predicciones;
    }
}

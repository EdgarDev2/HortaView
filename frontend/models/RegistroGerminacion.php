<?php

namespace frontend\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "registroGerminacion".
 *
 * @property string $registroGerminacionId
 * @property double $temperaturaAmbiente
 * @property double $humedadAmbiente
 * @property int $numeroZurcosGerminados
 * @property double $broteAlturaMaxima
 * @property double $broteAlturaMinima
 * @property int $numeroMortandad
 * @property string $observaciones
 * @property double $hojasAlturaMinima
 * @property double $hojasAlturaMaxima
 * @property int $linea
 * @property string $fechaRegistro
 * @property string $cultivoId
 */
class RegistroGerminacion extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'registroGerminacion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['registroGerminacionId', 'cultivoId'], 'required'],
            [['temperaturaAmbiente', 'humedadAmbiente', 'broteAlturaMaxima', 'broteAlturaMinima', 'hojasAlturaMinima', 'hojasAlturaMaxima'], 'number'],
            [['numeroZurcosGerminados', 'numeroMortandad', 'linea'], 'integer'],
            [['fechaRegistro'], 'safe'],
            [['registroGerminacionId', 'cultivoId'], 'string', 'max' => 255],
            [['observaciones'], 'string', 'max' => 255],
            [['registroGerminacionId'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'registroGerminacionId' => 'ID Registro Germinación',
            'temperaturaAmbiente' => 'Temperatura Ambiente',
            'humedadAmbiente' => 'Humedad Ambiente',
            'numeroZurcosGerminados' => 'Número de Surcos Germinados',
            'broteAlturaMaxima' => 'Altura Máxima del Brote',
            'broteAlturaMinima' => 'Altura Mínima del Brote',
            'numeroMortandad' => 'Número de Mortalidad',
            'observaciones' => 'Observaciones',
            'hojasAlturaMinima' => 'Altura Mínima de las Hojas',
            'hojasAlturaMaxima' => 'Altura Máxima de las Hojas',
            'linea' => 'Línea',
            'fechaRegistro' => 'Fecha de Registro',
            'cultivoId' => 'ID Cultivo',
        ];
    }
}

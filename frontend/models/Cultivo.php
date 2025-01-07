<?php

namespace frontend\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "cultivo".
 *
 * @property string $cultivoId
 * @property string $nombreCultivo
 * @property int $germinacion
 * @property string $fechaSiembra
 * @property string $fechaCosecha
 * @property string $tipoRiego
 * @property double $gramaje
 * @property double $alturaMaxima
 * @property double $alturaMinima
 * @property int $temperaturaAmbienteMaxima
 * @property int $temperaturaAmbienteMinima
 * @property int $humedadAmbienteMaxima
 * @property int $humedadAmbienteMinima
 * @property int $humedadMinimaTierra
 * @property int $presionBarometricaMaxima
 * @property int $presionBarometricaMinima
 * @property string $cicloId
 * @property int $humedadMaximaTierra
 * @property string $descripcion
 */
class Cultivo extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cultivo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cultivoId', 'nombreCultivo', 'tipoRiego', 'cicloId', 'descripcion'], 'required'],
            [['germinacion', 'temperaturaAmbienteMaxima', 'temperaturaAmbienteMinima', 'humedadAmbienteMaxima', 'humedadAmbienteMinima', 'humedadMinimaTierra', 'presionBarometricaMaxima', 'presionBarometricaMinima', 'humedadMaximaTierra'], 'integer'],
            [['fechaSiembra', 'fechaCosecha'], 'safe'],
            [['gramaje', 'alturaMaxima', 'alturaMinima'], 'number'],
            [['cultivoId', 'cicloId'], 'string', 'max' => 255],
            [['nombreCultivo'], 'string', 'max' => 100],
            [['tipoRiego'], 'string', 'max' => 50],
            [['descripcion'], 'string', 'max' => 255],
            [['cultivoId'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cultivoId' => 'ID Cultivo',
            'nombreCultivo' => 'Nombre del Cultivo',
            'germinacion' => 'Germinación (%)',
            'fechaSiembra' => 'Fecha de Siembra',
            'fechaCosecha' => 'Fecha de Cosecha',
            'tipoRiego' => 'Tipo de Riego',
            'gramaje' => 'Gramaje',
            'alturaMaxima' => 'Altura Máxima',
            'alturaMinima' => 'Altura Mínima',
            'temperaturaAmbienteMaxima' => 'Temperatura Ambiente Máxima',
            'temperaturaAmbienteMinima' => 'Temperatura Ambiente Mínima',
            'humedadAmbienteMaxima' => 'Humedad Ambiente Máxima',
            'humedadAmbienteMinima' => 'Humedad Ambiente Mínima',
            'humedadMinimaTierra' => 'Humedad Mínima del Suelo',
            'presionBarometricaMaxima' => 'Presión Barométrica Máxima',
            'presionBarometricaMinima' => 'Presión Barométrica Mínima',
            'cicloId' => 'ID del Ciclo',
            'humedadMaximaTierra' => 'Humedad Máxima del Suelo',
            'descripcion' => 'Descripción',
        ];
    }
}

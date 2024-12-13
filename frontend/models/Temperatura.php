<?php

namespace frontend\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "temperatura".
 *
 * @property string $idTemperatura
 * @property float $temperatura
 * @property float $humedad
 * @property string $fecha
 * @property string $hora
 */
class Temperatura extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temperatura'; // Nombre de la tabla
    }

    /**
     * {@inheritdoc}
     */
    public static function primaryKey()
    {
        return ['idTemperatura']; // Clave primaria
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idTemperatura', 'temperatura', 'humedad', 'fecha', 'hora'], 'required'],
            [['temperatura', 'humedad'], 'number'],
            [['fecha'], 'date', 'format' => 'php:Y-m-d'], // Validación de formato de fecha
            [['hora'], 'time', 'format' => 'php:H:i:s'], // Validación de formato de hora
            [['idTemperatura'], 'string', 'max' => 255], // Validación del tamaño de cadena
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idTemperatura' => 'ID Temperatura',
            'temperatura' => 'Temperatura',
            'humedad' => 'Humedad',
            'fecha' => 'Fecha',
            'hora' => 'Hora',
        ];
    }
}

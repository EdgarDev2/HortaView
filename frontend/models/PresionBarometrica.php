<?php

namespace frontend\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "presion_barometrica".
 *
 * @property string $idPresionBarometrica
 * @property float|null $presion
 * @property float|null $temperatura
 * @property float|null $altitud
 * @property string|null $fecha
 * @property string|null $hora
 */
class PresionBarometrica extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'presionbarometrica'; // Cambiar si el nombre de la tabla es diferente
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idPresionBarometrica'], 'required'],
            [['presion', 'temperatura', 'altitud'], 'number'],
            [['fecha'], 'date', 'format' => 'php:Y-m-d'],
            [['hora'], 'time', 'format' => 'php:H:i:s'],
            [['idPresionBarometrica'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idPresionBarometrica' => 'ID Presión Barométrica',
            'presion' => 'Presión',
            'temperatura' => 'Temperatura',
            'altitud' => 'Altitud',
            'fecha' => 'Fecha',
            'hora' => 'Hora',
        ];
    }
}

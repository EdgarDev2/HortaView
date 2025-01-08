<?php

namespace frontend\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "valvula".
 *
 * @property string $idValvula
 * @property string|null $fechaEncendido
 * @property string|null $fechaApagado
 * @property float|null $volumen
 * @property string|null $cultivoId
 */
class Valvula extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'valvula';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idValvula'], 'required'],
            [['fechaEncendido', 'fechaApagado'], 'safe'],
            [['volumen'], 'number'],
            [['idValvula', 'cultivoId'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idValvula' => 'ID Válvula',
            'fechaEncendido' => 'Fecha Encendido',
            'fechaApagado' => 'Fecha Apagado',
            'volumen' => 'Volumen',
            'cultivoId' => 'Cultivo ID',
        ];
    }
}

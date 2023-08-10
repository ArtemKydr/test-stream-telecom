<?php

namespace app\models;

use yii\db\ActiveRecord;

class WriteOffReasons extends ActiveRecord
{
    public function rules()
    {
        return [
            [['reason'], 'required'],
        ];
    }
}

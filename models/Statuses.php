<?php

namespace app\models;

use yii\db\ActiveRecord;

class Statuses extends ActiveRecord
{
    public function rules()
    {
        return [
            [['name'], 'required'],
        ];
    }
}

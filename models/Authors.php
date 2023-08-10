<?php

namespace app\models;

use yii\db\ActiveRecord;

class Authors extends ActiveRecord
{
    public function rules()
    {
        return [
            [['name'], 'required'],
        ];
    }
}

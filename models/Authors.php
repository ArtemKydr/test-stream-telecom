<?php

namespace app\models;

use yii\db\ActiveRecord;

class Authors extends ActiveRecord
{
    public function rules()
    {
        return [
            [['first_name', 'last_name'], 'required'],
        ];
    }
}

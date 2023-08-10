<?php

namespace app\models;

use yii\db\ActiveRecord;

class Readers extends ActiveRecord
{
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'email',], 'required'],
            ['email', 'email',   'message' => 'Email должен иметь вид example@example.com'],
        ];
    }
}

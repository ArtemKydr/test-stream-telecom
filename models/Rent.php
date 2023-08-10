<?php

namespace app\models;

use yii\db\ActiveRecord;

class Rent extends ActiveRecord
{
    public function rules()
    {
        return [
            [['book_id', 'reader_id'], 'required'],
        ];
    }
}

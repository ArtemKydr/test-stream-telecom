<?php

namespace app\models;

use yii\db\ActiveRecord;

class WriteOffs extends ActiveRecord
{
    public function rules()
    {
        return [
            [['reason_id'], 'required'],
        ];
    }

    public function getReason()
    {
        return $this->hasOne(WriteOffReasons::class, ['id' => 'reason_id']);
    }
}

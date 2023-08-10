<?php

namespace app\models;

use yii\db\ActiveRecord;

class Books extends ActiveRecord
{
    public function rules()
    {
        return [
            [['title','author_id','status_id'], 'required'],
        ];
    }

    public function getAuthor()
    {
        return $this->hasOne(Authors::class, ['id' => 'author_id']);
    }

    public function getStatus()
    {
        return $this->hasOne(Statuses::class, ['id' => 'status_id']);
    }

    public function getReader()
    {
        return $this->hasOne(Authors::class, ['id' => 'reader_id']);
    }
}

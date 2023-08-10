<?php

namespace app\controllers\api\v1;

use app\models\Readers;
use app\models\Rent;
use Yii;


//RentApiController:
//
//actionIssueBook()
//WriteOffApiController:


class RentApiController extends CommonApiController
{
    public function actionIssueBook()
    {
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();

            if ($this->checkExistingBook($postData)) {
                $readerModel = new Readers();
                $this->setReader($readerModel, $postData);
                $this->IssueBookForReader();
                return [
                    'status' => 200,
                    'message' => 'Book successfully issued',
                    'error' => null,
                ];
            } else {
                return [
                    'status' => 400,
                    'message' => "The book is not in the library/in stock",
                ];
            }
        } else {
            return [
                'status' => 400,
                'message' => 'Try POST request'
            ];
        }
    }


    protected function IssueBookForReader()
    {
        $model = new Rent();
        $model->setAttributes([
            'book_id'=>$this->existingBookForReader->id,
            'reader_id'=>$this->readerForSet->id,
        ]);
        $model->save();

        $this->updateBookForReader();
        $this->existingBookForReader->status_id = 2;
        $this->existingBookForReader->rent_id = $model->id;
        if ($this->existingBookForReader->save() && $this->updateBookForReader()) {
            return true;
        } else {
            return false;
        }
    }

}

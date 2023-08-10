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

            if ($this->checkExistingBook($postData['author'],$postData['title'])) {
                $readerModel = new Readers();
                $this->setReader($readerModel, $postData['reader']);
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
                    'errors' => $this->getErrors(),
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
        $updatingRent = $model->save();

        $updatingBookStatus = $this->updateBookForReader(
            $this->existingBookForReader,
            [
            'status_id'=>$this->defaultBookIssuedStatusId,
            'rent_id'=>$model->id
            ]);
        if ($updatingBookStatus && $updatingRent) {
            return true;
        } else {
            $this->addError('Failed to save author: ' . json_encode($model->errors));
            return false;
        }
    }

}

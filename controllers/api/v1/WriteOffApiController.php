<?php

namespace app\controllers\api\v1;

use app\controllers\MainController;
use app\models\Authors;
use app\models\Books;
use app\models\Readers;
use app\models\WriteOffReasons;
use app\models\WriteOffs;
use Yii;
use yii\web\Response;


//WriteOffApiController:
//
//actionWriteOffBook()
class WriteOffApiController extends CommonApiController
{
    public function actionWriteOffBook()
    {
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();

            if ($this->checkExistingBook($postData['author'],$postData['title'],false)) {
                $this->WriteOffBook($postData);
                return [
                    'status' => 200,
                    'message' => 'Book successfully written off',
                    'error'=>null
                ];
            } else {
                return [
                    'status' => 400,
                    'message' => "The book is not written off",
                    'error'=>$this->getErrors()
                ];
            }
        } else {
            return [
                'status' => 400,
                'message' => 'Try POST request'
            ];
        }
    }

    protected function WriteOffBook($data)
    {
        if ($this->existingBookForReader) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $this->existingBookForReader->status_id = $this->defaultBookWrittenOffStatusId;

                $modelWriteOffReasons = $this->checkExistingWriteOffReason($data['reason']);

                $modelWriteOff = new WriteOffs();
                $modelWriteOff->reason_id = $modelWriteOffReasons;
                $modelWriteOff->book_id = $this->existingBookForReader->id;

                if ($modelWriteOffReasons && $modelWriteOff->save() && $this->existingBookForReader->save()) {
                    $transaction->commit();
                    return true;
                } else {
                    $transaction->rollBack();
                    return false;
                }
            } catch (\yii\db\Exception $e) {
                $transaction->rollBack();
                $this->addError('Failed to save author: ' . json_encode($e));

                return false;
            }
        } else {
            return false; // Книги нет для списания
        }
    }

    protected function checkExistingWriteOffReason($reason)
    {
        $existingModel = WriteOffReasons::findOne(['reason' => $reason]);

        if ($existingModel) {
            return $existingModel->id;
        } else {
            $model = new WriteOffReasons();
            $model->reason = $reason;

            if ($model->save()) {
                return $model->id;
            } else {
                $this->addError('Failed to save author: ' . json_encode($model->errors));
                return null; // Ошибка при сохранении причины списания
            }
        }
    }

}

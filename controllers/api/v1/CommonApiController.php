<?php


namespace app\controllers\api\v1;

use app\controllers\MainController;
use app\models\Authors;
use app\models\Books;
use app\models\Readers;
use app\models\WriteOffReasons;
use app\models\WriteOffs;
use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class CommonApiController extends MainController
{
    public $authorsForSetBooks = [];
    public $readerForSet;

    public $errors = [];
    public $existingBookForReader;
    public $defaultBookAvailableStatusId = 1;
    public $defaultBookIssuedStatusId = 2;
    public $defaultBookWrittenOffStatusId = 3;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
        ];

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['POST', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        return $behaviors;
    }


    protected function setBooks($title, $authors)
    {
        try {
            foreach ($authors as $author) {
                if (!$this->setBook($title, $author)) {
                    return false;
                }
            }
            return true;
        } catch (\yii\db\Exception $e) {
            $this->addError('Failed to save author: ' . json_encode($e));
            }
    }

    protected function setBook($title, $author)
    {
        $model = new Books();
        $model->title = $title;
        $model->author_id = $author->id;
        $model->status_id = $this->defaultBookAvailableStatusId;
        if ($model->save()) {
            return true;
        } else {
            $this->addError('Failed to save author: ' . json_encode($model->errors));
            return false;
        }
    }

    protected function setAuthors($authors)
    {
        $uniqueAuthors = [];

        foreach ($authors as $author) {
            $fullName = $author["first_name"] . " " . $author["last_name"];
            $uniqueAuthors[$fullName] = $author;
        }

        $uniqueAuthors = array_values($uniqueAuthors);

        try{
            foreach ($uniqueAuthors as $author){
                if(!$this->setAuthor($author)){
                    return false;
                }
            }
            return true;
        } catch (\yii\db\Exception $e) {
            $this->addError('Failed to save author: ' . json_encode($e));
        }
    }

    protected function setAuthor($author)
    {
        $model = new Authors();
        $existingAuthor = Authors::findOne(['first_name' => $author['first_name'], 'last_name' => $author['last_name']]);

        if (!$existingAuthor) {
            $model->setAttributes([
                'first_name' => trim($author['first_name']),
                'last_name' => trim($author['last_name'])
            ]);
            if ($model->save()) {
                array_push($this->authorsForSetBooks, $model);
                return true;
            } else {
                $this->addError('Failed to save author: ' . json_encode($model->errors));
                return false;
            }
        }else{
            array_push($this->authorsForSetBooks, $existingAuthor);
            return true;
        }
    }

    protected function checkExistingBook($author, $title, $includeStatus = true)
    {
        $authorId = Authors::find()->select('id')->where([
            'first_name' => $author['first_name'],
            'last_name' => $author['last_name']
            ])->scalar();

        $conditions = [
            'title' => $title,
            'author_id' => $authorId,
        ];

        if ($includeStatus) {
            $conditions['status_id'] = $this->defaultBookAvailableStatusId;
        }

        $existingBook = Books::findOne($conditions);

        if (!$existingBook) {
            return false;
        } else {
            $this->existingBookForReader = $existingBook;
            return true;
        }
    }

    protected function setReader($model, $reader)
    {
        $existingReader = Readers::findOne([
            'email' => $reader['email'],
        ]);

        if (!$existingReader) {
            $model->setAttributes($reader);


            if ($model->save()) {
                $this->readerForSet = $model;
                return true;
            } else {
                $this->addError('Failed to save author: ' . json_encode($model->errors));
                return false;
            }
        } else {
            $this->readerForSet = $existingReader;
            return true;
        }
    }

    protected function WriteOffBook($data)
    {
        if ($this->existingBookForReader) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $this->existingBookForReader->status_id = 3;

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
                return null; // Ошибка при сохранении причины списания
            }
        }
    }

    protected function updateBookForReader($model, $params)
    {
        $model->setAttributes($params);
        return $model->save();
    }

    protected function addError($error)
    {
        $this->errors[] = $error;
    }

    protected function getErrors()
    {
        return $this->errors;
    }


}

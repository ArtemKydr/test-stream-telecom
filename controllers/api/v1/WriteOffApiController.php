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
    public $authorIdForSet;
    public $readerIdForSet;
    public $existingBookForReader;
    public $defaultBookAvailableStatusId = 1;

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

    public function actionGetAllBooks()
    {
        return Books::find()
            ->select(['books.id', 'books.title', 'authors.name AS author_name', 'statuses.name AS status_name'])
            ->joinWith(['author', 'status'])
            ->distinct(['books.id', 'author_id'])
            ->all();
    }

    public function actionWriteOffBook()
    {
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();

            if ($this->checkExistingBook($postData, false)) {
                $this->WriteOffBook($postData);
                return [
                    'status' => 200,
                    'message' => 'Book successfully written off',
                ];
            } else {
                return [
                    'status' => 400,
                    'message' => "The book is not written off",
                ];
            }
        } else {
            return [
                'status' => 400,
                'message' => 'Try POST request'
            ];
        }
    }

    public function actionIssueBook()
    {
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();
            $readerModel = new Readers();

            if ($this->checkExistingBook($postData)) {
                $this->setReader($readerModel, $postData['reader']);
                $this->IssueBookForReader();
                return [
                    'status' => 200,
                    'message' => 'Book successfully issued',
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

    public function actionSetBookWithAuthors()
    {
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();
            $authorModel = new Authors();
            $bookModel = new Books();

            if ($this->setAuthor($authorModel, $postData['author']) && $this->setBook($bookModel, $postData['title'], $this->authorIdForSet)) {
                return [
                    'status' => 200,
                    'message' => 'Data successfully saved',
                    'authorErrors' => null,
                    'bookErrors' => null
                ];
            } else {
                return [
                    'status' => 400,
                    'message' => 'Data saving failed',
                    'authorErrors' => $authorModel->getErrors(),
                    'bookErrors' => $bookModel->getErrors()
                ];
            }
        } else {
            return [
                'status' => 400,
                'message' => 'Try POST request'
            ];
        }
    }

    protected function setBook($model, $title, $authorId)
    {
        $model->title = $title;
        $model->author_id = $authorId;
        $model->status_id = $this->defaultBookAvailableStatusId;

        return $model->save();
    }

    protected function setAuthor($model, $author)
    {
        $existingAuthor = Authors::findOne(['name' => $author]);

        if (!$existingAuthor) {
            $model->name = $author;

            if ($model->save()) {
                $this->authorIdForSet = $model->id;
                return true;
            } else {
                return false;
            }
        } else {
            $this->authorIdForSet = $existingAuthor->id;
            return true;
        }
    }

    protected function checkExistingBook($data, $includeStatus = true)
    {
        $authorId = Authors::find()->select('id')->where(['name' => $data['author']])->scalar();

        $conditions = [
            'title' => $data['title'],
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

    protected function IssueBookForReader()
    {
        $model = $this->existingBookForReader;
        $model->status_id = 2;
        $model->reader_id = $this->readerIdForSet;
        if ($model->save()) {
            return true;
        } else {
            return false;
        }
    }

    protected function setReader($model, $reader)
    {
        $existingReader = Readers::findOne(['name' => $reader]);

        if (!$existingReader) {
            $model->name = $reader;

            if ($model->save()) {
                $this->readerIdForSet = $model->id;
                return true;
            } else {
                return false;
            }
        } else {
            $this->readerIdForSet = $existingReader->id;
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



}

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

// TODO Разделение на контроллеры может быть гибким и зависит от конкретных потребностей вашего приложения. Однако, вот пример возможной структуры:
//
//BooksApiController: Основной контроллер для работы с книгами. Методы для получения списка книг и связанных данных могут остаться здесь.
//
//RentApiController: Контроллер для операций по выдаче книг читателям. Содержит методы для выдачи книг и связанные операции.
//
//WriteOffApiController: Контроллер для операций списания книг. Включает методы для списания книг и связанных действий.
//
//AuthorsApiController: Контроллер для операций с авторами. Включает методы для добавления и управления авторами.
//
//CommonApiController: Этот контроллер может содержать общие методы, которые могут быть полезны в разных частях приложения, например, методы для работы с читателями.
//
//Такая структура позволит разделить функциональность на более мелкие и понятные блоки, сократит зависимости между разными операциями и облегчит расширение в будущем.
//
//Примеры методов для каждого из контроллеров:
//
//BooksApiController:
//
//actionGetAllBooks()
//actionSetBookWithAuthors()
//RentApiController:
//
//actionIssueBook()
//WriteOffApiController:
//
//actionWriteOffBook()
//AuthorsApiController:
//
//Методы для управления авторами (создание, редактирование, удаление и т.д.)
//CommonApiController:
//
//Методы для работы с читателями (создание, редактирование, удаление и т.д.)
//Эта структура, конечно, предложение, и вы можете адаптировать ее под требования вашего приложения. Важно помнить о принципе единственной ответственности, чтобы каждый контроллер отвечал за определенные аспекты функциональности.
//TODO ДОБАВИТЬ ТАБЛИЦУ НАЗВАНИЙ

class CommonApiController extends MainController
{
    public $authorForSet;
    public $readerForSet;
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
                $this->authorForSet = $model;
                return true;
            } else {
                return false;
            }
        } else {
            $this->authorForSet = $existingAuthor;
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

    protected function setReader($model, $reader)
    {
        $existingReader = Readers::findOne([
            'first_name' => $reader['first_name'],
            'last_name' => $reader['last_name'],
            'email' => $reader['email'],
        ]);

        if (!$existingReader) {
            $model->setAttributes($reader);

            if ($model->save()) {
                $this->readerForSet = $model->id;
                return true;
            } else {
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

    protected function updateBookForReader($model, $data)
    {

    }


}

<?php

namespace app\controllers\api\v1;

use app\models\Authors;
use app\models\Books;
use Yii;

//
//BooksApiController:
//
//actionGetAllBooks()
//actionSetBookWithAuthors()


class BooksApiController extends CommonApiController
{
    public function actionGetAllBooks()
    {
        $books = Books::find()
            ->select(['books.id', 'title', 'authors.name AS authors', 'statuses.name AS status'])
            ->leftJoin('authors','author_id = authors.id')
            ->leftJoin('statuses','status_id = statuses.id')
            ->distinct(['id', 'author_id'])
            ->asArray()
            ->all();
        return $books;
    }

    public function actionSetBookWithAuthors()
    {
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();
            $authorModel = new Authors();
            $bookModel = new Books();

            if ($this->setAuthor($authorModel, $postData['author']) && $this->setBook($bookModel, $postData['title'], $this->authorForSet->id)) {
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



}

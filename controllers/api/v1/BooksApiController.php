<?php

namespace app\controllers\api\v1;


use app\models\Books;
use Yii;

class BooksApiController extends CommonApiController
{
    public function actionGetAllBooks()
    {
        if (Yii::$app->request->isGet) {
            $books = Books::find()
                ->select(['books.id', 'title', "CONCAT(authors.last_name,' ',authors.first_name) AS author", 'statuses.name AS status'])
                ->leftJoin('authors', 'author_id = authors.id')
                ->leftJoin('statuses', 'status_id = statuses.id')
                ->distinct(['id', 'author_id'])
                ->asArray()
                ->all();
            if ($books) {
                return [
                    'status' => 200,
                    'data' => $books
                ];
            } else {
                return [
                    'status' => 400,
                    'data' => 'No books in library :('
                ];
            }
        }
    }

    public function actionSetBookWithAuthors(){
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();
            if ($this->setAuthors($postData['authors']) && $this->setBooks($postData['title'], $this->authorsForSetBooks)) {
                return [
                    'status' => 200,
                    'message' => 'Data successfully saved',
                    'errors' => null,
                ];
            } else {
                return [
                    'status' => 400,
                    'message' => 'Data saving failed',
                    'errors' => $this->getErrors(),
                ];
            }
        } else {
            return [
                'status' => 400,
                'message' => 'Try POST request',
            ];
        }
    }




}

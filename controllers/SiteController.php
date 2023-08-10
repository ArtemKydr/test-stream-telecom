<?php

namespace app\controllers;

use app\models\Books;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class SiteController extends MainController
{
    public function actionIndex()
    {
        return $this->render('index');
    }


}

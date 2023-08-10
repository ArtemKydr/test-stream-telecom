<?php

namespace app\controllers;

use yii\web\Controller;

class LibraryInterfaceController extends MainController
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}

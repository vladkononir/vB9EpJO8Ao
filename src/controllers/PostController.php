<?php

namespace app\controllers;

use app\services\PostService;
use Yii;
use app\models\Post;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PostController extends Controller
{
    private PostService $postService;

    public function __construct($id, $module, PostService $postService, $config = [])
    {
        $this->postService = $postService;
        parent::__construct($id, $module, $config);
    }

    public function actionEdit($id)
    {
        $model = $this->postService->findModel($id);
        $model->scenario = 'update';

        if (!$model->canEdit()) {
            Yii::$app->session->setFlash('error',
                'Редактирование сообщения доступно только в течение 12 часов после публикации.'
            );
            return $this->redirect(['site/index']);
        }

        if ($model->load(Yii::$app->request->post())) {
            Yii::info('Данные формы: ' . print_r($model->attributes, true));
            Yii::info('Ошибки валидации: ' . print_r($model->errors, true));

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Сообщение успешно отредактировано.');
                return $this->redirect(['site/index']);
            } else {
                Yii::error('Ошибка сохранения: ' . print_r($model->errors, true));
                Yii::$app->session->setFlash('error', 'Произошла ошибка при редактировании сообщения.');
            }
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->postService->findModel($id);

        if (!$model->canDelete()) {
            Yii::$app->session->setFlash('error',
                'Удаление сообщения доступно только в течение 14 дней после публикации.'
            );
            return $this->redirect(['site/index']);
        }

        if (Yii::$app->request->isPost) {
            if ($model->softDelete()) {
                Yii::$app->session->setFlash('success', 'Сообщение успешно удалено.');
                return $this->redirect(['site/index']);
            } else {
                Yii::$app->session->setFlash('error', 'Произошла ошибка при удалении сообщения.');
            }
        }

        return $this->render('delete', [
            'model' => $model,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->postService->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }
}

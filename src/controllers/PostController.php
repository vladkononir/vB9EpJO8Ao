<?php

namespace app\controllers;

use app\services\PostService;
use Yii;
use app\models\Post;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PostController extends Controller
{
    const FLASH_ERROR_EDIT_EXPIRED = 'Редактирование сообщения доступно только в течение 12 часов после публикации.';
    const FLASH_ERROR_DELETE_EXPIRED = 'Удаление сообщения доступно только в течение 14 дней после публикации.';
    const FLASH_SUCCESS_EDIT = 'Сообщение успешно отредактировано.';
    const FLASH_ERROR_EDIT = 'Произошла ошибка при редактировании сообщения.';
    const FLASH_SUCCESS_DELETE = 'Сообщение успешно удалено.';
    const FLASH_ERROR_DELETE = 'Произошла ошибка при удалении сообщения.';
    const ERROR_POST_NOT_FOUND = 'Запрашиваемое сообщение не найдено.';

    const LOG_FORM_DATA = 'Данные формы: %s';
    const LOG_VALIDATION_ERRORS = 'Ошибки валидации: %s';
    const LOG_SAVE_ERRORS = 'Ошибка сохранения: %s';

    public function actionEdit(int $id): string|Response
    {
        $model = Post::findOne($id)
            ?? throw new NotFoundHttpException(self::ERROR_POST_NOT_FOUND);

        $model->scenario = Post::SCENARIO_UPDATE;

        if (!$model->canEdit()) {
            Yii::$app->session->setFlash('error', self::FLASH_ERROR_EDIT_EXPIRED);

            return $this->redirect(['site/index']);
        }

        if ($model->load(Yii::$app->request->post())) {
            Yii::info(sprintf(self::LOG_FORM_DATA, print_r($model->attributes, true)));
            Yii::info(sprintf(self::LOG_VALIDATION_ERRORS, print_r($model->errors, true)));

            if ($model->save()) {
                Yii::$app->session->setFlash('success', self::FLASH_SUCCESS_EDIT);

                return $this->redirect(['site/index']);
            } else {
                Yii::error(sprintf(self::LOG_SAVE_ERRORS, print_r($model->errors, true)));
                Yii::$app->session->setFlash('error', self::FLASH_ERROR_EDIT);
            }
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionDelete(int $id): string|Response
    {
        $model = Post::findOne($id)
            ?? throw new NotFoundHttpException(self::ERROR_POST_NOT_FOUND);

        if (!$model->canDelete()) {
            Yii::$app->session->setFlash('error', self::FLASH_ERROR_DELETE_EXPIRED);

            return $this->redirect(['site/index']);
        }

        if (Yii::$app->request->isPost) {
            if ($model->softDelete()) {
                Yii::$app->session->setFlash('success', self::FLASH_SUCCESS_DELETE);

                return $this->redirect(['site/index']);
            } else {
                Yii::$app->session->setFlash('error', self::FLASH_ERROR_DELETE);
            }
        }

        return $this->render('delete', [
            'model' => $model,
        ]);
    }

    public function actionView(int $id): string
    {
        $model = Post::findOne($id)
            ?? throw new NotFoundHttpException(self::ERROR_POST_NOT_FOUND);

        return $this->render('view', [
            'model' => $model,
        ]);
    }
}

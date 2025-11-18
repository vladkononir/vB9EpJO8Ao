<?php

namespace app\controllers;

use app\services\posts\PostAccessService;
use app\services\posts\PostDeletionService;
use app\services\posts\PostQueryService;
use app\services\posts\PostUpdateService;
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

    private PostQueryService $postQueryService;
    private PostUpdateService $postUpdateService;
    private PostDeletionService $postDeletionService;
    private PostAccessService $postAccessService;

    public function __construct(
        $id,
        $module,
        PostQueryService $postQueryService,
        PostUpdateService $postUpdateService,
        PostDeletionService $postDeletionService,
        PostAccessService $postAccessService,
        $config = []
    ) {
        $this->postQueryService = $postQueryService;
        $this->postUpdateService = $postUpdateService;
        $this->postDeletionService = $postDeletionService;
        $this->postAccessService = $postAccessService;

        parent::__construct($id, $module, $config);
    }

    public function actionEdit(int $id): string|Response
    {
        $post = $this->postQueryService->findPost($id);

        if (!$this->postAccessService->canEdit($post)) {
            Yii::$app->session->setFlash('error', self::FLASH_ERROR_EDIT_EXPIRED);

            return $this->redirect(['site/index']);
        }

        if ($post->load(Yii::$app->request->post())) {
            Yii::info(sprintf(self::LOG_FORM_DATA, print_r($post->attributes, true)));
            Yii::info(sprintf(self::LOG_VALIDATION_ERRORS, print_r($post->errors, true)));

            if ($this->postUpdateService->updatePost($post)) {
                Yii::$app->session->setFlash('success', self::FLASH_SUCCESS_EDIT);

                return $this->redirect(['site/index']);
            } else {
                Yii::error(sprintf(self::LOG_SAVE_ERRORS, print_r($post->errors, true)));
                Yii::$app->session->setFlash('error', self::FLASH_ERROR_EDIT);
            }
        }

        return $this->render('edit', [
            'model' => $post,
        ]);
    }

    public function actionDelete(int $id): string|Response
    {
        $post = $this->postQueryService->findPost($id);

        if (!$this->postAccessService->canDelete($post)) {
            Yii::$app->session->setFlash('error', self::FLASH_ERROR_DELETE_EXPIRED);

            return $this->redirect(['site/index']);
        }

        if (Yii::$app->request->isPost) {
            if ($this->postDeletionService->softDelete($post)) {
                Yii::$app->session->setFlash('success', self::FLASH_SUCCESS_DELETE);

                return $this->redirect(['site/index']);
            } else {
                Yii::$app->session->setFlash('error', self::FLASH_ERROR_DELETE);
            }
        }

        return $this->render('delete', [
            'model' => $post,
        ]);
    }

    public function actionView(int $id): string
    {
        $post = $this->postQueryService->findPost($id);

        return $this->render('view', [
            'model' => $post,
            'canEdit' => $this->postAccessService->canEdit($post),
            'canDelete' => $this->postAccessService->canDelete($post),
        ]);
    }
}

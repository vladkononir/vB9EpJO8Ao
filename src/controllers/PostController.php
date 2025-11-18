<?php

namespace app\controllers;

use app\services\posts\AccessChecker;
use app\services\posts\PostRemover;
use app\services\posts\PostFinder;
use app\services\posts\PostUpdater;
use app\services\PostService;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class PostController extends Controller
{
    const FLASH_ERROR_EDIT_EXPIRED = 'Редактирование сообщения доступно только в течение 12 часов после публикации.';
    const FLASH_ERROR_DELETE_EXPIRED = 'Удаление сообщения доступно только в течение 14 дней после публикации.';
    const FLASH_SUCCESS_EDIT = 'Сообщение успешно отредактировано.';
    const FLASH_ERROR_EDIT = 'Произошла ошибка при редактировании сообщения.';
    const FLASH_SUCCESS_DELETE = 'Сообщение успешно удалено.';
    const FLASH_ERROR_DELETE = 'Произошла ошибка при удалении сообщения.';

    const LOG_FORM_DATA = 'Данные формы: %s';
    const LOG_VALIDATION_ERRORS = 'Ошибки валидации: %s';
    const LOG_SAVE_ERRORS = 'Ошибка сохранения: %s';

    private PostFinder $postFinder;
    private PostUpdater $postUpdater;
    private PostRemover $postRemover;
    private AccessChecker $accessChecker;

    public function __construct(
        $id,
        $module,
        PostFinder $postFinder,
        PostUpdater $postUpdater,
        PostRemover $postRemover,
        AccessChecker $accessChecker,
        $config = []
    ) {
        $this->postFinder = $postFinder;
        $this->postUpdater = $postUpdater;
        $this->postRemover = $postRemover;
        $this->accessChecker = $accessChecker;

        parent::__construct($id, $module, $config);
    }

    public function actionEdit(int $id): string|Response
    {
        $post = $this->postFinder->findPost($id);

        if (!$this->accessChecker->canEdit($post)) {
            Yii::$app->session->setFlash('error', self::FLASH_ERROR_EDIT_EXPIRED);

            return $this->redirect(['site/index']);
        }

        if ($post->load(Yii::$app->request->post())) {
            Yii::info(sprintf(self::LOG_FORM_DATA, print_r($post->attributes, true)));
            Yii::info(sprintf(self::LOG_VALIDATION_ERRORS, print_r($post->errors, true)));

            if ($this->postUpdater->update($post)) {
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
        $post = $this->postFinder->findPost($id);

        if (!$this->accessChecker->canDelete($post)) {
            Yii::$app->session->setFlash('error', self::FLASH_ERROR_DELETE_EXPIRED);

            return $this->redirect(['site/index']);
        }

        if (Yii::$app->request->isPost) {
            if ($this->postRemover->softDelete($post)) {
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
        $post = $this->postFinder->findPost($id);

        return $this->render('view', [
            'model' => $post,
            'canEdit' => $this->accessChecker->canEdit($post),
            'canDelete' => $this->accessChecker->canDelete($post),
            'postsCount' => $this->postFinder->getPostNumberByIp($post),
        ]);
    }
}

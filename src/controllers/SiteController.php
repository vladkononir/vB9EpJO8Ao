<?php

namespace app\controllers;

use app\factories\PostDataProviderFactory;
use app\factories\PostFactory;
use app\services\EmailService;
use Yii;
use app\models\Post;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    const FLASH_SUCCESS_CREATE = 'Сообщение успешно опубликовано! На ваш email отправлены ссылки для управления.';
    const FLASH_ERROR_CREATE = 'Произошла ошибка при публикации сообщения. Проверьте правильность заполнения формы.';

    private EmailService $emailService;
    private PostFactory $postFactory;
    private PostDataProviderFactory $dataProviderFactory;

    public function __construct
    (
        $id,
        $module,
        EmailService $emailService,
        PostFactory $postFactory,
        PostDataProviderFactory $dataProviderFactory,
        $config = [],
    )
    {
        $this->emailService = $emailService;
        $this->postFactory = $postFactory;
        $this->dataProviderFactory = $dataProviderFactory;

        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex(): string|Response
    {
        $dataProvider = $this->dataProviderFactory->createPostsDataProvider();

        $post = $this->postFactory->createPost();

        if ($post->load(\Yii::$app->request->post())) {
            if ($post->save()) {
                $this->emailService->sendManagementEmail($post);

                Yii::$app->session->setFlash('success', self::FLASH_SUCCESS_CREATE);

                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', self::FLASH_ERROR_CREATE);
            }
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $post,
        ]);
    }
}

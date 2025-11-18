<?php

namespace app\controllers;

use app\factories\PostDataProviderFactory;
use app\factories\PostFactory;

use app\services\posts\PostCreationService;
use app\services\posts\PostQueryService;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    const FLASH_SUCCESS_CREATE = 'Сообщение успешно опубликовано! На ваш email отправлены ссылки для управления.';
    const FLASH_ERROR_CREATE = 'Произошла ошибка при публикации сообщения. Проверьте правильность заполнения формы.';

    private PostFactory $postFactory;
    private PostDataProviderFactory $dataProviderFactory;
    private PostCreationService $postCreationService;
    private PostQueryService $postQueryService;

    public function __construct(
        $id,
        $module,
        PostFactory $postFactory,
        PostDataProviderFactory $dataProviderFactory,
        PostCreationService $postCreationService,
        PostQueryService $postQueryService,
        $config = []
    ) {
        $this->postFactory = $postFactory;
        $this->dataProviderFactory = $dataProviderFactory;
        $this->postCreationService = $postCreationService;
        $this->postQueryService = $postQueryService;
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

        if ($post->load(Yii::$app->request->post())) {
            if ($this->postCreationService->createPost($post)) {
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

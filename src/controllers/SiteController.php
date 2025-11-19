<?php

namespace app\controllers;

use app\factories\FormFactory;
use app\factories\PostDataProviderFactory;
use app\factories\PostFactory;
use app\services\posts\PostCreator;
use app\services\posts\PostCounter;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    const FLASH_SUCCESS_CREATE = 'Сообщение успешно опубликовано! На ваш email отправлены ссылки для управления.';
    const FLASH_ERROR_CREATE = 'Произошла ошибка при публикации сообщения. Проверьте правильность заполнения формы.';

    private PostFactory $postFactory;
    private PostDataProviderFactory $dataProviderFactory;
    private PostCreator $postCreator;
    private PostCounter $postCounter;
    private FormFactory $formFactory;

    public function __construct(
        $id,
        $module,
        PostFactory $postFactory,
        PostDataProviderFactory $dataProviderFactory,
        PostCreator $postCreator,
        PostCounter $postCounter,
        FormFactory $formFactory,
        $config = []
    ) {
        $this->postFactory = $postFactory;
        $this->dataProviderFactory = $dataProviderFactory;
        $this->postCreator = $postCreator;
        $this->postCounter = $postCounter;
        $this->formFactory = $formFactory;

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
        $dataProvider = $this->dataProviderFactory->createFromRequest();
        $form = $this->formFactory->postCreateForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $post = $form->createPost();

            if ($this->postCreator->create($post)) {
                Yii::$app->session->setFlash('success', self::FLASH_SUCCESS_CREATE);

                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', self::FLASH_ERROR_CREATE);
            }
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $form,
            'postNumbers' => $this->postCounter->getPostNumbersBatch($dataProvider),
        ]);
    }
}

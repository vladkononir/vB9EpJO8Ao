<?php

namespace app\controllers;

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
    const PAGE_SIZE = 20;

    private EmailService $emailService;

    public function __construct($id, $module, EmailService $emailService, $config = [])
    {
        $this->emailService = $emailService;
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
        $dataProvider = new ActiveDataProvider([
            'query' => Post::find()
                ->where(['IS', 'deleted_at', null])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => self::PAGE_SIZE,
            ],
        ]);

        $model = new Post();
        $model->scenario = POST::SCENARIO_CREATE;

        if ($model->load(\Yii::$app->request->post())) {
            if ($model->save()) {
                $this->emailService->sendManagementEmail($model);

                Yii::$app->session->setFlash('success', self::FLASH_SUCCESS_CREATE);

                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', self::FLASH_ERROR_CREATE);
            }
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }
}

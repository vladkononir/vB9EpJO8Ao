<?php

namespace app\controllers;

use app\services\EmailService;
use Yii;
use app\models\Post;
use yii\data\ActiveDataProvider;
use yii\web\Controller;

class SiteController extends Controller
{
    private EmailService $emailService;

    public function __construct($id, $module, EmailService $emailService, $config = [])
    {
        $this->emailService = $emailService;
        parent::__construct($id, $module, $config);
    }
    /**
     * {@inheritdoc}
     */
    public function actions()
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

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Post::find()
                ->where(['IS', 'deleted_at', null])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $model = new Post();
        $model->scenario = 'create';

        if ($model->load(\Yii::$app->request->post())) {
            if ($model->save()) {
                $this->emailService->sendManagementEmail($model);

                Yii::$app->session->setFlash('success',
                    'Сообщение успешно опубликовано! На ваш email отправлены ссылки для управления.'
                );
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error',
                    'Произошла ошибка при публикации сообщения. Проверьте правильность заполнения формы.'
                );
            }
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }
}

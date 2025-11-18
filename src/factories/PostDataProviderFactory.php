<?php

namespace app\factories;

use app\models\Post;
use yii\data\ActiveDataProvider;
use Yii;

class PostDataProviderFactory
{
    const DEFAULT_PAGE_SIZE = 5;

    public function createFromRequest(int $pageSize = self::DEFAULT_PAGE_SIZE): ActiveDataProvider
    {
         $postDataProvider = new ActiveDataProvider([
             'query' => Post::find()
                 ->where(['IS', 'deleted_at', null])
                 ->orderBy(['created_at' => SORT_DESC]),
             'pagination' => [
                 'pageSize' => $pageSize,
             ],
         ]);

        $postDataProvider->pagination->setPage(Yii::$app->request->get('page', 1) - 1);

        return $postDataProvider;
    }
}

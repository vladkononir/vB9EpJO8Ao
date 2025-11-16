<?php

use yii\db\Migration;

class m251116_163551_add_token_and_indexes_to_posts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%posts}}', 'token', $this->string(32)->notNull()->after('id'));

        $this->createIndex('idx-posts-token', '{{%posts}}', 'token');
        $this->createIndex('idx-posts-ip_address', '{{%posts}}', 'ip_address');
        $this->createIndex('idx-posts-created_at', '{{%posts}}', 'created_at');
        $this->createIndex('idx-posts-deleted_at', '{{%posts}}', 'deleted_at');

        $posts = $this->db->createCommand('SELECT id FROM {{%posts}}')->queryAll();

        foreach ($posts as $post) {
            $token = Yii::$app->security->generateRandomString(32);
            $this->db->createCommand()->update('{{%posts}}',
                ['token' => $token],
                ['id' => $post['id']]
            )->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-posts-deleted_at', '{{%posts}}');
        $this->dropIndex('idx-posts-created_at', '{{%posts}}');
        $this->dropIndex('idx-posts-ip_address', '{{%posts}}');
        $this->dropIndex('idx-posts-token', '{{%posts}}');
        $this->dropColumn('{{%posts}}', 'token');
    }
}

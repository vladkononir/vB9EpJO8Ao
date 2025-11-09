<?php

use yii\db\Migration;

class m251108_161538_create_posts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%posts}}', [
            'id' => $this->primaryKey(),
            'author_name' => $this->string(15)->notNull(),
            'email' => $this->string()->notNull(),
            'message' => $this->string(1000)->notNull(),
            'ip_address' => $this->string(45)->notNull(),
            'created_at' => $this->bigInteger()->notNull(),
            'updated_at' => $this->bigInteger(),
            'deleted_at' => $this->bigInteger(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%posts}}');
    }
}

<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => sprintf(
        'mysql:host=%s;dbname=%s',
        getenv('DB_HOST') ?: 'mysql',
        getenv('DB_NAME') ?: 'storyvalut'
    ),
    'username' => getenv('DB_USER') ?: 'user',
    'password' => getenv('DB_PASSWORD') ?: 'password',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];

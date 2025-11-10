<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Управление сообщением - StoryValut</title>
</head>
<body>
<h2>Ваше сообщение успешно опубликовано.</h2>

<h3>Ссылки для управления:</h3>

<p>
    <strong>Редактировать сообщение:</strong><br>
    <a href="<?= $editLink ?>"><?= $editLink ?></a><br>
    Доступно до: <?= $editDeadline ?>
</p>

<p>
    <strong>Удалить сообщение:</strong><br>
    <a href="<?= $deleteLink ?>"><?= $deleteLink ?></a><br>
    Доступно до: <?= $deleteDeadline ?>
</p>
</body>
</html>
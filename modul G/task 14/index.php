<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Загрузка файлов</title>
</head>
<body>
    <h1>Загрузите файл</h1>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <input type="submit" value="Загрузить">
    </form>
</body>
</html>
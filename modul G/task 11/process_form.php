<?php
session_start();

if (isset($_POST['captcha'])) {
    $captcha = $_POST['captcha'];

    if (isset($_SESSION['captcha']) && strtoupper($captcha) == strtoupper($_SESSION['captcha'])) {
        echo "Captcha верна!";
        // Дополнительная обработка формы (например, сохранение данных в базу данных)
    } else {
        echo "Неверный код captcha. Попробуйте еще раз.";
    }

    // Очищаем сессионную переменную после проверки. Важно для безопасности и повторного использования формы
    unset($_SESSION['captcha']);

} else {
    echo "Пожалуйста, введите код captcha.";
}
?>

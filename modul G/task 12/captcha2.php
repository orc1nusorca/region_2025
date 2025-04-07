<?php
session_start();
$imageWidth = 200;
$imageHeight = 60;
$characters = 4;
$image = imagecreatetruecolor($imageWidth, $imageHeight);
$backgroundColor = imagecolorallocate($image, 255, 255, 255); // Белый
$textColor = imagecolorallocate($image, 0, 0, 0);             // Черный
$lineColor = imagecolorallocate($image, 192, 192, 192);       // Серый
$noiseColor = imagecolorallocate($image, 128, 128, 128);      // Темно-серый
imagefill($image, 0, 0, $backgroundColor);
$captchaCode = '';
$possibleCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
for ($i = 0; $i < $characters; $i++) {
    $captchaCode .= $possibleCharacters[rand(0, strlen($possibleCharacters) - 1)];
}
$_SESSION['captcha'] = $captchaCode;
for ($i = 0; $i < 3; $i++) {
    imageline($image, rand(0, $imageWidth), rand(0, $imageHeight), rand(0, $imageWidth), rand(0, $imageHeight), $lineColor);
}
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, $imageWidth), rand(0, $imageHeight), $noiseColor);
}
$font = dirname(__FILE__) . '/arial.ttf'; // Путь к шрифту
$fontSize = 20;
$xPositions = range(20, 140, 40); // Генерация позиций X для символов
foreach (str_split($captchaCode) as $i => $char) {
    imagettftext($image, $fontSize, rand(-15, 15), $xPositions[$i], rand($fontSize + 5, $imageHeight - 5), $textColor, $font, $char);
}
header('Content-type: image/png');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
imagepng($image);
imagedestroy($image);
?>
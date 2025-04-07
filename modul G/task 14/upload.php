<?php
$SORTED_DIRS = [
    'images' => ['jpg', 'jpeg', 'png', 'gif'],
    'videos' => ['mp4', 'avi', 'mov', 'mkv'],
    'documents' => ['docx', 'pdf', 'xls', 'xlsx'],
    'audio' => ['mp3', 'wav', 'ogg'],
    'fonts' => ['ttf', 'otf']
];
$UPLOAD_DIR = 'uploads';
if (!is_dir($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR);
}
function getFileType($extension) {
    global $SORTED_DIRS;
    foreach ($SORTED_DIRS as $folder => $extensions) {
        if (in_array(strtolower($extension), $extensions)) {
            return $folder;
        }
    }
    return null;
}
function renameFile($originalFile) {
    $timestamp = date('Y_m_d_H_i');
    $extension = pathinfo($originalFile, PATHINFO_EXTENSION);
    return "{$timestamp}.{$extension}";
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Ошибка загрузки файла.");
    }
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileType = getFileType($fileExtension);
    if ($fileType) {
        $targetDir = $UPLOAD_DIR . '/' . $fileType;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $newFileName = renameFile($file['name']);
        $targetFilePath = $targetDir . '/' . $newFileName;
        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            echo "Файл загружен и перемещен в {$targetFilePath}.";
        } else {
            echo "Ошибка при перемещении файла.";
        }
    } else {
        echo "Неизвестный тип файла: {$file['name']}.";
    }
} else {
    echo "Нет файла для загрузки.";
}
?>
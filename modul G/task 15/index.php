<?php

/**
 * Сравнивает два массива медиафайлов, прочитанных из текстовых файлов,
 * и возвращает новый массив, содержащий общие элементы.  Теперь сравниваются строки.
 *
 * @param string $file1_path Путь к первому текстовому файлу.
 * @param string $file2_path Путь ко второму текстовому файлу.
 *
 * @return array Новый массив, содержащий общие элементы из обоих файлов.
 */
function findCommonMediaObjectsFromFiles(string $file1_path, string $file2_path): array {
    $array1 = getMediaFilesFromFile($file1_path);
    $array2 = getMediaFilesFromFile($file2_path);

    $common = [];
    foreach ($array1 as $file1) {
        foreach ($array2 as $file2) {
            if (trim($file1) === trim($file2)) { // Сравнение строк с удалением пробелов в начале и конце
                $common[] = trim($file1);
                break; // Чтобы не добавлять один и тот же файл несколько раз
            }
        }
    }
    return array_values(array_unique($common)); // Удаляем дубликаты и переиндексируем
}

/**
 * Читает список медиафайлов из текстового файла.
 *
 * @param string $file_path Путь к текстовому файлу.
 *
 * @return array Массив строк, где каждая строка - имя файла.
 */
function getMediaFilesFromFile(string $file_path): array {
    $files = [];
    if (file_exists($file_path)) {
        $files = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    } else {
        echo "Ошибка: Файл не найден: " . htmlspecialchars($file_path) . "\n";
    }
    return $files;
}

// Пути к текстовым файлам. Замените на ваши пути.
$file1_path = 'media_files1.txt';
$file2_path = 'media_files2.txt';

// Находим общие элементы из файлов.
$commonFiles = findCommonMediaObjectsFromFiles($file1_path, $file2_path);

// Выводим общие элементы.
echo "Общие медиафайлы:\n";
if (empty($commonFiles)) {
    echo "Нет общих файлов.\n";
} else {
    foreach ($commonFiles as $file) {
        echo "- " . htmlspecialchars($file) . "\n"; // htmlspecialchars для безопасности
    }
}

?>

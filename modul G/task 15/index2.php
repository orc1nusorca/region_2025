<?php
function findCommonElements(array $array1, array $array2): array {
    return array_values(array_intersect($array1, $array2));
}
$array1 = ['image1.jpg', 'video1.mp4', 'document1.pdf', 'audio1.mp3'];
$array2 = ['video1.mp4', 'document2.pdf', 'image1.jpg', 'audio2.mp3'];
$commonElements = findCommonElements($array1, $array2);
print_r($commonElements);
?>
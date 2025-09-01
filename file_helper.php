<?php
function uploadFile($file, $uploadDir = 'uploads/') {
    if (empty($file['name'])) return false;

    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) return $fileName;
    return false;
}

function deleteFile($fileName, $uploadDir = 'uploads/') {
    $filePath = $uploadDir . $fileName;
    if ($fileName && file_exists($filePath)) return unlink($filePath);
    return true;
}
<?php
require '../includes/auth.php';
require_staff();
require '../includes/db_staff.php';

$stmt = $pdo->prepare('SELECT file_path, original_filename FROM consultation_files WHERE id = ?');
$stmt->execute([(int)($_GET['id'] ?? 0)]);
$file = $stmt->fetch();

$path = __DIR__ . '/../uploads/' . basename($file['file_path'] ?? '');
if (!$file || !is_file($path)) {
    http_response_code(404);
    exit('File not found.');
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file['original_filename']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
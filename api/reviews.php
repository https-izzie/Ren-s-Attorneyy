<?php
header('Content-Type: application/json');
require '../includes/db_public.php';

// ---- A client submits a review ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $rating   = (int)($_POST['rating'] ?? 0);
    $comment  = trim($_POST['comment'] ?? '');
    $caseInfo = trim($_POST['case_info'] ?? '');

    $errors = [];
    if ($name === '' || strlen($name) > 120)        $errors[] = 'Please enter your name.';
    if ($rating < 1 || $rating > 5)                 $errors[] = 'Please choose a rating from 1 to 5.';
    if ($comment === '' || strlen($comment) > 1000) $errors[] = 'Please write a short review (max 1000 characters).';
    if (strlen($caseInfo) > 120)                    $errors[] = 'Case type and state is too long.';

    if ($errors) {
        http_response_code(400);
        exit(json_encode(['errors' => $errors]));
    }

    $stmt = $pdo->prepare('INSERT INTO reviews (name, rating, comment, case_info) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $rating, $comment, $caseInfo ?: null]);

    exit(json_encode(['message' => 'Thank you! Your review will appear once approved.']));
}

// ---- Show only approved reviews ----
echo json_encode($pdo->query(
    'SELECT id, name, rating, comment, case_info, created_at FROM approved_reviews ORDER BY created_at DESC'
)->fetchAll());
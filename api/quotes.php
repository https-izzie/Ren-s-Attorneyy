<?php
header('Content-Type: application/json');
require '../includes/db_public.php';

echo json_encode($pdo->query('SELECT id, text, author FROM quotes')->fetchAll());
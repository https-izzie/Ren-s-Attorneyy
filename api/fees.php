<?php
header('Content-Type: application/json');
require '../includes/db_public.php';

$rows = $pdo->query(
    "SELECT setting_key, setting_value FROM settings
     WHERE setting_key IN ('consultation_fee_ngn', 'consultation_fee_usd')"
)->fetchAll(PDO::FETCH_KEY_PAIR);

echo json_encode([
    'ngn' => (int)($rows['consultation_fee_ngn'] ?? 0),
    'usd' => (int)($rows['consultation_fee_usd'] ?? 0),
]);
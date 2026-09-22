<?php
require 'config.php';
header('Content-Type: application/json');

$wardId = filter_input(INPUT_GET, 'ward_id', FILTER_VALIDATE_INT);

if (!$wardId) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare('SELECT id, name FROM villages WHERE ward_id = :id ORDER BY name');
$stmt->execute(['id' => $wardId]);

echo json_encode($stmt->fetchAll());
<?php
require 'config.php';
header('Content-Type: application/json');

$countryId = filter_input(INPUT_GET, 'country_id', FILTER_VALIDATE_INT);

if (!$countryId) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare('SELECT id, name FROM counties WHERE country_id = :id ORDER BY name');
$stmt->execute(['id' => $countryId]);

echo json_encode($stmt->fetchAll());
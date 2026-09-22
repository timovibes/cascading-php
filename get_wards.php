<?php
require 'config.php';
header('Content-Type: application/json');

$countyId = filter_input(INPUT_GET, 'county_id', FILTER_VALIDATE_INT);

if (!$countyId) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare('SELECT id, name FROM wards WHERE county_id = :id ORDER BY name');
$stmt->execute(['id' => $countyId]);

echo json_encode($stmt->fetchAll());
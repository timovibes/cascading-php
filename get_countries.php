<?php
require 'config.php';
header('Content-Type: application/json');

$continentId = filter_input(INPUT_GET, 'continent_id', FILTER_VALIDATE_INT);

if (!$continentId) {
    echo json_encode([]);
    exit;
}

// Prepared statement — never concatenate $_GET values into SQL directly.
$stmt = $pdo->prepare('SELECT id, name FROM countries WHERE continent_id = :id ORDER BY name');
$stmt->execute(['id' => $continentId]);

echo json_encode($stmt->fetchAll());
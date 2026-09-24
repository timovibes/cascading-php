<?php
require 'config.php';
header('Content-Type: application/json');

// Returns every village by plain name. Duplicate names across different
// wards will look identical in the dropdown, but each still carries its own
// id, so selecting one still resolves to the correct ward/county/country.
$stmt = $pdo->query('SELECT id, name FROM villages ORDER BY name');

echo json_encode($stmt->fetchAll());
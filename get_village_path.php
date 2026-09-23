<?php
require 'config.php';
header('Content-Type: application/json');

$villageId = filter_input(INPUT_GET, 'village_id', FILTER_VALIDATE_INT);

if (!$villageId) {
    http_response_code(400);
    echo json_encode(['error' => 'village_id is required']);
    exit;
}

// Walk village -> ward -> county -> country -> continent in one shot.
$stmt = $pdo->prepare(
    'SELECT
        ct.id AS continent_id, ct.name AS continent_name,
        cn.id AS country_id,   cn.name AS country_name,
        co.id AS county_id,    co.name AS county_name,
        w.id  AS ward_id,      w.name  AS ward_name,
        v.id  AS village_id,   v.name  AS village_name
     FROM villages v
     JOIN wards w      ON v.ward_id = w.id
     JOIN counties co  ON w.county_id = co.id
     JOIN countries cn ON co.country_id = cn.id
     JOIN continents ct ON cn.continent_id = ct.id
     WHERE v.id = :id'
);
$stmt->execute(['id' => $villageId]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'Village not found']);
    exit;
}

echo json_encode([
    'continent' => ['id' => $row['continent_id'], 'name' => $row['continent_name']],
    'country'   => ['id' => $row['country_id'],   'name' => $row['country_name']],
    'county'    => ['id' => $row['county_id'],    'name' => $row['county_name']],
    'ward'      => ['id' => $row['ward_id'],      'name' => $row['ward_name']],
    'village'   => ['id' => $row['village_id'],   'name' => $row['village_name']],
]);
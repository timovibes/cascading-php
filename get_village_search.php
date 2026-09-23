<?php
require 'config.php';
header('Content-Type: application/json');

$q = filter_input(INPUT_GET, 'q', FILTER_UNSAFE_RAW);
$q = trim((string) $q);

if ($q === '' || strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// Join all the way up so we can show "Village, Ward, County, Country"
// in the results list (village names alone can repeat across the country).
$stmt = $pdo->prepare(
    'SELECT
        v.id   AS id,
        v.name AS name,
        w.name AS ward_name,
        co.name AS county_name,
        cn.name AS country_name
     FROM villages v
     JOIN wards w      ON v.ward_id = w.id
     JOIN counties co  ON w.county_id = co.id
     JOIN countries cn ON co.country_id = cn.id
     WHERE v.name ILIKE :q
     ORDER BY v.name
     LIMIT 20'
);
$stmt->execute(['q' => '%' . $q . '%']);

$rows = $stmt->fetchAll();

$results = array_map(function ($r) {
    return [
        'id'    => $r['id'],
        'label' => sprintf('%s (%s, %s, %s)', $r['name'], $r['ward_name'], $r['county_name'], $r['country_name']),
    ];
}, $rows);

echo json_encode($results);
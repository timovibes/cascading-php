<?php

namespace App\Location;

use PDO;

class LocationRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getContinents(): array
    {
        return $this->pdo->query('SELECT id, name FROM continents ORDER BY name')->fetchAll();
    }

    public function getCountries(int $continentId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, name FROM countries WHERE continent_id = :id ORDER BY name');
        $stmt->execute(['id' => $continentId]);
        return $stmt->fetchAll();
    }

    public function getCounties(int $countryId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, name FROM counties WHERE country_id = :id ORDER BY name');
        $stmt->execute(['id' => $countryId]);
        return $stmt->fetchAll();
    }

    public function getWards(int $countyId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, name FROM wards WHERE county_id = :id ORDER BY name');
        $stmt->execute(['id' => $countyId]);
        return $stmt->fetchAll();
    }

    public function getVillages(int $wardId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, name FROM villages WHERE ward_id = :id ORDER BY name');
        $stmt->execute(['id' => $wardId]);
        return $stmt->fetchAll();
    }

    public function getAllVillages(): array
    {
        return $this->pdo->query('SELECT id, name FROM villages ORDER BY name')->fetchAll();
    }

    public function getVillagePath(int $villageId): ?array
    {
        $stmt = $this->pdo->prepare(
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
            return null;
        }

        return [
            'continent' => ['id' => $row['continent_id'], 'name' => $row['continent_name']],
            'country'   => ['id' => $row['country_id'],   'name' => $row['country_name']],
            'county'    => ['id' => $row['county_id'],    'name' => $row['county_name']],
            'ward'      => ['id' => $row['ward_id'],      'name' => $row['ward_name']],
            'village'   => ['id' => $row['village_id'],   'name' => $row['village_name']],
        ];
    }

    public function searchVillages(string $query): array
    {
        $stmt = $this->pdo->prepare(
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
        $stmt->execute(['q' => '%' . $query . '%']);

        return array_map(function ($r) {
            return [
                'id'    => $r['id'],
                'label' => sprintf('%s (%s, %s, %s)', $r['name'], $r['ward_name'], $r['county_name'], $r['country_name']),
            ];
        }, $stmt->fetchAll());
    }
}
<?php
require 'config.php';

$continents = $pdo->query('SELECT id, name FROM continents ORDER BY name')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cascading Dropdowns Demo</title>
<style>
    body { font-family: Arial, sans-serif; max-width: 420px; margin: 40px auto; }
    label { display: block; margin-top: 14px; font-weight: bold; }
    select { width: 100%; padding: 8px; margin-top: 4px; font-size: 15px; }
    select:disabled { background: #eee; color: #999; }
</style>
</head>
<body>

<h2>Location Picker</h2>

<label for="village_reverse">Pick a Village</label>
<select id="village_reverse">
    <option value="">Select Village</option>
</select>

<hr style="margin-top:20px;">

<label for="continent">Continent</label>
<select id="continent">
    <option value="">Select Continent</option>
    <?php foreach ($continents as $c): ?>
        <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['name']) ?></option>
    <?php endforeach; ?>
</select>

<label for="country">Country</label>
<select id="country" disabled>
    <option value="">Select Country</option>
</select>

<label for="county">County</label>
<select id="county" disabled>
    <option value="">Select County</option>
</select>

<label for="ward">Ward</label>
<select id="ward" disabled>
    <option value="">Select Ward</option>
</select>

<label for="village">Village</label>
<select id="village" disabled>
    <option value="">Select Village</option>
</select>

<script>
// selectedValue (optional): pre-select this id in the dropdown once loaded,
// instead of leaving it on the placeholder. Used by the reverse lookup below.
async function loadOptions(selectId, endpoint, paramName, paramValue, placeholder, selectedValue) {
    const select = document.getElementById(selectId);

    //reset this dropdown to its placeholder and disable it while loading
    select.innerHTML = `<option value="">${placeholder}</option>`;
    select.disabled = true;

    if (!paramValue) return;

    try {
        const response = await fetch(`${endpoint}?${paramName}=${encodeURIComponent(paramValue)}`);
        const items = await response.json();

        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            select.appendChild(option);
        });

        select.disabled = items.length === 0;

        if (selectedValue) {
            select.value = selectedValue;
        }
    } catch (err) {
        console.error(`Failed to load ${selectId}:`, err);
    }
}

//whenever a dropdown changes, load its children and clear everything below it.
document.getElementById('continent').addEventListener('change', function () {
    loadOptions('country', 'get_countries.php', 'continent_id', this.value, '-- Select Country --');
    loadOptions('county', '', '', null, '-- Select County --');
    loadOptions('ward', '', '', null, '-- Select Ward --');
    loadOptions('village', '', '', null, '-- Select Village --');
});

document.getElementById('country').addEventListener('change', function () {
    loadOptions('county', 'get_counties.php', 'country_id', this.value, '-- Select County --');
    loadOptions('ward', '', '', null, '-- Select Ward --');
    loadOptions('village', '', '', null, '-- Select Village --');
});

document.getElementById('county').addEventListener('change', function () {
    loadOptions('ward', 'get_wards.php', 'county_id', this.value, '-- Select Ward --');
    loadOptions('village', '', '', null, '-- Select Village --');
});

document.getElementById('ward').addEventListener('change', function () {
    loadOptions('village', 'get_villages.php', 'ward_id', this.value, '-- Select Village --');
});

const villageReverse = document.getElementById('village_reverse');

// Load every village into the dropdown as soon as the page loads.
(async function loadAllVillages() {
    try {
        const response = await fetch('get_all_villages.php');
        const items = await response.json();

        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            villageReverse.appendChild(option);
        });
    } catch (err) {
        console.error('Failed to load village list:', err);
    }
})();

villageReverse.addEventListener('change', function () {
    if (this.value) selectVillage(this.value);
});

// Fetch the full parent chain for a village and fill the dropdowns top-down,
// pre-selecting each one so the chain ends up fully populated and in sync.
async function selectVillage(villageId) {
    try {
        const response = await fetch(`get_village_path.php?village_id=${encodeURIComponent(villageId)}`);
        if (!response.ok) throw new Error('Lookup failed');
        const path = await response.json();

        // Continents list is static, so just select it directly.
        document.getElementById('continent').value = path.continent.id;

        await loadOptions('country', 'get_countries.php', 'continent_id', path.continent.id, '-- Select Country --', path.country.id);
        await loadOptions('county', 'get_counties.php', 'country_id', path.country.id, '-- Select County --', path.county.id);
        await loadOptions('ward', 'get_wards.php', 'county_id', path.county.id, '-- Select Ward --', path.ward.id);
        await loadOptions('village', 'get_villages.php', 'ward_id', path.ward.id, '-- Select Village --', path.village.id);
    } catch (err) {
        console.error('Failed to resolve village path:', err);
    }
}
</script>

</body>
</html>
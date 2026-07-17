<?php
declare(strict_types=1);

$jsonFile = __DIR__ . '/aout_2025.json';
if (!is_file($jsonFile)) {
    throw new RuntimeException('Générez aout_2025.json via: python scripts/build_eo_aout_2025_json.py');
}
$data = json_decode((string) file_get_contents($jsonFile), true);
if (!is_array($data)) {
    throw new RuntimeException('aout_2025.json invalide.');
}
return $data;

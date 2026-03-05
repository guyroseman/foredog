<?php
/**
 * Foredog — Illinois Scraper (Premium Bio Edition)
 */
declare(strict_types=1);
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/scraper_helpers.php';

$db     = Database::getInstance();
$config = require __DIR__ . '/../config.php';
$token  = $config['socrata_token'] ?? '';
$headers = $token ? ["X-App-Token: $token"] : [];

$totalIns = $totalUpd = 0;
echo "\n=== Illinois Scraper ===\n";
echo "\n[1/1] Cook County Animal & Rabies Control\n";

$url  = 'https://datacatalog.cookcountyil.gov/resource/4nfp-qd5s.json?$where=' . urlencode("species='Dog' AND outcome_type IS NULL") . '&$limit=50';
$body = fetchUrl($url, $headers);

// SAFE JSON PARSING
$rows = [];
if (is_string($body) && !empty($body)) {
    $decoded = json_decode($body, true);
    if (is_array($decoded)) $rows = $decoded;
}

$found = $ins = $upd = $err = 0;
foreach ($rows as $d) {
    $name = cleanName($d['animal_name'] ?? $d['name'] ?? '');
    if (!$name) continue;

    $breed = $d['primary_breed'] ?? $d['breed'] ?? 'Mixed Breed';
    $age = $d['age_upon_intake'] ?? $d['age'] ?? 'Adult';
    $gender = normalizeGender($d['sex_upon_intake'] ?? $d['sex'] ?? '');
    $color = $d['color'] ?? 'Unknown';

    // Generate Premium Foredog Bio
    $rawDesc = trim("$color $breed");
    $desc = generateForedogBio($name, $breed, $age, $gender, $color, $rawDesc);

    $found++;
    $r = upsertDog($db, [
        'external_id'         => 'COOK-' . ($d['animal_id'] ?? $d['id'] ?? uniqid()),
        'name'                => $name,
        'breed_name'          => $breed,
        'age'                 => $age,
        'gender'              => $gender,
        'color'               => $color,
        'description'         => $desc,
        'image_url'           => '',
        'gallery_urls'        => json_encode([]),
        'location'            => 'Cook County, IL',
        'city'                => 'Chicago',
        'state'               => 'IL',
        'source_state'        => 'illinois',
        'source_shelter'      => 'Cook County Animal Control',
        'source_url'          => 'https://www.cookcountyil.gov/agency/animal-and-rabies-control',
        'owner_contact_name'  => 'Cook County Animal Control',
        'owner_contact_phone' => '(708) 974-6140',
        'owner_contact_email' => 'animal.control@cookcountyil.gov',
    ]);
    if ($r === 'inserted') { $ins++; $totalIns++; }
    elseif ($r === 'updated') { $upd++; $totalUpd++; }
    else $err++;
}
printSummary('Cook County Animal Control', $found, $ins, $upd, $err);
echo "\nIllinois total — inserted: $totalIns | updated: $totalUpd\n";
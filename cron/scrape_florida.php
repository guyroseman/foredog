<?php
/**
 * Foredog — Florida Scraper (Premium Bio Edition)
 */
declare(strict_types=1);
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/scraper_helpers.php';

$db     = Database::getInstance();
$config = require __DIR__ . '/../config.php';
$token  = $config['socrata_token'] ?? '';
$headers = $token ? ["X-App-Token: $token"] : [];

$totalIns = $totalUpd = 0;
echo "\n=== Florida Scraper ===\n";
echo "\n[1/1] Miami-Dade Animal Services\n";

$url  = 'https://opendata.miamidade.gov/resource/x4s3-bwkj.json?$where=' . urlencode("animal_type='DOG' AND outcome_type IS NULL") . '&$limit=50';
$body = fetchUrl($url, $headers);

// SAFE JSON PARSING (Prevents crashes if API sends an HTML error)
$rows = [];
if (is_string($body) && !empty($body)) {
    $decoded = json_decode($body, true);
    if (is_array($decoded)) $rows = $decoded;
}

$found = $ins = $upd = $err = 0;
foreach ($rows as $d) {
    $name = cleanName($d['animal_name'] ?? '');
    if (!$name) continue;

    $breed = $d['primary_breed'] ?? 'Mixed Breed';
    $age = $d['age'] ?? ($d['dob'] ?? 'Adult'); 
    $gender = normalizeGender($d['sex'] ?? '');
    $color = $d['color'] ?? 'Unknown';

    // Generate Premium Foredog Bio
    $rawDesc = trim("$color $breed");
    $desc = generateForedogBio($name, $breed, $age, $gender, $color, $rawDesc);

    $found++;
    $r = upsertDog($db, [
        'external_id'         => 'MIAMIDADE-' . ($d['animal_id'] ?? uniqid()),
        'name'                => $name,
        'breed_name'          => $breed,
        'age'                 => $age,
        'gender'              => $gender,
        'color'               => $color,
        'description'         => $desc,
        'image_url'           => '',
        'gallery_urls'        => json_encode([]),
        'location'            => 'Miami, FL',
        'city'                => 'Miami',
        'state'               => 'FL',
        'source_state'        => 'florida',
        'source_shelter'      => 'Miami-Dade Animal Services',
        'source_url'          => 'https://animals.miamidade.gov/',
        'owner_contact_name'  => 'Miami-Dade Animal Services',
        'owner_contact_phone' => '(305) 884-1101',
        'owner_contact_email' => 'petadoptions@miamidade.gov',
    ]);
    if ($r === 'inserted') { $ins++; $totalIns++; }
    elseif ($r === 'updated') { $upd++; $totalUpd++; }
    else $err++;
}
printSummary('Miami-Dade Animal Services', $found, $ins, $upd, $err);
echo "\nFlorida total — inserted: $totalIns | updated: $totalUpd\n";
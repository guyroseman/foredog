<?php
/**
 * Foredog — Illinois Scraper
 *
 * Sources:
 *   1. Cook County Animal & Rabies Control  (Socrata — datacatalog.cookcountyil.gov)
 *
 * Run: php cron/scrape_illinois.php
 *
 * ─── Cook County Note ────────────────────────────────────────────────────────
 * If the Socrata endpoint returns 0 dogs, verify the dataset ID by visiting:
 *   https://datacatalog.cookcountyil.gov/browse?category=Finance+%26+Administration
 * Search "animal" and find the live animals dataset, then update the URL below.
 * ─────────────────────────────────────────────────────────────────────────────
 */

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/scraper_helpers.php';

$db     = Database::getInstance();
$config = require __DIR__ . '/../config.php';

$token   = $config['socrata_token'] ?? '';
$headers = $token ? ["X-App-Token: $token"] : [];

$totalIns = $totalUpd = 0;
echo "=== Illinois Scraper ===\n";

// ─────────────────────────────────────────────────────────────────────────────
// Cook County Animal & Rabies Control
// Dataset ID: 4nfp-qd5s  (verify at datacatalog.cookcountyil.gov)
// ─────────────────────────────────────────────────────────────────────────────
echo "\n[1/1] Cook County Animal & Rabies Control\n";

$url  = 'https://datacatalog.cookcountyil.gov/resource/4nfp-qd5s.json'
      . '?$where=' . urlencode("species='Dog' AND outcome_type IS NULL")
      . '&$limit=500';
$body = fetchUrl($url, $headers);
$rows = $body ? json_decode($body, true) : [];

$found = $ins = $upd = $err = 0;
foreach ((array) $rows as $d) {
    $found++;
    $r = upsertDog($db, [
        'external_id'         => 'COOK-' . ($d['animal_id'] ?? $d['id'] ?? uniqid()),
        'name'                => $d['animal_name']   ?? ($d['name'] ?? 'Unknown'),
        'breed_name'          => $d['primary_breed'] ?? ($d['breed'] ?? 'Mixed Breed'),
        'age'                 => $d['age_upon_intake'] ?? ($d['age'] ?? ''),
        'gender'              => $d['sex_upon_intake'] ?? ($d['sex'] ?? ''),
        'description'         => trim(($d['color'] ?? '') . ' ' . ($d['primary_breed'] ?? $d['breed'] ?? '')),
        'image_url'           => '',
        'location'            => 'Cook County, IL',
        'city'                => 'Chicago',
        'state'               => 'IL',
        'source_state'        => 'illinois',
        'owner_contact_name'  => 'Cook County Animal & Rabies Control',
        'owner_contact_phone' => '(708) 974-6140',
        'owner_contact_email' => 'animal.control@cookcountyil.gov',
    ]);
    if ($r === 'inserted')    { $ins++; $totalIns++; }
    elseif ($r === 'updated') { $upd++; $totalUpd++; }
    else $err++;
}
printSummary('Cook County Animal & Rabies Control', $found, $ins, $upd, $err);

echo "\nIllinois total — inserted: $totalIns | updated: $totalUpd\n";
<?php
/**
 * Foredog — Florida Scraper
 *
 * Sources:
 *   1. Miami-Dade Animal Services  (Socrata — opendata.miamidade.gov)
 *
 * Note: Hillsborough County (Tampa) was dropped — their site runs on Oracle APEX
 *       (session-based JS, completely unscrapeable). Miami-Dade is the replacement.
 *
 * Run: php cron/scrape_florida.php
 */

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/scraper_helpers.php';

$db     = Database::getInstance();
$config = require __DIR__ . '/../config.php';

$token   = $config['socrata_token'] ?? '';
$headers = $token ? ["X-App-Token: $token"] : [];

$totalIns = $totalUpd = 0;
echo "=== Florida Scraper ===\n";

// ─────────────────────────────────────────────────────────────────────────────
// Miami-Dade Animal Services
// Dataset: opendata.miamidade.gov — animal services shelter animals
// Dataset ID: x4s3-bwkj (verify at opendata.miamidade.gov if 0 rows returned)
// ─────────────────────────────────────────────────────────────────────────────
echo "\n[1/1] Miami-Dade Animal Services\n";

$url  = 'https://opendata.miamidade.gov/resource/x4s3-bwkj.json'
      . '?$where=' . urlencode("animal_type='DOG' AND outcome_type IS NULL")
      . '&$limit=500';
$body = fetchUrl($url, $headers);
$rows = $body ? json_decode($body, true) : [];

$found = $ins = $upd = $err = 0;
foreach ((array) $rows as $d) {
    $found++;
    $r = upsertDog($db, [
        'external_id'         => 'MIAMIDADE-' . ($d['animal_id'] ?? uniqid()),
        'name'                => $d['animal_name']   ?? 'Unknown',
        'breed_name'          => $d['primary_breed'] ?? 'Mixed Breed',
        'age'                 => $d['age']           ?? ($d['dob'] ?? ''),
        'gender'              => $d['sex']           ?? '',
        'description'         => trim(($d['color'] ?? '') . ' ' . ($d['primary_breed'] ?? '')),
        'image_url'           => '',
        'location'            => 'Miami, FL',
        'city'                => 'Miami',
        'state'               => 'FL',
        'source_state'        => 'florida',
        'owner_contact_name'  => 'Miami-Dade Animal Services',
        'owner_contact_phone' => '(305) 884-1101',
        'owner_contact_email' => 'petadoptions@miamidade.gov',
    ]);
    if ($r === 'inserted')    { $ins++; $totalIns++; }
    elseif ($r === 'updated') { $upd++; $totalUpd++; }
    else $err++;
}
printSummary('Miami-Dade Animal Services', $found, $ins, $upd, $err);

echo "\nFlorida total — inserted: $totalIns | updated: $totalUpd\n";
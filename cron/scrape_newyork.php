<?php
/**
 * Foredog — New York Scraper
 *
 * Sources:
 *   1. NYC Animal Care Centers  (NYC Open Data — Socrata)
 *
 * Why not nycacc.app?
 *   nycacc.org is a Flutter web app (Dart compiled to JS rendered in canvas).
 *   There is no HTML to parse. The underlying data comes from NYC Open Data,
 *   which we query directly via the Socrata API.
 *
 * Dataset: https://data.cityofnewyork.us/resource/8nwg-abmf.json
 *   (Animal Care Centers of NYC — Animal Shelter Intakes)
 *
 * Run: php cron/scrape_newyork.php
 */

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/scraper_helpers.php';

$db     = Database::getInstance();
$config = require __DIR__ . '/../config.php';

$token   = $config['socrata_token'] ?? '';
$headers = $token ? ["X-App-Token: $token"] : [];

$totalIns = $totalUpd = 0;
echo "=== New York Scraper ===\n";

// ─────────────────────────────────────────────────────────────────────────────
// NYC Animal Care Centers
// Dataset ID: 8nwg-abmf  — ACC animal intake/outcome data
// We filter: species=Dog, no outcome yet (still in shelter).
// ─────────────────────────────────────────────────────────────────────────────
echo "\n[1/1] NYC Animal Care Centers\n";

$url  = 'https://data.cityofnewyork.us/resource/8nwg-abmf.json'
      . '?$where=' . urlencode("species='Dog' AND outcome_type IS NULL")
      . '&$limit=500';
$body = fetchUrl($url, $headers);
$rows = $body ? json_decode($body, true) : [];

$found = $ins = $upd = $err = 0;
foreach ((array) $rows as $d) {
    $found++;
    // Borough → city label
    $borough = $d['intake_condition'] ?? '';
    $r = upsertDog($db, [
        'external_id'         => 'NYC-' . ($d['animal_id'] ?? uniqid()),
        'name'                => $d['animal_name'] ?? 'Unknown',
        'breed_name'          => $d['primary_breed'] ?? 'Mixed Breed',
        'age'                 => $d['age_upon_intake'] ?? '',
        'gender'              => $d['sex_upon_intake'] ?? '',
        'description'         => trim(($d['color'] ?? '') . ' ' . ($d['primary_breed'] ?? '')),
        'image_url'           => '',
        'location'            => 'New York, NY',
        'city'                => 'New York',
        'state'               => 'NY',
        'source_state'        => 'newyork',
        'owner_contact_name'  => 'NYC Animal Care Centers',
        'owner_contact_phone' => '(212) 788-4000',
        'owner_contact_email' => 'info@nycacc.org',
    ]);
    if ($r === 'inserted')    { $ins++; $totalIns++; }
    elseif ($r === 'updated') { $upd++; $totalUpd++; }
    else $err++;
}
printSummary('NYC Animal Care Centers', $found, $ins, $upd, $err);

echo "\nNew York total — inserted: $totalIns | updated: $totalUpd\n";
<?php
/**
 * Foredog — Texas Scraper
 *
 * Sources:
 *   1. Austin Animal Center  (Socrata — data.austintexas.gov)
 *   2. Dallas Animal Services (Adopets API — ⚠️ endpoint TBD)
 *
 * Run: php cron/scrape_texas.php
 *
 * ─── Dallas TODO ─────────────────────────────────────────────────────────────
 * Dallas loads dogs via an Adopets iframe (React SPA).
 * To find the real API endpoint:
 *   1. Open: https://www.dallasanimalservices.org/adopt_a_pet
 *   2. DevTools → Network → Fetch/XHR tab → Refresh
 *   3. Look for a request to api.adopets.com  (e.g. GET /v2/animals?shelter_uuid=...)
 *   4. Paste that URL below as DALLAS_ADOPETS_URL
 * ─────────────────────────────────────────────────────────────────────────────
 */

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/scraper_helpers.php';

$db     = Database::getInstance();
$config = require __DIR__ . '/../config.php';

$token   = $config['socrata_token'] ?? '';
$headers = $token ? ["X-App-Token: $token"] : [];

$totalIns = $totalUpd = 0;
echo "=== Texas Scraper ===\n";

// ─────────────────────────────────────────────────────────────────────────────
// 1. Austin Animal Center  (Socrata)
//    Dataset: 9t4d-g238  — Austin Animal Center Outcomes
//    We filter for dogs with no outcome (still in shelter).
// ─────────────────────────────────────────────────────────────────────────────
echo "\n[1/2] Austin Animal Center\n";

$url  = 'https://data.austintexas.gov/resource/9t4d-g238.json'
      . '?$where=' . urlencode("animal_type='Dog' AND outcome_type IS NULL")
      . '&$limit=500';
$body = fetchUrl($url, $headers);
$rows = $body ? json_decode($body, true) : [];

$found = $ins = $upd = $err = 0;
foreach ((array) $rows as $d) {
    $found++;
    $r = upsertDog($db, [
        'external_id'         => 'AUSTIN-' . ($d['animal_id'] ?? uniqid()),
        'name'                => $d['name']         ?? 'Unknown',
        'breed_name'          => $d['breed']        ?? 'Mixed Breed',
        'age'                 => $d['age_upon_outcome'] ?? '',
        'gender'              => $d['sex_upon_outcome'] ?? '',
        'description'         => trim(($d['color'] ?? '') . ' ' . ($d['breed'] ?? '')),
        'image_url'           => '',
        'location'            => 'Austin, TX',
        'city'                => 'Austin',
        'state'               => 'TX',
        'source_state'        => 'texas',
        'owner_contact_name'  => 'Austin Animal Center',
        'owner_contact_phone' => '(512) 978-0500',
        'owner_contact_email' => 'animal.shelter@austintexas.gov',
    ]);
    if ($r === 'inserted')    { $ins++; $totalIns++; }
    elseif ($r === 'updated') { $upd++; $totalUpd++; }
    else $err++;
}
printSummary('Austin Animal Center', $found, $ins, $upd, $err);


// ─────────────────────────────────────────────────────────────────────────────
// 2. Dallas Animal Services  (Adopets API)
//    shelter_uuid: fe5e083e-7beb-43d5-a3b3-2811fee91695
//    ⚠️  Replace DALLAS_ADOPETS_URL with the real endpoint from DevTools.
// ─────────────────────────────────────────────────────────────────────────────
echo "\n[2/2] Dallas Animal Services\n";

define('DALLAS_ADOPETS_URL', ''); // ← paste Adopets API URL here

if (empty(DALLAS_ADOPETS_URL)) {
    echo "  [SKIP] Dallas Adopets URL not configured yet.\n";
    echo "         See instructions at top of this file.\n";
} else {
    $adopetsHeaders = [
        'Origin: https://www.dallasanimalservices.org',
        'Referer: https://www.dallasanimalservices.org/',
    ];
    $body = fetchUrl(DALLAS_ADOPETS_URL, $adopetsHeaders);
    $json = $body ? json_decode($body, true) : null;
    // Adopets typically wraps results in json['data']['animals'] or json['animals']
    $animals = $json['data']['animals'] ?? $json['animals'] ?? $json['results'] ?? (array)$json;

    $found = $ins = $upd = $err = 0;
    foreach ($animals as $d) {
        if (strtolower($d['type'] ?? '') !== 'dog') continue;
        $found++;
        $r = upsertDog($db, [
            'external_id'         => 'DALLAS-' . ($d['id'] ?? $d['uuid'] ?? uniqid()),
            'name'                => $d['name']   ?? 'Unknown',
            'breed_name'          => $d['breeds']['primary'] ?? $d['breed'] ?? 'Mixed Breed',
            'age'                 => $d['age']    ?? '',
            'gender'              => $d['gender'] ?? '',
            'description'         => $d['description'] ?? '',
            'image_url'           => $d['photos'][0]['full'] ?? $d['primary_photo_cropped']['full'] ?? '',
            'location'            => 'Dallas, TX',
            'city'                => 'Dallas',
            'state'               => 'TX',
            'source_state'        => 'texas',
            'owner_contact_name'  => 'Dallas Animal Services',
            'owner_contact_phone' => '(214) 670-8246',
            'owner_contact_email' => 'das@dallas.gov',
        ]);
        if ($r === 'inserted')    { $ins++; $totalIns++; }
        elseif ($r === 'updated') { $upd++; $totalUpd++; }
        else $err++;
    }
    printSummary('Dallas Animal Services', $found, $ins, $upd, $err);
}

echo "\nTexas total — inserted: $totalIns | updated: $totalUpd\n";
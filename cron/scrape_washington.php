<?php
/**
 * Foredog — Washington Scraper
 *
 * Sources:
 *   1. King County Regional Animal Services  (custom JS app — ⚠️ API endpoint TBD)
 *
 * ─── King County TODO ────────────────────────────────────────────────────────
 * King County loads dogs via a custom JavaScript app:
 *   https://cdn.kingcounty.gov/-/media/king-county/fe-apps/executive-services/
 *           animal-services/pets-lookup-js.js
 *
 * To find the API endpoint:
 *   1. Open: https://www.kingcounty.gov/en/dept/regional-animal-services/adopt-foster-volunteer/adopt
 *   2. DevTools → Network → Fetch/XHR tab → Refresh
 *   3. Look for a request loading dog/pet data (JSON response)
 *   4. Paste that URL below as KINGCOUNTY_API_URL
 *
 * Alternative: Fetch the JS file directly and search for the API base URL:
 *   curl https://cdn.kingcounty.gov/.../pets-lookup-js.js | grep -o 'https://[^"]*api[^"]*'
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Run: php cron/scrape_washington.php
 */

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/scraper_helpers.php';

$db = Database::getInstance();

$totalIns = $totalUpd = 0;
echo "=== Washington Scraper ===\n";

// ─────────────────────────────────────────────────────────────────────────────
// King County Regional Animal Services
// ⚠️  Replace KINGCOUNTY_API_URL with the real endpoint from DevTools.
// ─────────────────────────────────────────────────────────────────────────────
echo "\n[1/1] King County Regional Animal Services\n";

define('KINGCOUNTY_API_URL', ''); // ← paste API URL here

if (empty(KINGCOUNTY_API_URL)) {
    echo "  [SKIP] King County API URL not configured yet.\n";
    echo "         See instructions at top of this file.\n";
} else {
    $body = fetchUrl(KINGCOUNTY_API_URL);
    $json = $body ? json_decode($body, true) : null;

    // Adjust these keys once you see the real JSON shape.
    $animals = $json['animals'] ?? $json['data'] ?? $json['pets'] ?? (array)$json;

    $found = $ins = $upd = $err = 0;
    foreach ($animals as $d) {
        if (!empty($d['type']) && strtolower($d['type']) !== 'dog') continue;
        $found++;
        $r = upsertDog($db, [
            'external_id'         => 'KC-' . ($d['id'] ?? $d['animal_id'] ?? uniqid()),
            'name'                => $d['name']          ?? 'Unknown',
            'breed_name'          => $d['breed']         ?? ($d['breeds']['primary'] ?? 'Mixed Breed'),
            'age'                 => $d['age']           ?? '',
            'gender'              => $d['gender']        ?? ($d['sex'] ?? ''),
            'description'         => $d['description']   ?? '',
            'image_url'           => $d['photo']         ?? ($d['photos'][0]['full'] ?? ''),
            'location'            => 'King County, WA',
            'city'                => 'Seattle',
            'state'               => 'WA',
            'source_state'        => 'washington',
            'owner_contact_name'  => 'King County Regional Animal Services',
            'owner_contact_phone' => '(206) 296-7387',
            'owner_contact_email' => 'ras@kingcounty.gov',
        ]);
        if ($r === 'inserted')    { $ins++; $totalIns++; }
        elseif ($r === 'updated') { $upd++; $totalUpd++; }
        else $err++;
    }
    printSummary('King County Regional Animal Services', $found, $ins, $upd, $err);
}

echo "\nWashington total — inserted: $totalIns | updated: $totalUpd\n";
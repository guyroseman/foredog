<?php
/**
 * Foredog — Washington Scraper (Premium Bio Edition)
 */
declare(strict_types=1);
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/scraper_helpers.php';

$db = Database::getInstance();

$totalIns = $totalUpd = 0;
echo "\n=== Washington Scraper ===\n";
echo "\n[1/1] King County Regional Animal Services\n";

define('KINGCOUNTY_API_URL', ''); // ← paste API URL here when found

if (empty(KINGCOUNTY_API_URL)) {
    echo "  [SKIP] King County API URL not configured yet. Skipping to prevent errors.\n";
} else {
    $body = fetchUrl(KINGCOUNTY_API_URL);
    
    // SAFE JSON PARSING
    $json = [];
    if (is_string($body) && !empty($body)) {
        $decoded = json_decode($body, true);
        if (is_array($decoded)) $json = $decoded;
    }

    $animals = $json['animals'] ?? $json['data'] ?? $json['pets'] ?? $json;

    $found = $ins = $upd = $err = 0;
    foreach ($animals as $d) {
        if (!empty($d['type']) && strtolower($d['type']) !== 'dog') continue;
        
        $name = cleanName($d['name'] ?? '');
        if (!$name) continue;

        $breed = $d['breed'] ?? ($d['breeds']['primary'] ?? 'Mixed Breed');
        $age = $d['age'] ?? 'Adult';
        $gender = normalizeGender($d['gender'] ?? $d['sex'] ?? '');
        $color = $d['color'] ?? 'Unknown';
        
        // Generate Premium Foredog Bio
        $rawDesc = trim($d['description'] ?? '');
        $desc = generateForedogBio($name, $breed, $age, $gender, $color, $rawDesc);

        $found++;
        $r = upsertDog($db, [
            'external_id'         => 'KC-' . ($d['id'] ?? $d['animal_id'] ?? uniqid()),
            'name'                => $name,
            'breed_name'          => $breed,
            'age'                 => $age,
            'gender'              => $gender,
            'color'               => $color,
            'description'         => $desc,
            'image_url'           => $d['photo'] ?? ($d['photos'][0]['full'] ?? ''),
            'gallery_urls'        => json_encode([]),
            'location'            => 'King County, WA',
            'city'                => 'Seattle',
            'state'               => 'WA',
            'source_state'        => 'washington',
            'source_shelter'      => 'King County Regional Animal Services',
            'source_url'          => 'https://kingcounty.gov/en/dept/regional-animal-services',
            'owner_contact_name'  => 'King County Animal Services',
            'owner_contact_phone' => '(206) 296-7387',
            'owner_contact_email' => 'ras@kingcounty.gov',
        ]);
        if ($r === 'inserted') { $ins++; $totalIns++; }
        elseif ($r === 'updated') { $upd++; $totalUpd++; }
        else $err++;
    }
    printSummary('King County Regional Animal Services', $found, $ins, $upd, $err);
}

echo "\nWashington total — inserted: $totalIns | updated: $totalUpd\n";
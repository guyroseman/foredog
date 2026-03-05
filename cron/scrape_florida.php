<?php
/**
 * Foredog Scraper - Florida Master
 * Scrapes SPCA Florida & Humane Society of Broward County (ShelterLuv API)
 * + Miami-Dade Animal Services (Socrata open data)
 */
declare(strict_types=1);
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/scraper_helpers.php';

$config = require __DIR__ . '/../config.php';
$db = Database::getInstance();
$totIns = 0; $totUpd = 0;
echo "\n=== Florida Scraper ===\n\n";

// =====================================================================
// HELPER: Scrape a ShelterLuv shelter by GID
// =====================================================================
function scrapeShelterLuv(
    PDO $db,
    string $gid,
    string $shelterName,
    string $city,
    string $profileUrlBase,
    string $phone,
    string $email,
    int &$totIns,
    int &$totUpd
): void {
    $apiUrl  = "https://www.shelterluv.com/api/v3/available-animals/{$gid}?species=Dog";
    $body    = fetchUrl($apiUrl);
    $animals = [];

    if (is_string($body) && !empty($body)) {
        $decoded = json_decode($body, true);
        if (is_array($decoded) && isset($decoded['animals'])) {
            $animals = $decoded['animals'];
        }
    }

    $found = count($animals);
    $s = $u = $e = 0;

    foreach ($animals as $d) {
        $name = cleanName($d['Name'] ?? $d['name'] ?? '');
        if (!$name) continue;

        $dogId   = $d['ID'] ?? $d['id'] ?? uniqid();
        $breed   = $d['Breed'] ?? $d['breed'] ?? 'Mixed Breed';
        $rawAge  = (string)($d['Age'] ?? $d['age'] ?? '12'); // ShelterLuv = months
        $color   = $d['Color'] ?? $d['color'] ?? 'Unknown';
        $rawDesc = trim($d['Description'] ?? $d['description'] ?? '');
        $gender  = normalizeGender($d['Sex'] ?? $d['sex'] ?? '');

        // Photos
        $photoUrls = [];
        foreach (($d['Photos'] ?? $d['photos'] ?? []) as $p) {
            if (is_string($p)) $photoUrls[] = $p;
            elseif (is_array($p) && isset($p['url'])) $photoUrls[] = $p['url'];
        }
        if (empty($photoUrls)) {
            $cover = $d['CoverPhoto'] ?? $d['coverPhoto'] ?? '';
            if ($cover) $photoUrls[] = is_array($cover) ? $cover['url'] : $cover;
        }
        $cleanImages = extractValidImages($photoUrls);

        $age  = normalizeAge($rawAge);
        $desc = generateForedogBio($name, $breed, $age, $gender, $color, $rawDesc);

        $res = upsertDog($db, [
            'external_id'         => 'FL-SL-' . $dogId,
            'source_shelter'      => $shelterName,
            'source_url'          => $profileUrlBase . $dogId,
            'source_state'        => 'florida',
            'name'                => $name,
            'breed_name'          => $breed,
            'location'            => "$city, FL",
            'city'                => $city,
            'state'               => 'FL',
            'age'                 => $age,
            'gender'              => $gender,
            'color'               => $color,
            'description'         => $desc,
            'image_url'           => $cleanImages[0] ?? '',
            'gallery_urls'        => json_encode($cleanImages),
            'owner_contact_name'  => $shelterName,
            'owner_contact_phone' => $phone,
            'owner_contact_email' => $email,
        ]);

        if ($res === 'inserted') $s++; elseif ($res === 'updated') $u++; else $e++;
    }

    $db->prepare("
        UPDATE dogs SET status='adopted', adopted_at=NOW()
        WHERE source_shelter=? AND status='available'
        AND last_seen_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)
    ")->execute([$shelterName]);

    printSummary($shelterName, $found, $s, $u, $e);
    $totIns += $s; $totUpd += $u;
}

// =====================================================================
// 1. SPCA FLORIDA — Lakeland
//    Phone: (863) 577-4615 | Email: adopt@spcaflorida.org
// =====================================================================
echo "Scanning: SPCA Florida...\n";
scrapeShelterLuv(
    $db, '13324', 'SPCA Florida', 'Lakeland',
    'https://www.shelterluv.com/embed/animal/',
    '(863) 577-4615', 'adopt@spcaflorida.org',
    $totIns, $totUpd
);

// =====================================================================
// 2. HUMANE SOCIETY OF BROWARD COUNTY — Fort Lauderdale
//    Phone: (954) 266-6807 | Email: info@browardhumane.org
// =====================================================================
echo "Scanning: Humane Society of Broward County...\n";

$gid       = null;
$indexHtml = fetchUrl('https://www.browardhumane.org/adopt/dogs/');
if ($indexHtml && preg_match('/var\s+GID\s*=\s*["\']?(\d+)["\']?/i', $indexHtml, $m)) {
    $gid = $m[1];
}
if (!$gid && $indexHtml && preg_match('/shelterluv\.com\/embed\/(\d+)/i', $indexHtml, $m)) {
    $gid = $m[1];
}
$gid = $gid ?? '15220';

scrapeShelterLuv(
    $db, $gid, 'Humane Society of Broward County', 'Fort Lauderdale',
    'https://www.shelterluv.com/embed/animal/',
    '(954) 989-3977', 'info@hsbroward.com',
    $totIns, $totUpd
);

// =====================================================================
// 3. MIAMI-DADE ANIMAL SERVICES — Miami
//    Phone: (305) 884-1101 | Email: animals@miamidade.gov
// =====================================================================
echo "Scanning: Miami-Dade Animal Services...\n";

$found = 0; $s = 0; $u = 0; $e = 0;
$limit = 1000; $offset = 0;
$animals = [];

do {
    $url = sprintf(
        'https://opendata.miamidade.gov/resource/x4s3-bwkj.json?$where=%s&$limit=%d&$offset=%d',
        urlencode("animal_type='DOG' AND outcome_type IS NULL"),
        $limit, $offset
    );

    $raw     = fetchUrl($url, ['X-App-Token: ' . ($config['socrata_token'] ?? '')]);
    $animals = $raw ? json_decode($raw, true) : [];

    if (!is_array($animals)) {
        echo "  Miami-Dade: PARSE FAILED\n";
        break;
    }

    foreach ($animals as $a) {
        $breedName = $a['breed'] ?? 'Mixed Breed';
        if (!empty($a['secondary_breed'])) $breedName .= ' / ' . $a['secondary_breed'];

        $color = ucwords(strtolower(
            ($a['dominant_color'] ?? '') .
            (!empty($a['secondary_color']) ? ' / ' . $a['secondary_color'] : '')
        ));

        $res = upsertDog($db, [
            'external_id'         => 'FL-MD-' . ($a['animal_id'] ?? uniqid()),
            'source_shelter'      => 'Miami-Dade Animal Services',
            'source_url'          => 'https://www.miamidade.gov/animals/pet-adoption.asp',
            'source_state'        => 'florida',
            'name'                => ucwords(strtolower($a['animal_name'] ?? 'Unknown')),
            'breed_name'          => $breedName,
            'location'            => 'Miami, FL',
            'city'                => 'Miami',
            'state'               => 'FL',
            'age'                 => normalizeAge($a['age'] ?? ''),
            'gender'              => $a['sex'] ?? '',
            'color'               => $color,
            'description'         => '',
            'image_url'           => '',
            'gallery_urls'        => json_encode([]),
            'owner_contact_name'  => 'Miami-Dade Animal Services',
            'owner_contact_phone' => '(305) 468-5900',
            'owner_contact_email' => 'asdfoster@miamidade.gov',
        ]);

        if ($res === 'inserted') { $s++; $found++; }
        elseif ($res === 'updated') { $u++; $found++; }
        else $e++;
    }

    $offset += $limit;

} while (count($animals) === $limit);

$db->prepare("
    UPDATE dogs SET status='adopted', adopted_at=NOW()
    WHERE source_shelter='Miami-Dade Animal Services' AND status='available'
    AND last_seen_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)
")->execute();

printSummary('Miami-Dade Animal Services', $found, $s, $u, $e);
$totIns += $s; $totUpd += $u;

echo "\nFlorida total — inserted: $totIns | updated: $totUpd\n";
echo str_repeat("─", 50) . "\n";
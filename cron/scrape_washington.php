<?php
/**
 * Foredog Scraper - Washington Master
 * Scrapes Spokane Humane Society (ShelterLuv API) & Tacoma Humane (HTML Deep Scrape)
 */
declare(strict_types=1);
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/scraper_helpers.php'; 

$db = Database::getInstance();
$totIns = 0; $totUpd = 0; $totErr = 0;
echo "\n=== Washington Scraper ===\n\n";

// =====================================================================
// 1. SPOKANE HUMANE SOCIETY (Dynamic ShelterLuv API)
// =====================================================================
echo "Scanning: Spokane Humane Society...\n";

$indexHtml = fetchUrl('https://spokanehumanesociety.org/adopt-dogs/');
$gid = null;

if ($indexHtml && preg_match('/var\s+GID\s*=\s*(\d+)/i', $indexHtml, $m)) {
    $gid = $m[1];
}

if (!$gid) {
    $gid = '100001095'; // Known fallback ID for Spokane
}

if ($gid) {
    $apiUrl = "https://www.shelterluv.com/api/v3/available-animals/$gid?species=Dog";
    $body = fetchUrl($apiUrl);

    $json = [];
    if (is_string($body) && !empty($body)) {
        $decoded = json_decode($body, true);
        if (is_array($decoded) && isset($decoded['animals'])) {
            $json = $decoded['animals'];
        }
    }

    $found = count($json);
    $s = $u = $e = 0;

    foreach ($json as $d) {
        $rawName = $d['Name'] ?? $d['name'] ?? '';
        $name = cleanName($rawName);
        if (!$name) continue;

        $breed = $d['Breed'] ?? $d['breed'] ?? 'Mixed Breed';
        $rawAge = $d['Age'] ?? $d['age'] ?? 12; // Shelterluv returns age in MONTHS
        $gender = normalizeGender($d['Sex'] ?? $d['sex'] ?? '');
        $color = $d['Color'] ?? $d['color'] ?? 'Unknown';
        $rawDesc = trim($d['Description'] ?? $d['description'] ?? '');
        $dogId = $d['ID'] ?? $d['id'] ?? uniqid();
        $profileLink = "https://spokanehumanesociety.org/adopt-dogs/#sl_embed&page=shelterluv_wrap_1601305436322%2Fembed%2Fanimal%2F{$dogId}";

        $rawPhotos = $d['Photos'] ?? $d['photos'] ?? [];
        $photoUrls = [];
        foreach ($rawPhotos as $p) {
            if (is_string($p)) { $photoUrls[] = $p; }
            elseif (is_array($p) && isset($p['url'])) { $photoUrls[] = $p['url']; }
        }

        if (empty($photoUrls)) {
            $cover = $d['CoverPhoto'] ?? $d['coverPhoto'] ?? '';
            if ($cover) $photoUrls[] = (is_array($cover) ? $cover['url'] : $cover);
        }
        
        $cleanImages = extractValidImages($photoUrls);
        $age = normalizeAge((string)$rawAge);
        $desc = generateForedogBio($name, $breed, $age, $gender, $color, $rawDesc);

        $res = upsertDog($db, [
            'external_id'    => 'WA-SHS-' . $dogId,
            'source_shelter' => 'Spokane Humane Society',
            'source_url'     => $profileLink,
            'source_state'   => 'washington',
            'name'           => $name,
            'breed_name'     => $breed,
            'location'       => 'Spokane, WA',
            'city'           => 'Spokane',
            'state'          => 'WA',
            'age'            => $age,
            'gender'         => $gender,
            'color'          => $color,
            'description'    => $desc,
            'image_url'      => $cleanImages[0] ?? '',
            'gallery_urls'   => json_encode($cleanImages),
            'owner_contact_name'  => 'Spokane Humane Society',
            'owner_contact_phone' => '(509) 467-5235',
            'owner_contact_email' => 'adoptions@spokanehumanesociety.org',
        ]);

        if ($res === 'inserted') $s++; elseif ($res === 'updated') $u++; else $e++;
    }

    $db->prepare("UPDATE dogs SET status='adopted', adopted_at=NOW() WHERE source_shelter='Spokane Humane Society' AND status='available' AND last_seen_at < DATE_SUB(NOW(),INTERVAL 48 HOUR)")->execute();
    
    printSummary('Spokane Humane', $found, $s, $u, $e);
    $totIns += $s; $totUpd += $u;
}

// =====================================================================
// 2. THE HUMANE SOCIETY FOR TACOMA & PIERCE COUNTY
// =====================================================================
echo "Scanning: Tacoma Humane...\n";

$indexUrl = 'https://www.thehumanesociety.org/adoptable-pet-category/dogs/';
$indexHtml = fetchUrl($indexUrl);

$links = [];
if ($indexHtml) {
    $xp = getXPath($indexHtml);
    foreach($xp->query("//a[contains(@class, 'pet-card')]/@href") as $node) {
        $link = trim($node->nodeValue);
        if (!str_starts_with($link, 'http')) {
            $link = 'https://www.thehumanesociety.org' . $link;
        }
        if (!in_array($link, $links)) {
            $links[] = $link;
        }
    }
}

$linksToFetch = array_slice($links, 0, 15);
$profilePages = fetchMultiplePages($linksToFetch);
$found = count($linksToFetch);
$s = $u = $e = 0;

foreach ($profilePages as $link => $html) {
    $xp = getXPath($html);
    
    $name = cleanName($xp->evaluate("string(//h1[contains(@class, 'headline')])"));
    if (!$name) continue;

    $petIdRaw = $xp->evaluate("string(//p[strong[contains(text(), 'PET ID')]])");
    preg_match('/PET ID:\s*(\d+)/i', $petIdRaw, $idMatch);
    $petId = $idMatch[1] ?? md5($link);

    $rawBreed = $xp->evaluate("normalize-space(//p[strong[contains(text(), 'Breed')]])");
    $rawBreed = str_replace('Breed:', '', $rawBreed);
    
    $rawGender = $xp->evaluate("normalize-space(//p[strong[contains(text(), 'Sex')]])");
    $rawGender = str_replace('Sex:', '', $rawGender);

    $rawAge = $xp->evaluate("normalize-space(//p[strong[contains(text(), 'Age')]])");
    $rawAge = str_replace('Age:', '', $rawAge);

    $rawDesc = trim($xp->evaluate("string(//div[contains(@class, 'rtecontent')])"));
    
    $rawImages = [];
    foreach($xp->query("//div[contains(@class, 'pet-details-image')]//img/@src") as $img) {
        $rawImages[] = $img->nodeValue;
    }
    
    $cleanImages = extractValidImages($rawImages);
    $primaryImage = $cleanImages[0] ?? '';

    $age = normalizeAge(trim($rawAge));
    $gender = normalizeGender(trim($rawGender));
    $breed = trim($rawBreed) ?: 'Mixed Breed';
    
    $desc = generateForedogBio($name, $breed, $age, $gender, 'Unknown', $rawDesc);

    $res = upsertDog($db, [
        'external_id'    => 'WA-THS-' . $petId,
        'source_shelter' => 'Tacoma Humane',
        'source_url'     => $link,
        'source_state'   => 'washington',
        'name'           => $name,
        'breed_name'     => $breed,
        'location'       => 'Tacoma, WA',
        'city'           => 'Tacoma',
        'state'          => 'WA',
        'age'            => $age,
        'gender'         => $gender,
        'color'          => 'Unknown',
        'description'    => $desc,
        'image_url'      => $primaryImage,
        'gallery_urls'   => json_encode($cleanImages),
        'owner_contact_name'  => 'Humane Society for Tacoma & Pierce County',
        'owner_contact_phone' => '(253) 383-2733',
        'owner_contact_email' => 'adopt@thehumanesociety.org',
    ]);

    if ($res === 'inserted') $s++; elseif ($res === 'updated') $u++; else $e++;
}

$db->prepare("UPDATE dogs SET status='adopted', adopted_at=NOW() WHERE source_shelter='Tacoma Humane' AND status='available' AND last_seen_at < DATE_SUB(NOW(),INTERVAL 48 HOUR)")->execute();

printSummary('Tacoma Humane', $found, $s, $u, $e);
$totIns += $s; $totUpd += $u;

echo "\nWashington total — inserted: $totIns | updated: $totUpd\n";
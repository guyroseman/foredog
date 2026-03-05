<?php
/**
 * Foredog Scraper - California Master
 * Scrapes Pasadena Humane (ShelterLuv API), Wags and Walks, and Muttville.
 */
declare(strict_types=1);
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/scraper_helpers.php'; 

$db = Database::getInstance();
$totIns = 0; $totUpd = 0; $totErr = 0;
echo "\n=== California Scraper ===\n\n";

// =====================================================================
// 1. PASADENA HUMANE (ShelterLuv API)
// =====================================================================
echo "Scanning: Pasadena Humane...\n";

$apiUrl = 'https://www.shelterluv.com/api/v3/available-animals/100001324?species=Dog';
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
    $rawAge = $d['Age'] ?? $d['age'] ?? 12;
    $gender = normalizeGender($d['Sex'] ?? $d['sex'] ?? '');
    $color = $d['Color'] ?? $d['color'] ?? 'Unknown';
    $rawDesc = trim($d['Description'] ?? $d['description'] ?? '');
    $dogId = $d['ID'] ?? $d['id'] ?? uniqid();
    $profileLink = "https://pasadenahumane.org/adopt/view-pets/dogs/#sl_embed&page=shelterluv_wrap_1767068249%2Fembed%2Fanimal%2F{$dogId}";

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
    
    // AGE MATH LOGIC CALLED HERE
    $age = normalizeAge((string)$rawAge);
    $desc = generateForedogBio($name, $breed, $age, $gender, $color, $rawDesc);

    $res = upsertDog($db, [
        'external_id'    => 'PH-' . $dogId,
        'source_shelter' => 'Pasadena Humane',
        'source_url'     => $profileLink,
        'source_state'   => 'california',
        'name'           => $name,
        'breed_name'     => $breed,
        'location'       => 'Pasadena, CA',
        'city'           => 'Pasadena',
        'state'          => 'CA',
        'age'            => $age,
        'gender'         => $gender,
        'color'          => $color,
        'description'    => $desc,
        'image_url'      => $cleanImages[0] ?? '',
        'gallery_urls'   => json_encode($cleanImages),
        'owner_contact_name'  => 'Foredog Matchmaking',
        'owner_contact_phone' => 'N/A',
        'owner_contact_email' => 'hello@foredog.com',
    ]);

    if ($res === 'inserted') $s++; elseif ($res === 'updated') $u++; else $e++;
}

$db->prepare("UPDATE dogs SET status='adopted', adopted_at=NOW() WHERE source_shelter='Pasadena Humane' AND status='available' AND last_seen_at < DATE_SUB(NOW(),INTERVAL 48 HOUR)")->execute();
printSummary('Pasadena Humane', $found, $s, $u, $e);
$totIns += $s; $totUpd += $u;

// =====================================================================
// 2. WAGS AND WALKS
// =====================================================================
echo "Scanning: Wags and Walks...\n";
$indexHtml = fetchUrl('https://www.wagsandwalks.org/adopt-la');
if ($indexHtml) {
    $xp = getXPath($indexHtml);
    $links = [];
    foreach($xp->query("//a[contains(@class, 'summary-thumbnail-container')]/@href") as $node) {
        $links[] = rtrim('https://www.wagsandwalks.org', '/') . '/' . ltrim($node->nodeValue, '/');
    }

    $linksToFetch = array_slice(array_unique($links), 0, 15);
    $profilePages = fetchMultiplePages($linksToFetch);
    $found = count($linksToFetch);
    $s = $u = $e = 0;

    foreach ($profilePages as $link => $html) {
        $xp = getXPath($html);
        $jsonStr = $xp->evaluate("string(//div[contains(@class, 'product-detail')]/@data-context)");
        $data = json_decode($jsonStr, true);
        if (!$data || !isset($data['product'])) continue;
        
        $prod = $data['product'];
        $name = cleanName($prod['title'] ?? '');
        if (!$name) continue;

        $rawDescHtml = $prod['description'] ?? '';
        $plainText = strip_tags(str_ireplace(['<br>', '<br/>', '</p>', '</div>'], "\n", $rawDescHtml));
        preg_match('/Breed:\s*([^\n]+)/i', $plainText, $bMatch);
        preg_match('/Age:\s*([^\n]+)/i', $plainText, $aMatch);
        preg_match('/Gender:\s*([^\n]+)/i', $plainText, $gMatch);

        $rawImages = array_column($prod['images'] ?? [], 'assetUrl');
        $cleanImages = extractValidImages($rawImages); 

        $age = normalizeAge(trim($aMatch[1] ?? ''));
        $desc = generateForedogBio($name, trim($bMatch[1] ?? 'Mixed Breed'), $age, trim($gMatch[1] ?? 'Unknown'), 'Unknown', $plainText);

        $res = upsertDog($db, [
            'external_id'    => $prod['id'] ?? md5($link),
            'source_shelter' => 'Wags and Walks',
            'source_url'     => $link,
            'source_state'   => 'california',
            'name'           => $name,
            'breed_name'     => trim($bMatch[1] ?? 'Mixed Breed'),
            'location'       => 'Los Angeles, CA',
            'city'           => 'Los Angeles',
            'state'          => 'CA',
            'age'            => $age,
            'gender'         => trim($gMatch[1] ?? 'Unknown'),
            'color'          => 'Unknown',
            'description'    => $desc,
            'image_url'      => $cleanImages[0] ?? '',
            'gallery_urls'   => json_encode($cleanImages),
            'owner_contact_name'  => 'Foredog Matchmaking',
            'owner_contact_phone' => 'N/A',
            'owner_contact_email' => 'hello@foredog.com',
        ]);

        if ($res === 'inserted') $s++; elseif ($res === 'updated') $u++; else $e++;
    }
    
    $db->prepare("UPDATE dogs SET status='adopted', adopted_at=NOW() WHERE source_shelter='Wags and Walks' AND status='available' AND last_seen_at < DATE_SUB(NOW(),INTERVAL 48 HOUR)")->execute();
    printSummary('Wags and Walks', $found, $s, $u, $e);
    $totIns += $s; $totUpd += $u;
}

// =====================================================================
// 3. MUTTVILLE SENIOR DOG RESCUE
// =====================================================================
echo "Scanning: Muttville...\n";
$indexHtml = fetchUrl('https://muttville.org/available_mutts');
if ($indexHtml) {
    $xp = getXPath($indexHtml);
    $links = [];
    foreach($xp->query("//article[contains(@class, 'card')]/a/@href") as $node) {
        $links[] = rtrim('https://muttville.org', '/') . '/' . ltrim($node->nodeValue, '/');
    }

    $linksToFetch = array_slice(array_unique($links), 0, 15);
    $profilePages = fetchMultiplePages($linksToFetch);
    $found = count($linksToFetch);
    $s = $u = $e = 0;

    foreach ($profilePages as $link => $html) {
        $xp = getXPath($html);
        $name = cleanName($xp->evaluate("string(//meta[@property='og:title']/@content)"));
        if (!$name) continue;

        $infoBlock = $xp->evaluate("string(//p[contains(text(), 'Est. age')])");
        $rawAge = preg_match('/Est\. age:\s*([^\n\r]+)/i', $infoBlock, $m) ? trim($m[1]) : 'Senior';
        
        $ogDesc = $xp->evaluate("string(//meta[@property='og:description']/@content)");
        $parts = explode('Status: Available.', $ogDesc, 2);
        $metaParts = array_map('trim', explode('|', $parts[0] ?? ''));
        
        $rawImages = [];
        foreach($xp->query("//div[contains(@class, 'slideshow-pics')]//img/@src") as $img) {
            $rawImages[] = str_replace('-med.jpg', '-lg.jpg', $img->nodeValue);
        }
        
        $cleanImages = extractValidImages($rawImages, 'https://muttville.org');

        $age = normalizeAge($rawAge);
        $desc = generateForedogBio($name, $metaParts[0] ?? 'Mixed Breed', $age, $metaParts[1] ?? 'Unknown', 'Unknown', trim($parts[1] ?? $ogDesc));

        $res = upsertDog($db, [
            'external_id'    => md5($link),
            'source_shelter' => 'Muttville Senior Dog Rescue',
            'source_url'     => $link,
            'source_state'   => 'california',
            'name'           => $name,
            'breed_name'     => $metaParts[0] ?? 'Mixed Breed',
            'location'       => 'San Francisco, CA',
            'city'           => 'San Francisco',
            'state'          => 'CA',
            'age'            => $age,
            'gender'         => $metaParts[1] ?? 'Unknown',
            'color'          => 'Unknown',
            'description'    => $desc,
            'image_url'      => $cleanImages[0] ?? '',
            'gallery_urls'   => json_encode($cleanImages),
            'owner_contact_name'  => 'Foredog Matchmaking',
            'owner_contact_phone' => 'N/A',
            'owner_contact_email' => 'hello@foredog.com',
        ]);

        if ($res === 'inserted') $s++; elseif ($res === 'updated') $u++; else $e++;
    }

    $db->prepare("UPDATE dogs SET status='adopted', adopted_at=NOW() WHERE source_shelter='Muttville Senior Dog Rescue' AND status='available' AND last_seen_at < DATE_SUB(NOW(),INTERVAL 48 HOUR)")->execute();
    printSummary('Muttville', $found, $s, $u, $e);
    $totIns += $s; $totUpd += $u;
}

echo "\nCalifornia total — inserted: $totIns | updated: $totUpd\n";
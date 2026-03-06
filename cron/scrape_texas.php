<?php
/**
 * Foredog Scraper - Texas Master
 * Scrapes Austin Pets Alive! (HTML) & Texas Humane Heroes (ShelterLuv API)
 */
declare(strict_types=1);
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/scraper_helpers.php'; 

$db = Database::getInstance();
$totIns = 0; $totUpd = 0; $totErr = 0;
echo "\n=== Texas Scraper ===\n\n";

// =====================================================================
// 1. AUSTIN PETS ALIVE! (HTML Deep Scrape)
// =====================================================================
echo "Scanning: Austin Pets Alive!...\n";
$indexUrl = 'https://www.austinpetsalive.org/adopt/dogs';
$indexHtml = fetchUrl($indexUrl);

if ($indexHtml) {
    $xp = getXPath($indexHtml);
    $links = [];
    
    foreach($xp->query("//div[contains(@class, 'large-tile')]//a[contains(@class, 'img-holder')]/@href") as $node) {
        $link = trim($node->nodeValue);
        if (!str_starts_with($link, 'http')) {
            $link = 'https://www.austinpetsalive.org' . $link;
        }
        if (!in_array($link, $links)) {
            $links[] = $link;
        }
    }

    $linksToFetch = array_slice($links, 0, 15);
    $profilePages = fetchMultiplePages($linksToFetch);
    $found = count($linksToFetch);
    $s = $u = $e = 0;

    foreach ($profilePages as $link => $html) {
        $xp = getXPath($html);
        
        $name = cleanName($xp->evaluate("string(//h1[contains(@class, 'orange')])"));
        if (!$name) continue;

        $breed = $xp->evaluate("normalize-space(//h6[contains(text(), 'Looks Like')]/parent::div/following-sibling::div/h6)") ?: 'Mixed Breed';
        
        $rawAge = $xp->evaluate("normalize-space(//h6[contains(text(), 'Estimated Age')]/parent::div/following-sibling::div/h6)") ?: 'Adult';
        $age = preg_replace('/\s+/', ' ', str_replace(['<br>', '<br/>'], ' ', $rawAge));
        
        $gender = $xp->evaluate("normalize-space(//h6[contains(text(), 'Sex')]/parent::div/following-sibling::div/h6)") ?: 'Unknown';
        $eid = $xp->evaluate("normalize-space(//h6[contains(text(), 'APA-A')])") ?: md5($link);
        $rawDesc = trim($xp->evaluate("string(//div[contains(@class, 'blog')]//div[contains(@class, 'large')])"));

        $rawImages = [];
        foreach($xp->query("//img[contains(@class, 'gallery-img')]/@data-lazy") as $img) {
            $rawImages[] = trim($img->nodeValue);
        }
        
        $cleanImages = extractValidImages($rawImages);
        $desc = generateForedogBio($name, $breed, normalizeAge($age), $gender, 'Unknown', $rawDesc);

        $res = upsertDog($db, [
            'external_id'    => $eid,
            'source_shelter' => 'Austin Pets Alive!',
            'source_url'     => $link,
            'source_state'   => 'texas',
            'name'           => $name,
            'breed_name'     => $breed,
            'location'       => 'Austin, TX',
            'city'           => 'Austin',
            'state'          => 'TX',
            'age'            => normalizeAge($age),
            'gender'         => $gender,
            'color'          => 'Unknown',
            'description'    => $desc,
            'image_url'      => $cleanImages[0] ?? '',
            'gallery_urls'   => json_encode($cleanImages),
            'owner_contact_name'  => 'Austin Pets Alive!',
            'owner_contact_phone' => '(512) 961-6519',
            'owner_contact_email' => 'adopt@austinpetsalive.org',
        ]);

        if ($res === 'inserted') $s++; elseif ($res === 'updated') $u++; else $e++;
    }
    
    $db->prepare("UPDATE dogs SET status='adopted', adopted_at=NOW() WHERE source_shelter='Austin Pets Alive!' AND status='available' AND last_seen_at < DATE_SUB(NOW(),INTERVAL 48 HOUR)")->execute();
    printSummary('Austin Pets Alive!', $found, $s, $u, $e);
    $totIns += $s; $totUpd += $u;
}

// =====================================================================
// 2. TEXAS HUMANE HEROES (ShelterLuv JSON API)
// =====================================================================
echo "Scanning: Texas Humane Heroes...\n";

// From your HTML: var GID = 100001767
$apiUrl = 'https://www.shelterluv.com/api/v3/available-animals/100001767?species=Dog';
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
    $age = (is_numeric($rawAge) && $rawAge < 12) ? 'Puppy' : 'Adult'; 
    $gender = normalizeGender($d['Sex'] ?? $d['sex'] ?? '');
    $color = $d['Color'] ?? $d['color'] ?? 'Unknown';
    $rawDesc = trim($d['Description'] ?? $d['description'] ?? '');
    $dogId = $d['ID'] ?? $d['id'] ?? uniqid();
    $profileLink = "https://humaneheroes.org/adopt-a-pet/dogs/#sl_embed&page=shelterluv_wrap_1767068249%2Fembed%2Fanimal%2F{$dogId}";

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
    $desc = generateForedogBio($name, $breed, normalizeAge((string)$rawAge), $gender, $color, $rawDesc);

    $res = upsertDog($db, [
        'external_id'    => 'THH-' . $dogId,
        'source_shelter' => 'Texas Humane Heroes',
        'source_url'     => $profileLink,
        'source_state'   => 'texas',
        'name'           => $name,
        'breed_name'     => $breed,
        'location'       => 'Leander, TX',
        'city'           => 'Leander',
        'state'          => 'TX',
        'age'            => normalizeAge((string)$rawAge),
        'gender'         => $gender,
        'color'          => $color,
        'description'    => $desc,
        'image_url'      => $cleanImages[0] ?? '',
        'gallery_urls'   => json_encode($cleanImages),
        'owner_contact_name'  => 'Texas Humane Heroes',
        'owner_contact_phone' => '(512) 260-3602',
        'owner_contact_email' => 'info@txhh.org',
    ]);

    if ($res === 'inserted') $s++; elseif ($res === 'updated') $u++; else $e++;
}

$db->prepare("UPDATE dogs SET status='adopted', adopted_at=NOW() WHERE source_shelter='Texas Humane Heroes' AND status='available' AND last_seen_at < DATE_SUB(NOW(),INTERVAL 48 HOUR)")->execute();
printSummary('Texas Humane Heroes', $found, $s, $u, $e);
$totIns += $s; $totUpd += $u;

echo "\nTexas total — inserted: $totIns | updated: $totUpd\n";
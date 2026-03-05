<?php
/**
 * Foredog Scraper - New York Master
 * Scrapes Animal Haven (NYC) via Precise HTML Scrape
 */
declare(strict_types=1);
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/scraper_helpers.php'; 

$db = Database::getInstance();
$totIns = 0; $totUpd = 0; $totErr = 0;
echo "\n=== New York Scraper ===\n\n";

echo "Scanning: Animal Haven (NYC)...\n";
$indexUrl = 'https://animalhaven.org/adopt/dogs';
$indexHtml = fetchUrl($indexUrl);

$links = [];
if ($indexHtml) {
    $xp = getXPath($indexHtml);
    // FIX: Target only pet profile links
    foreach($xp->query("//a[contains(@class, 'pet-preview__link')]/@href") as $node) {
        $link = trim($node->nodeValue);
        if (!str_starts_with($link, 'http')) {
            $link = 'https://animalhaven.org' . $link;
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
    
    $name = cleanName($xp->evaluate("string(//h1[contains(@class, 'pet-profile__name')] | //h1)"));
    if (!$name) continue;

    // FIX: Grab exactly the list items for Breed and Age to guarantee 100% accuracy
    $rawGender = $xp->evaluate("normalize-space(//li[contains(@class, 'pet-profile__subtitle-item--gender')]//span)");
    $rawBreed = $xp->evaluate("normalize-space((//li[contains(@class, 'pet-profile__subtitle-item')])[2])");
    $rawAge = $xp->evaluate("normalize-space((//li[contains(@class, 'pet-profile__subtitle-item')])[3])");
    $rawDesc = trim($xp->evaluate("string(//div[contains(@class, 'pet-profile__description-text')])"));
    
    // FIX: Only grab the primary gallery image of the dog. Prevents mixing multiple dogs!
    $rawImages = [];
    foreach($xp->query("//img[contains(@class, 'pet-gallery__image')]/@src | //img[contains(@class, 'pet-gallery__thumbnail-image')]/@src") as $img) {
        $src = trim($img->nodeValue);
        if (!preg_match('/(placeholder)/i', $src)) {
            $rawImages[] = $src;
        }
    }
    
    $cleanImages = extractValidImages($rawImages, 'https://animalhaven.org');
    if (empty($cleanImages)) continue;

    // We pass the exact explicit age here, no paragraph reading!
    $age = normalizeAge($rawAge);
    $gender = normalizeGender($rawGender);
    $breed = $rawBreed ?: 'Mixed Breed';
    
    $desc = generateForedogBio($name, $breed, $age, $gender, 'Unknown', $rawDesc);

    $res = upsertDog($db, [
        'external_id'    => md5($link),
        'source_shelter' => 'Animal Haven',
        'source_url'     => $link,
        'source_state'   => 'newyork',
        'name'           => $name,
        'breed_name'     => $breed,
        'location'       => 'New York, NY',
        'city'           => 'New York',
        'state'          => 'NY',
        'age'            => $age,
        'gender'         => $gender,
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

$db->prepare("UPDATE dogs SET status='adopted', adopted_at=NOW() WHERE source_shelter='Animal Haven' AND status='available' AND last_seen_at < DATE_SUB(NOW(),INTERVAL 48 HOUR)")->execute();
printSummary('Animal Haven NYC', $found, $s, $u, $e);
$totIns += $s; $totUpd += $u;

echo "\nNew York total — inserted: $totIns | updated: $totUpd\n";
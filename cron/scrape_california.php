<?php
/**
 * Foredog Scraper - PREMIUM BIO EDITION
 * Ultra-fast concurrent fetching, strict data filters, and seamless Foredog Bios.
 */
declare(strict_types=1);
require_once __DIR__ . '/../src/Database.php';
$db = Database::getInstance();

$shelters = [
    [
        'name'       => 'Pasadena Humane',
        'location'   => 'Pasadena, CA',
        'index_url'  => 'https://pasadenahumane.org/adopt/view-pets/dogs/',
        'contact'    => ['name'=>'Pasadena Humane', 'phone'=>'(626) 792-7151', 'email'=>'hello@pasadenahumane.org'],
        'link_xpath' => "//a[contains(@class, 'woocommerce-LoopProduct-link')]/@href",
        'type'       => 'pasadena'
    ],
    [
        'name'       => 'Wags and Walks',
        'location'   => 'Los Angeles, CA',
        'index_url'  => 'https://www.wagsandwalks.org/adopt-la',
        'contact'    => ['name'=>'Wags and Walks', 'phone'=>'N/A', 'email'=>'info@wagsandwalks.org'],
        'link_xpath' => "//a[contains(@class, 'summary-thumbnail-container')]/@href",
        'base_url'   => 'https://www.wagsandwalks.org',
        'type'       => 'wags'
    ],
    [
        'name'       => 'Muttville Senior Dog Rescue',
        'location'   => 'San Francisco, CA',
        'index_url'  => 'https://muttville.org/available_mutts',
        'contact'    => ['name'=>'Muttville', 'phone'=>'(415) 272-4172', 'email'=>'info@muttville.org'],
        'link_xpath' => "//article[contains(@class, 'card')]/a/@href",
        'base_url'   => 'https://muttville.org',
        'type'       => 'muttville'
    ]
];

// ============================================================
// HELPERS & FORMATTERS
// ============================================================
function cleanName(string $raw): string { 
    $cleaned = ucwords(strtolower(trim(preg_replace('/[*#@!]+/', '', $raw)))); 
    // Kill Switch: Destroy dogs with fake names
    if (empty($cleaned) || strtolower($cleaned) === 'null' || strtolower($cleaned) === 'unknown' || strtolower($cleaned) === 'no name') {
        return '';
    }
    return $cleaned;
}

function normalizeGender(string $raw): string {
    $r = strtolower(trim($raw));
    if (str_contains($r, 'f') || str_contains($r, 'spayed')) return 'Female';
    if (str_contains($r, 'm') || str_contains($r, 'neutered')) return 'Male';
    return 'Unknown';
}

function breedSlug(string $raw): string {
    $r = strtolower(trim($raw));
    $r = preg_replace('/\s*\/.*$/', '', $r);
    $r = preg_replace('/\s*(mix|mixed|breed|shorthair|longhair|medium hair)$/i', '', trim($r));
    $map = ['german shepherd'=>'german-shepherd', 'golden retriever'=>'golden-retriever', 'labrador'=>'labrador-retriever', 'french bulldog'=>'french-bulldog', 'husky'=>'siberian-husky', 'poodle'=>'poodle', 'rottweiler'=>'rottweiler', 'dachshund'=>'dachshund', 'bulldog'=>'english-bulldog', 'australian shepherd'=>'australian-shepherd', 'chihuahua'=>'chihuahua', 'pit bull'=>'pit-bull', 'pug'=>'pug', 'beagle'=>'beagle', 'boxer'=>'boxer'];
    foreach ($map as $k => $s) { if (str_contains($r, $k)) return $s; }
    return strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($r, '-')));
}

// THE PREMIUM BIO SYNTHESIZER
// Blends raw shelter data into a seamless, proprietary Foredog story
function generateForedogBio($name, $breed, $age, $gender, $color, $rawShelterDesc): string {
    $intros = [
        "Meet $name, an exceptional match waiting to find their forever home.",
        "We are thrilled to introduce you to $name, one of our featured rescue companions.",
        "Looking for your perfect match? $name might just be the one you've been searching for."
    ];
    
    $middles = [
        "This beautiful $breed is currently $age, making them the perfect age to bond with a new family.",
        "As a $age $gender $breed, $name has so much love and personality to share.",
        "Don't let that sweet face fool you—this $gender is full of character and ready for their next adventure!"
    ];

    $foredogIntro = "<strong>" . $intros[array_rand($intros)] . " " . $middles[array_rand($middles)] . "</strong>";
    
    // Clean up the shelter description
    $cleanShelterDesc = trim(strip_tags($rawShelterDesc));
    
    // Build the seamless narrative (No headers, no external links!)
    $finalBio = $foredogIntro . "<br><br>";

    if (!empty($cleanShelterDesc) && strlen($cleanShelterDesc) > 20) {
        $finalBio .= $cleanShelterDesc . "<br><br>";
    }

    $outros = [
        "If your lifestyle aligns with $name's needs, subscribe today to unlock their direct adoption details and schedule a meet-and-greet!",
        "Ready to take the next step? Unlock $name's contact details and start your adoption journey today.",
        "Don't let this perfect match slip away. Subscribe to access the shelter's information and bring $name home."
    ];

    $finalBio .= "<em>" . $outros[array_rand($outros)] . "</em>";

    return $finalBio;
}

function getXPath($html) {
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();
    return new DOMXPath($dom);
}

// ============================================================
// MULTI-CURL (CONCURRENT FETCHING)
// ============================================================
function fetchMultiplePages(array $urls): array {
    $mh = curl_multi_init();
    $ch_list = [];
    $results = [];

    foreach ($urls as $url) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 15, CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0.0.0 Safari/537.36'
        ]);
        curl_multi_add_handle($mh, $ch);
        $ch_list[$url] = $ch;
    }

    $active = null;
    do { $mrc = curl_multi_exec($mh, $active); } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    while ($active && $mrc == CURLM_OK) {
        if (curl_multi_select($mh) != -1) {
            do { $mrc = curl_multi_exec($mh, $active); } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }

    foreach ($ch_list as $url => $ch) {
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
            $results[$url] = curl_multi_getcontent($ch);
        }
        curl_multi_remove_handle($mh, $ch);
    }
    return $results;
}

// ============================================================
// DEEP PARSERS
// ============================================================
function parsePasadena($xp, $url) {
    $name = cleanName($xp->evaluate("string(//h1[contains(@class, 'product_title')])"));
    $breed = $xp->evaluate("string(//td[strong[contains(text(), 'Looks Like')]]/following-sibling::td)") ?: 'Mixed Breed';
    $age = $xp->evaluate("string(//td[strong[contains(text(), 'Age')]]/following-sibling::td)") ?: 'Adult';
    $gender = $xp->evaluate("string(//td[strong[contains(text(), 'Gender')]]/following-sibling::td)") ?: 'Unknown';
    $color = $xp->evaluate("string(//td[strong[contains(text(), 'Color')]]/following-sibling::td)") ?: 'Unknown';
    $eid = $xp->evaluate("string(//td[strong[contains(text(), 'Animal ID')]]/following-sibling::td)") ?: md5($url);
    $desc = trim($xp->evaluate("string(//div[@class='longdescription'])"));

    $images = [];
    foreach($xp->query("//ul[@id='image-gallery']//img/@src | //meta[@property='og:image']/@content") as $img) {
        $src = $img->nodeValue;
        if (str_contains($src, '.svg') || str_contains(strtolower($src), 'logo')) continue;
        if (!in_array($src, $images)) $images[] = $src;
    }
    if (empty($name)) return null;
    return ['eid'=>$eid, 'name'=>$name, 'breed'=>$breed, 'age'=>$age, 'gender'=>$gender, 'color'=>$color, 'desc'=>$desc, 'images'=>$images];
}

function parseWags($xp, $url) {
    $jsonStr = $xp->evaluate("string(//div[contains(@class, 'product-detail')]/@data-context)");
    $data = json_decode($jsonStr, true);
    if (!$data || !isset($data['product'])) return null;
    
    $prod = $data['product'];
    $name = cleanName($prod['title'] ?? '');
    $eid = $prod['id'] ?? md5($url);
    $rawDescHtml = $prod['description'] ?? '';
    
    preg_match('/Breed:\s*([^<]+)/i', strip_tags($rawDescHtml), $bMatch);
    preg_match('/Age:\s*([^<]+)/i', strip_tags($rawDescHtml), $aMatch);
    preg_match('/Gender:\s*([^<]+)/i', strip_tags($rawDescHtml), $gMatch);
    
    $images = [];
    if (isset($prod['images'])) { 
        foreach($prod['images'] as $img) { 
            if (str_contains($img['assetUrl'], 'placeholder')) continue;
            $images[] = $img['assetUrl']; 
        } 
    }
    
    if (empty($name)) return null;
    return ['eid'=>$eid, 'name'=>$name, 'breed'=>trim($bMatch[1] ?? 'Mixed Breed'), 'age'=>trim($aMatch[1] ?? 'Adult'), 'gender'=>trim($gMatch[1] ?? 'Unknown'), 'color'=>'Unknown', 'desc'=>strip_tags($rawDescHtml), 'images'=>$images];
}

function parseMuttville($xp, $url) {
    $name = cleanName($xp->evaluate("string(//meta[@property='og:title']/@content)"));
    $eid = md5($url); 
    
    $infoBlock = $xp->evaluate("string(//p[contains(text(), 'Est. age')])");
    $age = 'Senior'; 
    if (preg_match('/Est\. age:\s*([^\n\r]+)/i', $infoBlock, $m)) {
        $age = trim($m[1]);
    }
    
    $ogDesc = $xp->evaluate("string(//meta[@property='og:description']/@content)");
    $parts = explode('Status: Available.', $ogDesc, 2);
    $metaParts = array_map('trim', explode('|', $parts[0] ?? ''));
    $desc = trim($parts[1] ?? $ogDesc);
    
    $images = [];
    foreach($xp->query("//div[contains(@class, 'slideshow-pics')]//img/@src") as $img) {
        $src = $img->nodeValue;
        if (!str_contains($src, '/mutts/')) continue;
        
        $src = str_replace('-med.jpg', '-lg.jpg', $src); 
        if (!str_starts_with($src, 'http')) $src = 'https://muttville.org' . $src;
        if (!in_array($src, $images)) $images[] = $src;
    }
    
    if (empty($name)) return null;
    return ['eid'=>$eid, 'name'=>$name, 'breed'=>$metaParts[0] ?? 'Mixed Breed', 'age'=>$age, 'gender'=>$metaParts[1] ?? 'Unknown', 'color'=>'Unknown', 'desc'=>$desc, 'images'=>$images];
}

// ============================================================
// MAIN LOOP
// ============================================================
$totIns = 0; $totUpd = 0; $totErr = 0;
echo "\n=== Foredog Deep Scraper [" . date('Y-m-d H:i:s') . "] ===\n\n";

foreach ($shelters as $shelter) {
    echo "Scanning Index: {$shelter['name']}...\n";
    $pages = fetchMultiplePages([$shelter['index_url']]);
    $indexHtml = $pages[$shelter['index_url']] ?? null;
    if (!$indexHtml) { echo "  [ERROR] Failed to load index.\n\n"; continue; }

    $xp = getXPath($indexHtml);
    $links = [];
    foreach($xp->query($shelter['link_xpath']) as $node) {
        $link = $node->nodeValue;
        if (!str_starts_with($link, 'http') && isset($shelter['base_url'])) {
            $link = rtrim($shelter['base_url'], '/') . '/' . ltrim($link, '/');
        }
        if (!in_array($link, $links)) $links[] = $link;
    }

    $linksToFetch = array_slice($links, 0, 15);
    echo "  Found " . count($links) . " profiles. Initiating concurrent fetch for " . count($linksToFetch) . " dogs...\n";
    
    $t0 = microtime(true);
    $profilePages = fetchMultiplePages($linksToFetch);
    $s = $u = 0;

    foreach ($profilePages as $link => $profileHtml) {
        $profileXp = getXPath($profileHtml);

        if ($shelter['type'] === 'pasadena') $dog = parsePasadena($profileXp, $link);
        elseif ($shelter['type'] === 'wags') $dog = parseWags($profileXp, $link);
        elseif ($shelter['type'] === 'muttville') $dog = parseMuttville($profileXp, $link);

        if (!$dog) continue;

        $primaryImg = $dog['images'][0] ?? '';
        $galleryJson = json_encode($dog['images']);
        
        // Generate the Proprietary Foredog Bio
        $finalDescription = generateForedogBio($dog['name'], $dog['breed'], $dog['age'], $dog['gender'], $dog['color'], $dog['desc']);

        $dogData = [
            ':eid'    => $dog['eid'],
            ':shelter'=> $shelter['name'],
            ':surl'   => $link,
            ':name'   => $dog['name'],
            ':bslug'  => breedSlug($dog['breed']),
            ':bname'  => ucwords(strtolower($dog['breed'])),
            ':loc'    => $shelter['location'],
            ':age'    => $dog['age'],
            ':gender' => normalizeGender($dog['gender']),
            ':color'  => $dog['color'],
            ':desc'   => $finalDescription,
            ':img'    => $primaryImg,
            ':gallery'=> $galleryJson,
            ':cname'  => $shelter['contact']['name'],
            ':cphone' => $shelter['contact']['phone'],
            ':cemail' => $shelter['contact']['email'],
        ];

        try {
            $stmt = $db->prepare("INSERT INTO dogs
                (external_id,source_shelter,source_url,name,breed_slug,breed_name,location,age,gender,color,description,image_url,gallery_urls,owner_contact_name,owner_contact_phone,owner_contact_email,status,last_seen_at)
                VALUES(:eid,:shelter,:surl,:name,:bslug,:bname,:loc,:age,:gender,:color,:desc,:img,:gallery,:cname,:cphone,:cemail,'available',NOW())
                ON DUPLICATE KEY UPDATE
                name=VALUES(name), breed_name=VALUES(breed_name), breed_slug=VALUES(breed_slug), age=VALUES(age), description=VALUES(description), image_url=VALUES(image_url), gallery_urls=VALUES(gallery_urls), source_url=VALUES(source_url), status='available', last_seen_at=NOW()");
            $stmt->execute($dogData);
            
            if ($stmt->rowCount() === 1) $s++;
            elseif ($stmt->rowCount() === 2) $u++;
        } catch(Exception $e) { $totErr++; }
    }
    
    $rm = $db->prepare("UPDATE dogs SET status='adopted', adopted_at=NOW() WHERE source_shelter=? AND status='available' AND last_seen_at < DATE_SUB(NOW(),INTERVAL 48 HOUR)");
    $rm->execute([$shelter['name']]);
    
    $totIns += $s; $totUpd += $u;
    echo "  -> Added: $s | Updated: $u | Time: " . round(microtime(true)-$t0, 2) . "s\n\n";
}
echo "=== Scrape Complete ===\n";
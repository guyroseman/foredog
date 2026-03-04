<?php
/**
 * Foredog California Scraper
 * Sources: Open Data portals (Socrata + custom APIs) for CA cities
 * Run: php cron/scrape_california.php
 * Cron: 0 6,18 * * * /usr/bin/php /path/to/foredog/cron/scrape_california.php >> /var/log/foredog_ca.log 2>&1
 */
declare(strict_types=1);
require_once __DIR__ . '/../src/Database.php';
$db = Database::getInstance();

// ============================================================
// CALIFORNIA SHELTER SOURCES
// All confirmed working open data endpoints
// ============================================================
$shelters = [

    // ----------------------------------------------------------
    // LONG BEACH — Confirmed working ✅
    // Open data portal, returns clean JSON
    // Gets CURRENTLY available dogs (outcome_is_current = True)
    // ----------------------------------------------------------
    [
        'name'     => 'Long Beach Animal Care Services',
        'location' => 'Long Beach, CA',
        'contact'  => [
            'name'  => 'Long Beach Animal Care Services',
            'phone' => '(562) 570-7387',
            'email' => 'animalcare@longbeach.gov',
        ],
        'url'      => 'https://data.longbeach.gov/api/explore/v2.1/catalog/datasets/animal-shelter-intakes-and-outcomes/records?where=animal_type%3D%27DOG%27%20AND%20outcome_is_current%3D%27True%27&limit=100',
        'mode'     => 'longbeach',
    ],

    // ----------------------------------------------------------
    // LOS ANGELES CITY — Socrata open data portal
    // Status: Test pending — add after confirmed
    // ----------------------------------------------------------
    [
        'name'     => 'LA Animal Services',
        'location' => 'Los Angeles, CA',
        'contact'  => [
            'name'  => 'LA Animal Services',
            'phone' => '(888) 452-7381',
            'email' => 'laas@lacity.org',
        ],
        'url'      => 'https://data.lacity.org/resource/8cmr-fbcu.json?$limit=500&$where=animal_type=%27DOG%27%20AND%20outcome_type=%27ADOPTION%27',
        'mode'     => 'socrata_lacity',
    ],

    // ----------------------------------------------------------
    // AUSTIN TX — Already confirmed working ✅
    // Keeping here for reference — also in main scraper
    // ----------------------------------------------------------
    // [
    //     'name'  => 'Austin Animal Center',
    //     'url'   => 'https://data.austintexas.gov/resource/9t4d-g238.json?animal_type=Dog&outcome_type=Transfer&$limit=100',
    //     'mode'  => 'socrata_austin',
    // ],

];

// ============================================================
// HELPERS
// ============================================================
function cleanName(string $raw): string {
    return ucwords(strtolower(trim(preg_replace('/[*#@!]+/', '', $raw))));
}

function breedSlug(string $raw): string {
    $r = strtolower(trim($raw));
    $r = preg_replace('/\s*\/.*$/', '', $r);
    $r = preg_replace('/\s*(mix|mixed|breed|shorthair|longhair|medium hair)$/i', '', trim($r));
    $r = trim($r);
    $map = [
        'german shepherd'=>'german-shepherd','golden retriever'=>'golden-retriever',
        'labrador retriever'=>'labrador-retriever','labrador'=>'labrador-retriever','lab'=>'labrador-retriever',
        'french bulldog'=>'french-bulldog','siberian husky'=>'siberian-husky','husky'=>'siberian-husky',
        'poodle'=>'poodle','rottweiler'=>'rottweiler','dachshund'=>'dachshund',
        'bulldog'=>'english-bulldog','english bulldog'=>'english-bulldog',
        'australian shepherd'=>'australian-shepherd','aussie'=>'australian-shepherd',
        'border collie'=>'border-collie','doberman'=>'doberman','doberman pinscher'=>'doberman',
        'yorkshire terrier'=>'yorkshire-terrier','yorkie'=>'yorkshire-terrier',
        'chihuahua'=>'chihuahua','pit bull'=>'pit-bull','pug'=>'pug',
        'beagle'=>'beagle','boxer'=>'boxer','shih tzu'=>'shih-tzu',
        'miniature pinscher'=>'miniature-pinscher','great dane'=>'great-dane',
        'corgi'=>'corgi','maltese'=>'maltese','schnauzer'=>'schnauzer',
        'havanese'=>'havanese','shiba inu'=>'shiba-inu',
    ];
    if (isset($map[$r])) return $map[$r];
    foreach ($map as $k => $s) { if (str_contains($r, $k)) return $s; }
    return strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($r, '-')));
}

function normalizeGender(string $raw): string {
    $r = strtolower(trim($raw));
    if (str_contains($r, 'female') || str_contains($r, 'spayed') || $r === 'f') return 'Female';
    if (str_contains($r, 'male')   || str_contains($r, 'neutered') || $r === 'm') return 'Male';
    return 'Unknown';
}

function fetchJson(string $url): array {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    return ['body' => $body, 'code' => $code, 'error' => $err];
}

function upsertDog(PDO $db, array $d): string {
    $stmt = $db->prepare("INSERT INTO dogs
        (external_id,source_shelter,source_url,name,breed_slug,breed_name,location,age,gender,color,description,image_url,owner_contact_name,owner_contact_phone,owner_contact_email,status,last_seen_at)
        VALUES(:eid,:shelter,:surl,:name,:bslug,:bname,:loc,:age,:gender,:color,:desc,:img,:cname,:cphone,:cemail,'available',NOW())
        ON DUPLICATE KEY UPDATE
            name=VALUES(name),breed_slug=VALUES(breed_slug),breed_name=VALUES(breed_name),
            age=VALUES(age),color=VALUES(color),description=VALUES(description),
            image_url=VALUES(image_url),status='available',last_seen_at=NOW()");
    $stmt->execute($d);
    return match((int)$stmt->rowCount()) { 1=>'inserted', 2=>'updated', default=>'unchanged' };
}

// ============================================================
// PARSERS — one per data format
// ============================================================

function parseLongBeach(array $data, array $shelter): array {
    $dogs = [];
    $items = $data['results'] ?? [];
    foreach ($items as $item) {
        $name = cleanName($item['animal_name'] ?? '');
        $eid  = $item['animal_id'] ?? '';
        if (empty($eid) || empty($name) || $name === 'Unknown') continue;

        // Calculate age from DOB
        $age = 'Unknown';
        if (!empty($item['dob'])) {
            $dob  = new DateTime($item['dob']);
            $now  = new DateTime();
            $diff = $now->diff($dob);
            $age  = $diff->y > 0 ? $diff->y . ' year' . ($diff->y > 1 ? 's' : '') : $diff->m . ' months';
        }

        $color = ucwords(strtolower(trim(($item['primary_color'] ?? '') . (isset($item['secondary_color']) && $item['secondary_color'] !== 'NULL' ? ' & ' . $item['secondary_color'] : ''))));

        $dogs[] = [
            ':eid'    => $eid,
            ':shelter'=> $shelter['name'],
            ':surl'   => $shelter['url'],
            ':name'   => $name,
            ':bslug'  => breedSlug($item['breed'] ?? $item['primary_breed'] ?? ''),
            ':bname'  => ucwords(strtolower($item['breed'] ?? $item['primary_breed'] ?? 'Unknown')),
            ':loc'    => $shelter['location'],
            ':age'    => $age,
            ':gender' => normalizeGender($item['sex'] ?? ''),
            ':color'  => $color,
            ':desc'   => '',
            ':img'    => '',
            ':cname'  => $shelter['contact']['name'],
            ':cphone' => $shelter['contact']['phone'],
            ':cemail' => $shelter['contact']['email'],
        ];
    }
    return $dogs;
}

function parseSocrataLacity(array $data, array $shelter): array {
    $dogs = [];
    foreach ($data as $item) {
        $name = cleanName($item['animal_name'] ?? '');
        $eid  = $item['animal_id'] ?? '';
        if (empty($eid) || empty($name)) continue;
        $dogs[] = [
            ':eid'    => $eid,
            ':shelter'=> $shelter['name'],
            ':surl'   => $shelter['url'],
            ':name'   => $name,
            ':bslug'  => breedSlug($item['breed'] ?? ''),
            ':bname'  => ucwords(strtolower($item['breed'] ?? 'Unknown')),
            ':loc'    => $shelter['location'],
            ':age'    => $item['age'] ?? '',
            ':gender' => normalizeGender($item['sex'] ?? ''),
            ':color'  => ucwords(strtolower($item['color'] ?? '')),
            ':desc'   => '',
            ':img'    => '',
            ':cname'  => $shelter['contact']['name'],
            ':cphone' => $shelter['contact']['phone'],
            ':cemail' => $shelter['contact']['email'],
        ];
    }
    return $dogs;
}

// ============================================================
// MAIN LOOP
// ============================================================
$totIns = 0; $totUpd = 0; $totErr = 0;
echo "\n=== Foredog California Scraper " . date('Y-m-d H:i:s') . " ===\n\n";

foreach ($shelters as $shelter) {
    $t0 = microtime(true); $s = $u = 0;
    echo "[ {$shelter['name']} ]\n";
    sleep(1);

    $res = fetchJson($shelter['url']);

    if (!$res['body'] || $res['code'] !== 200) {
        echo "  ERROR: HTTP {$res['code']} {$res['error']}\n\n";
        $db->prepare("INSERT INTO scrape_log (shelter_name,shelter_url,status,error_msg,duration_sec) VALUES(?,?,'error',?,?)")
           ->execute([$shelter['name'], $shelter['url'], "HTTP {$res['code']}", round(microtime(true)-$t0,2)]);
        $totErr++; continue;
    }

    $data = json_decode($res['body'], true);
    if (!$data) {
        echo "  ERROR: Invalid JSON\n\n";
        $totErr++; continue;
    }

    // Route to correct parser
    $dogs = match($shelter['mode']) {
        'longbeach'      => parseLongBeach($data, $shelter),
        'socrata_lacity' => parseSocrataLacity($data, $shelter),
        default          => [],
    };

    $f = count($dogs);
    echo "  Found: $f dogs\n";

    foreach ($dogs as $dog) {
        try {
            $r = upsertDog($db, $dog);
            if ($r === 'inserted') $s++;
            elseif ($r === 'updated') $u++;
        } catch (Exception $e) {
            echo "  DB Error: " . $e->getMessage() . "\n";
            $totErr++;
        }
    }

    // Mark dogs from this shelter not seen in 48h as adopted
    if ($f > 0) {
        $rm = $db->prepare("UPDATE dogs SET status='adopted',adopted_at=NOW() WHERE source_shelter=? AND status='available' AND last_seen_at < DATE_SUB(NOW(),INTERVAL 48 HOUR)");
        $rm->execute([$shelter['name']]);
        if ($rm->rowCount() > 0) echo "  Marked adopted: " . $rm->rowCount() . "\n";
    }

    $db->prepare("INSERT INTO scrape_log (shelter_name,shelter_url,dogs_found,dogs_added,dogs_updated,status,duration_sec) VALUES(?,?,?,?,?,'success',?)")
       ->execute([$shelter['name'], $shelter['url'], $f, $s, $u, round(microtime(true)-$t0,2)]);

    $totIns += $s; $totUpd += $u;
    echo "  Inserted: $s | Updated: $u | Time: " . round(microtime(true)-$t0,1) . "s\n\n";
}

echo "=== Done — Inserted: $totIns | Updated: $totUpd | Errors: $totErr ===\n\n";

<?php
/**
 * Foredog Scraper - Illinois Master
 * Scrapes Illinois ShelterLuv Rescues
 */
declare(strict_types=1);
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/scraper_helpers.php';

$db = Database::getInstance();

$totIns = 0; 
$totUpd = 0;
$totErr = 0;

echo "\n=== Illinois Scraper ===\n\n";

// =====================================================================
// ILLINOIS SHELTERLUV RESCUES
// =====================================================================
$illinois_sl_shelters = [
    [
        'name'  => 'Tails Humane Society',
        'gid'   => '100000307', 
        'city'  => 'DeKalb',
        'phone' => '(815) 758-2457',
        'email' => 'info@tailshumanesociety.org'
    ],
    [
        'name'  => 'Border Tails Rescue',
        'gid'   => '45923', 
        'city'  => 'Northbrook',
        'phone' => '(847) 813-5774',
        'email' => 'adopt@bordertailsrescue.org'
    ]
];

foreach ($illinois_sl_shelters as $shelter) {
    echo "Scanning: {$shelter['name']}...\n";
    
    $api_url = "https://www.shelterluv.com/api/v3/available-animals/{$shelter['gid']}?species=Dog";
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'User-Agent: Mozilla/5.0'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($http_code === 200 && $response) {
        $data = json_decode($response, true);
        
        if (isset($data['animals']) && is_array($data['animals'])) {
            $sl_found = count($data['animals']); 
            $sl_ins = 0; $sl_upd = 0; $sl_err = 0;
            
            foreach ($data['animals'] as $animal) {
                // Skip Pending dogs
                $status = $animal['Status'] ?? $animal['status'] ?? '';
                if (strpos(strtolower($status), 'pending') !== false) continue;
                
                // Account for uppercase AND lowercase keys from the API
                $rawName = $animal['Name'] ?? $animal['name'] ?? '';
                $name = cleanName($rawName);
                if (!$name) continue;

                $breed = $animal['Breed'] ?? $animal['breed'] ?? 'Mixed Breed';
                $gender = normalizeGender($animal['Sex'] ?? $animal['sex'] ?? '');
                $rawAge = $animal['Age'] ?? $animal['age'] ?? 12;
                $age = normalizeAge((string)$rawAge);
                $color = $animal['Color'] ?? $animal['color'] ?? 'Unknown';
                $rawWeight = $animal['Weight'] ?? $animal['weight'] ?? 'Unknown';
                $weight = $rawWeight !== 'Unknown' ? $rawWeight . ' lbs' : 'Unknown';
                
                $rawDesc = trim(strip_tags($animal['Description'] ?? $animal['description'] ?? ''));
                $desc = generateForedogBio($name, $breed, $age, $gender, $color, $rawDesc);
                
                $image_url = $animal['CoverPhoto'] ?? $animal['coverPhoto'] ?? '';
                $animalId = $animal['ID'] ?? $animal['id'] ?? uniqid();
                
                // Format Images array
                $rawPhotos = $animal['Photos'] ?? $animal['photos'] ?? [];
                $photoUrls = [];
                foreach ($rawPhotos as $p) {
                    if (is_string($p)) { $photoUrls[] = $p; }
                    elseif (is_array($p) && isset($p['url'])) { $photoUrls[] = $p['url']; }
                }
                if (empty($photoUrls) && $image_url) $photoUrls[] = (is_array($image_url) ? $image_url['url'] : $image_url);
                $cleanImages = extractValidImages($photoUrls);

                // --- CUSTOM SHELTER RULE ---
                // Border Tails Rescue often uses a bad first image. 
                // If they have more than 1 image, we delete the first one so the 2nd becomes the main profile photo.
                if ($shelter['name'] === 'Border Tails Rescue' && count($cleanImages) > 1) {
                    array_shift($cleanImages);
                }
                // ---------------------------

                $res = upsertDog($db, [
                    'external_id'    => 'IL-SL-' . $animalId,
                    'source_shelter' => $shelter['name'],
                    'source_url'     => "https://www.shelterluv.com/embed/animal/" . $animalId,
                    'source_state'   => 'illinois',
                    'name'           => $name,
                    'breed_name'     => $breed,
                    'location'       => $shelter['city'] . ', IL',
                    'city'           => $shelter['city'],
                    'state'          => 'IL',
                    'age'            => $age,
                    'gender'         => $gender,
                    'color'          => $color,
                    'weight'         => $weight,
                    'description'    => $desc,
                    'image_url'      => $cleanImages[0] ?? '',
                    'gallery_urls'   => json_encode($cleanImages),
                    'owner_contact_name'  => $shelter['name'],
                    'owner_contact_phone' => $shelter['phone'],
                    'owner_contact_email' => $shelter['email']
                ]);
                
                if ($res === 'inserted') { $sl_ins++; $totIns++; }
                elseif ($res === 'updated') { $sl_upd++; $totUpd++; }
                else { $sl_err++; }
            }
            
            // Mark older dogs as adopted
            $db->prepare("UPDATE dogs SET status='adopted', adopted_at=NOW() WHERE source_shelter=? AND status='available' AND last_seen_at < DATE_SUB(NOW(),INTERVAL 48 HOUR)")->execute([$shelter['name']]);
            
            printSummary($shelter['name'], $sl_found, $sl_ins, $sl_upd, $sl_err);
        }
    } else {
        echo "  FAILED to fetch API for {$shelter['name']} (HTTP: $http_code)\n";
    }
}

echo "\nIllinois total — inserted: $totIns | updated: $totUpd\n";
echo str_repeat("─", 50) . "\n";
?>
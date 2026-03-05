<?php
/**
 * Foredog - Master Scraper Helpers (Advanced Bio Cleaner & Age Formatting)
 */
declare(strict_types=1);

if (!function_exists('fetchUrl')) {
    function fetchUrl(string $url, array $extraHeaders = [], int $timeout = 20): string|false {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER     => array_merge(['Accept: application/json, text/html, */*'], $extraHeaders),
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return ($body && $httpCode >= 200 && $httpCode < 300) ? $body : false;
    }
}

if (!function_exists('fetchMultiplePages')) {
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
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) $results[$url] = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
        }
        return $results;
    }
}

if (!function_exists('getXPath')) {
    function getXPath(string $html): DOMXPath {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();
        return new DOMXPath($dom);
    }
}

if (!function_exists('extractValidImages')) {
    function extractValidImages(array $rawUrls, string $baseUrl = ''): array {
        $clean = [];
        foreach ($rawUrls as $url) {
            $u = trim($url);
            if (empty($u)) continue;
            if (str_starts_with($u, 'data:image')) continue;
            if (str_ends_with(strtolower($u), '.svg')) continue;
            
            // STRICT BAN on logos, placeholders, and avatars
            if (preg_match('/(logo|icon|placeholder|banner|fallback|bg|default|related|avatar)/i', $u)) continue;
            
            if (!str_starts_with($u, 'http') && $baseUrl) {
                $u = rtrim($baseUrl, '/') . '/' . ltrim($u, '/');
            }
            if (!in_array($u, $clean)) $clean[] = $u;
        }
        return $clean;
    }
}

if (!function_exists('cleanName')) {
    function cleanName(string $raw): string {
        $cleaned = ucwords(strtolower(trim(preg_replace('/[*#@!]+/', '', $raw))));
        if (empty($cleaned) || in_array(strtolower($cleaned), ['null', 'unknown', 'no name', 'none', 'adopted'])) return '';
        return $cleaned;
    }
}

if (!function_exists('normalizeGender')) {
    function normalizeGender(string $raw): string {
        $r = strtolower(trim($raw));
        if (str_contains($r, 'f') || str_contains($r, 'spayed')) return 'Female';
        if (str_contains($r, 'm') || str_contains($r, 'neutered')) return 'Male';
        return 'Unknown';
    }
}

if (!function_exists('normalizeAge')) {
    function normalizeAge(string $raw): string {
        $r = strtolower(str_replace(['•', ' ', '&ensp;', '&nbsp;'], ' ', trim($raw)));
        
        // Idempotent Check to prevent runaway aging in the database
        if (str_contains($r, '< 1 year')) return '< 1 Year';
        if (str_contains($r, '1 - 3 years')) return '1 - 3 Years';
        if (str_contains($r, '3 - 7 years')) return '3 - 7 Years';
        if (str_contains($r, '7+ years')) return '7+ Years';

        if (empty($r) || str_contains($r, 'unknown')) return '3 - 7 Years';
        
        if (is_numeric(trim($r))) {
            $months = (float)trim($r);
            if ($months < 12) return '< 1 Year';
            if ($months >= 12 && $months < 36) return '1 - 3 Years';
            if ($months >= 36 && $months < 84) return '3 - 7 Years';
            return '7+ Years';
        }

        if (preg_match('/(\d+)\s*(y|yr|year)/i', $r, $m)) {
            $years = (int)$m[1];
            if ($years < 1) return '< 1 Year';
            if ($years >= 1 && $years < 3) return '1 - 3 Years';
            if ($years >= 3 && $years < 7) return '3 - 7 Years';
            return '7+ Years';
        }
        if (preg_match('/(\d+)\s*(m|mo|month)/i', $r, $m)) {
            $months = (int)$m[1];
            if ($months < 12) return '< 1 Year';
            if ($months >= 12 && $months < 36) return '1 - 3 Years';
            if ($months >= 36 && $months < 84) return '3 - 7 Years';
            return '7+ Years';
        }
        if (preg_match('/(\d+)\s*(w|wk|week)/i', $r, $m)) {
            return '< 1 Year';
        }
        
        if (str_contains($r, 'puppy') || str_contains($r, 'baby')) return '< 1 Year';
        if (str_contains($r, 'young')) return '1 - 3 Years';
        if (str_contains($r, 'senior')) return '7+ Years';
        
        return '3 - 7 Years';
    }
}

if (!function_exists('breedSlug')) {
    function breedSlug(string $raw): string {
        $r = strtolower(trim($raw));
        $r = preg_replace('/\s*\/.*$/', '', $r);
        $r = preg_replace('/\s*(mix|mixed|breed|shorthair|longhair|medium hair)$/i', '', trim($r));
        $map = ['german shepherd'=>'german-shepherd', 'golden retriever'=>'golden-retriever', 'labrador'=>'labrador-retriever', 'french bulldog'=>'french-bulldog', 'husky'=>'siberian-husky', 'poodle'=>'poodle', 'rottweiler'=>'rottweiler', 'dachshund'=>'dachshund', 'bulldog'=>'english-bulldog', 'australian shepherd'=>'australian-shepherd', 'chihuahua'=>'chihuahua', 'pit bull'=>'pit-bull', 'pug'=>'pug', 'beagle'=>'beagle', 'boxer'=>'boxer'];
        foreach ($map as $k => $s) { if (str_contains($r, $k)) return $s; }
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($r, '-')));
    }
}

// === PREMIUM FOREDOG BIO GENERATOR ===
if (!function_exists('generateForedogBio')) {
    function generateForedogBio(string $name, string $breed, string $ageRange, string $gender, string $color, string $rawShelterDesc): string {
        
        // 1. Strip raw HTML and messy breaks
        $cleanDesc = trim(strip_tags(str_ireplace(['<br>', '<br/>', '</p>'], ' ', $rawShelterDesc)));
        $cleanDesc = preg_replace('/\s+/', ' ', $cleanDesc);
        
        // 2. ADVANCED SCRUBBER: Removes inline stat blocks like "Age: 3 years old Gender: Male Weight: 12 lbs"
        $cleanDesc = preg_replace('/(?:Name|Age|Breed|Gender|Weight|Sex|Color|Size|Location|ID|Fee|Adoption Fee)[\s\:\-]+[^\n\r\.\!]+/i', '', $cleanDesc);
        $cleanDesc = preg_replace('/^Meet [^!.]+!\s*/i', '', $cleanDesc); 
        
        // 3. Scrub shelter names
        $cleanDesc = str_ireplace(
            ['shelter', 'rescue', 'foster', 'adoption center', 'our facility', 'APA!', 'Texas Humane Heroes', 'Animal Haven', 'Pasadena Humane', 'Muttville', 'Wags and Walks'], 
            ['matchmaking network', 'network', 'care home', 'network', 'our network', 'us', 'us', 'us', 'us', 'us', 'us'], 
            $cleanDesc
        );
        
        // 4. Extract exactly 3 clean sentences
        preg_match('/^([^.!?]*[.!?]+){0,3}/', trim($cleanDesc), $matches);
        $excerpt = trim($matches[0] ?? $cleanDesc);
        
        if (strlen($excerpt) < 20 && strlen($cleanDesc) > 20) {
            $excerpt = substr($cleanDesc, 0, 250) . '...';
        }

        // Return ONLY the clean excerpt! The Frontend UI handles the Intro formatting now!
        return $excerpt;
    }
}

if (!function_exists('upsertDog')) {
    function upsertDog(PDO $db, array $d): string {
        if (empty($d['external_id']) || empty($d['name'])) return 'error';

        $breedName = ucwords(strtolower(substr(trim($d['breed_name'] ?? 'Mixed Breed') ?: 'Mixed Breed', 0, 100)));
        // Database age remains pure, we do not double-normalize here!
        $age = substr(trim($d['age'] ?? '3 - 7 Years'), 0, 50); 
        $color = ucwords(strtolower(substr(trim($d['color'] ?? 'Unknown'), 0, 50)));
        
        $sql = "
            INSERT INTO dogs
                (external_id, source_shelter, source_url, source_state, name, breed_slug, breed_name,
                 location, city, state, age, gender, color, description, image_url, gallery_urls,
                 owner_contact_name, owner_contact_phone, owner_contact_email, status, last_seen_at)
            VALUES
                (:external_id, :source_shelter, :source_url, :source_state, :name, :breed_slug, :breed_name,
                 :location, :city, :state, :age, :gender, :color, :description, :image_url, :gallery_urls,
                 :owner_contact_name, :owner_contact_phone, :owner_contact_email, 'available', NOW())
            ON DUPLICATE KEY UPDATE
                name               = VALUES(name),
                breed_slug         = VALUES(breed_slug),
                breed_name         = VALUES(breed_name),
                age                = VALUES(age),
                gender             = VALUES(gender),
                color              = VALUES(color),
                description        = VALUES(description),
                image_url          = COALESCE(VALUES(image_url), image_url),
                gallery_urls       = COALESCE(VALUES(gallery_urls), gallery_urls),
                source_url         = COALESCE(VALUES(source_url), source_url),
                status             = 'available',
                last_seen_at       = NOW()
        ";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':external_id'         => $d['external_id'],
                ':source_shelter'      => $d['source_shelter'] ?? '',
                ':source_url'          => $d['source_url'] ?? '',
                ':source_state'        => strtolower(trim($d['source_state'] ?? '')),
                ':name'                => substr($d['name'], 0, 100),
                ':breed_slug'          => substr(breedSlug($breedName), 0, 100),
                ':breed_name'          => $breedName,
                ':location'            => substr($d['location'] ?? '', 0, 255),
                ':city'                => substr($d['city'] ?? '', 0, 100),
                ':state'               => substr($d['state'] ?? '', 0, 10),
                ':age'                 => $age,
                ':gender'              => normalizeGender($d['gender'] ?? ''),
                ':color'               => $color,
                ':description'         => trim($d['description'] ?? ''),
                ':image_url'           => $d['image_url'] ?? '',
                ':gallery_urls'        => $d['gallery_urls'] ?? json_encode([]),
                ':owner_contact_name'  => substr($d['owner_contact_name'] ?? '', 0, 100),
                ':owner_contact_phone' => substr($d['owner_contact_phone'] ?? '', 0, 20),
                ':owner_contact_email' => substr($d['owner_contact_email'] ?? '', 0, 150),
            ]);
            return $stmt->rowCount() === 1 ? 'inserted' : 'updated';
        } catch (Exception $e) {
            return 'error';
        }
    }
}

if (!function_exists('printSummary')) {
    function printSummary(string $label, int $found, int $ins, int $upd, int $err): void {
        echo sprintf("  %-40s found=%-4d inserted=%-4d updated=%-4d errors=%d\n", $label, $found, $ins, $upd, $err);
    }
}
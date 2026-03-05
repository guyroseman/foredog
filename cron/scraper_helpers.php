<?php
/**
 * scraper_helpers.php
 * Shared utilities for all Foredog state scrapers.
 */

/**
 * cURL GET — returns body string or false on failure.
 */
function fetchUrl(string $url, array $extraHeaders = [], int $timeout = 20): string|false
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) '
                                 .'AppleWebKit/537.36 (KHTML, like Gecko) '
                                 .'Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => array_merge([
            'Accept: application/json, text/html, */*',
            'Accept-Language: en-US,en;q=0.9',
        ], $extraHeaders),
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $body     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr || !$body || $httpCode < 200 || $httpCode >= 300) {
        echo "  [WARN] HTTP $httpCode — $url" . ($curlErr ? " ($curlErr)" : '') . "\n";
        return false;
    }
    return $body;
}

/**
 * Normalise gender to Male / Female / Unknown.
 */
function normaliseGender(string $raw): string
{
    $r = strtolower(trim($raw));
    if ($r === 'm' || $r === 'male')   return 'Male';
    if ($r === 'f' || $r === 'female') return 'Female';
    return 'Unknown';
}

/**
 * "German Shepherd" → "german-shepherd"
 */
function breedSlug(string $breed): string
{
    return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $breed), '-'));
}

/**
 * Upsert one dog row. Returns 'inserted' | 'updated' | 'error'.
 *
 * Required keys in $d: external_id, name
 * All other keys are optional (fall back to empty string / defaults).
 */
function upsertDog(PDO $db, array $d): string
{
    if (empty($d['external_id']) || empty($d['name'])) return 'error';

    $breedName = trim($d['breed_name'] ?? 'Mixed Breed') ?: 'Mixed Breed';

    $sql = "
        INSERT INTO dogs
            (external_id, name, breed_slug, breed_name,
             location, city, state, source_state,
             age, gender, description, image_url,
             owner_contact_name, owner_contact_phone, owner_contact_email,
             status)
        VALUES
            (:external_id, :name, :breed_slug, :breed_name,
             :location, :city, :state, :source_state,
             :age, :gender, :description, :image_url,
             :owner_contact_name, :owner_contact_phone, :owner_contact_email,
             'available')
        ON DUPLICATE KEY UPDATE
            status             = 'available',
            description        = VALUES(description),
            image_url          = COALESCE(VALUES(image_url), image_url),
            age                = VALUES(age)
    ";

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':external_id'         => $d['external_id'],
            ':name'                => ucwords(strtolower(trim($d['name']))),
            ':breed_slug'          => breedSlug($breedName),
            ':breed_name'          => $breedName,
            ':location'            => $d['location']            ?? '',
            ':city'                => $d['city']                ?? '',
            ':state'               => $d['state']               ?? '',
            ':source_state'        => $d['source_state']        ?? '',
            ':age'                 => $d['age']                 ?? '',
            ':gender'              => normaliseGender($d['gender'] ?? ''),
            ':description'         => trim($d['description']    ?? ''),
            ':image_url'           => $d['image_url']           ?? '',
            ':owner_contact_name'  => $d['owner_contact_name']  ?? '',
            ':owner_contact_phone' => $d['owner_contact_phone'] ?? '',
            ':owner_contact_email' => $d['owner_contact_email'] ?? '',
        ]);
        return $stmt->rowCount() === 1 ? 'inserted' : 'updated';
    } catch (Exception $e) {
        echo "  [DB ERROR] " . $e->getMessage() . "\n";
        return 'error';
    }
}

/**
 * Print a one-line summary for a shelter source.
 */
function printSummary(string $label, int $found, int $ins, int $upd, int $err): void
{
    echo sprintf(
        "  %-40s found=%-4d inserted=%-4d updated=%-4d errors=%d\n",
        $label, $found, $ins, $upd, $err
    );
}
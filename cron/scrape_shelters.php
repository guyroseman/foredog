<?php
/**
 * Foredog Automated Shelter Scraper
 * Run manually:  php cron/scrape_shelters.php
 * Run via cron:  0 6,18 * * * /usr/bin/php /path/to/foredog/cron/scrape_shelters.php >> /var/log/foredog.log 2>&1
 */
declare(strict_types=1);
require_once __DIR__ . '/../src/Database.php';
$db = Database::getInstance();

// ============================================================
// SHELTER CONFIG — add as many as you want
// For each shelter, inspect their HTML source to find the right
// XPath class names and update the 'fields' selectors below.
// ============================================================
$shelters = [
    [
        'name'       => 'Austin Animal Center',
        'location'   => 'Austin, TX',
        'url'        => 'https://www.austintexas.gov/adoptable-animals',
        'contact'    => ['name'=>'Austin Animal Center','phone'=>'(512) 978-0500','email'=>'aac@austintexas.gov'],
        'card_xpath' => "//div[contains(@class,'views-row')]",
        'fields'     => [
            'external_id' => ".//span[contains(@class,'field-content')][1]",
            'name'        => ".//span[contains(@class,'animal-name')]",
            'breed'       => ".//div[contains(@class,'field-breed')]",
            'age'         => ".//div[contains(@class,'field-age')]",
            'gender'      => ".//div[contains(@class,'field-sex')]",
            'description' => ".//div[contains(@class,'field-description')]",
            'image'       => ".//img/@src",
        ],
        'base_url'   => 'https://www.austintexas.gov',
    ],
    [
        'name'       => 'LA County Animal Care',
        'location'   => 'Los Angeles, CA',
        'url'        => 'https://animalcare.lacounty.gov/dogs/',
        'contact'    => ['name'=>'LA County Animal Care','phone'=>'(562) 728-4882','email'=>'animalcare@lacounty.gov'],
        'card_xpath' => "//div[contains(@class,'pet-card')]",
        'fields'     => [
            'external_id' => ".//span[@class='pet-id']",
            'name'        => ".//h3[contains(@class,'pet-name')]",
            'breed'       => ".//span[contains(@class,'pet-breed')]",
            'age'         => ".//span[contains(@class,'pet-age')]",
            'gender'      => ".//span[contains(@class,'pet-gender')]",
            'description' => ".//p[contains(@class,'pet-desc')]",
            'image'       => ".//img/@src",
        ],
        'base_url'   => 'https://animalcare.lacounty.gov',
    ],
    // JSON API example (no HTML parsing needed)
    [
        'name'       => 'NYC Animal Care Centers',
        'location'   => 'New York, NY',
        'url'        => 'https://adoptapp.nycacc.org/api/v2/animals?species=Dog&status=Available&limit=100',
        'contact'    => ['name'=>'NYC Animal Care Centers','phone'=>'(212) 788-4000','email'=>'info@nycacc.org'],
        'mode'       => 'json',
        'json_root'  => 'animals',
        'fields'     => ['external_id'=>'id','name'=>'name','breed'=>'primary_breed','age'=>'age','gender'=>'sex','description'=>'description','image'=>'primary_photo_url'],
        'base_url'   => '',
    ],
];

// ============================================================
// BREED NORMALIZER — maps shelter breed names to our slugs
// ============================================================
// Clean dog name — strip asterisks (foster flags), extra spaces, fix casing
function cleanDogName(string $raw): string {
    $name = preg_replace('/[*#@!]+/', '', $raw); // strip *, #, @ etc
    $name = trim(preg_replace('/\s+/', ' ', $name));
    return ucwords(strtolower($name));
}

function normalizeBreedSlug(string $raw): string {
    // Strip " Mix", " mix", "/labrador retriever" suffixes for cleaner matching
    $r = strtolower(trim($raw));
    $r = preg_replace('/\s*\/.*$/', '', $r);       // remove everything after slash (e.g. "/labrador retriever")
    $r = preg_replace('/\s*(mix|mixed|breed)$/', '', trim($r)); // remove trailing "mix"
    $r = preg_replace('/\s*(shorthair|longhair|medium hair)$/', '', trim($r)); // remove coat descriptors
    $r = trim($r);

    $map = [
        'german shepherd'=>'german-shepherd','gsd'=>'german-shepherd','alsatian'=>'german-shepherd',
        'golden retriever'=>'golden-retriever','golden'=>'golden-retriever',
        'labrador retriever'=>'labrador-retriever','labrador'=>'labrador-retriever','lab'=>'labrador-retriever',
        'yellow lab'=>'labrador-retriever','black lab'=>'labrador-retriever','chocolate lab'=>'labrador-retriever',
        'french bulldog'=>'french-bulldog','frenchie'=>'french-bulldog',
        'siberian husky'=>'siberian-husky','husky'=>'siberian-husky',
        'standard poodle'=>'poodle','miniature poodle'=>'poodle','toy poodle'=>'poodle','poodle'=>'poodle',
        'rottweiler'=>'rottweiler','rottie'=>'rottweiler',
        'dachshund'=>'dachshund','wiener dog'=>'dachshund','dachshund longhaired'=>'dachshund',
        'english bulldog'=>'english-bulldog','bulldog'=>'english-bulldog',
        'australian shepherd'=>'australian-shepherd','aussie'=>'australian-shepherd',
        'border collie'=>'border-collie',
        'doberman'=>'doberman','doberman pinscher'=>'doberman',
        'yorkshire terrier'=>'yorkshire-terrier','yorkie'=>'yorkshire-terrier',
        'chihuahua'=>'chihuahua','chi'=>'chihuahua',
        'pit bull'=>'pit-bull','pit bull terrier'=>'pit-bull','american pit bull terrier'=>'pit-bull',
        'american staffordshire'=>'pit-bull','amstaff'=>'pit-bull','staffordshire'=>'pit-bull',
        'pug'=>'pug','great dane'=>'great-dane',
        'shiba inu'=>'shiba-inu','shih tzu'=>'shih-tzu','havanese'=>'havanese',
        'miniature pinscher'=>'miniature-pinscher','min pin'=>'miniature-pinscher',
        'beagle'=>'beagle','boxer'=>'boxer','corgi'=>'corgi','maltese'=>'maltese',
        'schnauzer'=>'schnauzer','weimaraner'=>'weimaraner','dalmatian'=>'dalmatian',
    ];
    if (isset($map[$r])) return $map[$r];
    foreach ($map as $k => $s) { if (str_contains($r, $k)) return $s; }
    return strtolower(preg_replace('/[^a-z0-9]+/', '-', $r));
}

function fetchUrl(string $url): array {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL=>$url, CURLOPT_RETURNTRANSFER=>true, CURLOPT_FOLLOWLOCATION=>true,
        CURLOPT_TIMEOUT=>30, CURLOPT_CONNECTTIMEOUT=>10, CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_USERAGENT=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER=>['Accept: text/html,application/json,*/*;q=0.8','Accept-Language: en-US,en;q=0.5'],
    ]);
    $body=curl_exec($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch);
    return ['body'=>$body,'code'=>$code,'error'=>$err];
}

function upsertDog(PDO $db, array $d): string {
    $stmt = $db->prepare("INSERT INTO dogs
        (external_id,source_shelter,source_url,name,breed_slug,breed_name,location,age,gender,description,image_url,owner_contact_name,owner_contact_phone,owner_contact_email,status,last_seen_at)
        VALUES(:eid,:shelter,:surl,:name,:bslug,:bname,:loc,:age,:gender,:desc,:img,:cname,:cphone,:cemail,'available',NOW())
        ON DUPLICATE KEY UPDATE name=VALUES(name),breed_slug=VALUES(breed_slug),breed_name=VALUES(breed_name),age=VALUES(age),description=VALUES(description),image_url=VALUES(image_url),status='available',last_seen_at=NOW()");
    $stmt->execute($d);
    return match((int)$stmt->rowCount()){1=>'inserted',2=>'updated',default=>'unchanged'};
}

// ============================================================
// MAIN LOOP
// ============================================================
$totIns=0; $totUpd=0; $totErr=0;
echo "\n=== Foredog Scraper " . date('Y-m-d H:i:s') . " ===\n\n";

foreach ($shelters as $shelter) {
    $t0=$s=$u=$f=0; $t0=microtime(true); $runStatus='success'; $errMsg=null;
    echo "[ {$shelter['name']} ]\n";
    sleep(2); // polite delay
    $res = fetchUrl($shelter['url']);

    if (!$res['body'] || $res['code'] !== 200) {
        $errMsg="HTTP {$res['code']} {$res['error']}"; $runStatus='error';
        echo "  ERROR: $errMsg\n\n";
        $db->prepare("INSERT INTO scrape_log (shelter_name,shelter_url,status,error_msg,duration_sec) VALUES(?,?,'error',?,?)")
           ->execute([$shelter['name'],$shelter['url'],$errMsg,round(microtime(true)-$t0,2)]);
        $totErr++; continue;
    }

    $dogs=[];
    if (($shelter['mode']??'html')==='json') {
        $data=json_decode($res['body'],true);
        $items=$data[$shelter['json_root']]??$data??[];
        $fl=$shelter['fields'];
        foreach($items as $item){
            $rg=strtolower($item[$fl['gender']]??'');
            $dogs[]=[':eid'=>(string)($item[$fl['external_id']]??''),':shelter'=>$shelter['name'],':surl'=>$shelter['url'],':name'=>cleanDogName($item[$fl['name']]??''),':bslug'=>normalizeBreedSlug($item[$fl['breed']]??''),':bname'=>ucwords(strtolower($item[$fl['breed']]??'')),':loc'=>$shelter['location'],':age'=>$item[$fl['age']]??'',':gender'=>(str_contains($rg,'f')?'Female':(str_contains($rg,'m')?'Male':'Unknown')),':desc'=>$item[$fl['description']]??'',':img'=>$item[$fl['image']]??'',':cname'=>$shelter['contact']['name'],':cphone'=>$shelter['contact']['phone'],':cemail'=>$shelter['contact']['email']];
        }
    } else {
        $dom=new DOMDocument(); @$dom->loadHTML($res['body']);
        $xp=new DOMXPath($dom);
        $cards=$xp->query($shelter['card_xpath']);
        if(!$cards||$cards->length===0){echo "  WARNING: No cards matched XPath — check selectors\n"; $runStatus='partial';}
        $fl=$shelter['fields'];
        foreach($cards as $card){
            $rb=trim($xp->evaluate("string({$fl['breed']})",$card));
            $rg=strtolower(trim($xp->evaluate("string({$fl['gender']})",$card)));
            $img=trim($xp->evaluate("string({$fl['image']})",$card));
            if($img && !str_starts_with($img,'http')) $img=rtrim($shelter['base_url'],'/').'/'.ltrim($img,'/');
            $eid=trim($xp->evaluate("string({$fl['external_id']})",$card));
            $nm=trim($xp->evaluate("string({$fl['name']})",$card));
            if(empty($eid)&&empty($nm)) continue;
            if(empty($eid)) $eid=md5($shelter['name'].$rb.$nm);
            $dogs[]=[':eid'=>$eid,':shelter'=>$shelter['name'],':surl'=>$shelter['url'],':name'=>cleanDogName($nm),':bslug'=>normalizeBreedSlug($rb),':bname'=>ucwords(strtolower($rb)),':loc'=>$shelter['location'],':age'=>trim($xp->evaluate("string({$fl['age']})",$card)),':gender'=>(str_contains($rg,'female')?'Female':(str_contains($rg,'male')?'Male':'Unknown')),':desc'=>trim($xp->evaluate("string({$fl['description']})",$card)),':img'=>$img,':cname'=>$shelter['contact']['name'],':cphone'=>$shelter['contact']['phone'],':cemail'=>$shelter['contact']['email']];
        }
    }

    $f=count($dogs); echo "  Found: $f dogs\n";
    foreach($dogs as $dog){
        try{ $r=upsertDog($db,$dog); if($r==='inserted')$s++; elseif($r==='updated')$u++; }
        catch(Exception $e){ echo "  DB Error: ".$e->getMessage()."\n"; $totErr++; }
    }

    // Mark dogs from this shelter not seen in 48h as adopted
    if($f>0){
        $rm=$db->prepare("UPDATE dogs SET status='adopted',adopted_at=NOW() WHERE source_shelter=? AND status='available' AND last_seen_at < DATE_SUB(NOW(),INTERVAL 48 HOUR)");
        $rm->execute([$shelter['name']]);
        if($rm->rowCount()>0) echo "  Marked adopted: ".$rm->rowCount()."\n";
    }

    $db->prepare("INSERT INTO scrape_log (shelter_name,shelter_url,dogs_found,dogs_added,dogs_updated,status,error_msg,duration_sec) VALUES(?,?,?,?,?,?,?,?)")
       ->execute([$shelter['name'],$shelter['url'],$f,$s,$u,$runStatus,$errMsg,round(microtime(true)-$t0,2)]);

    $totIns+=$s; $totUpd+=$u;
    echo "  Inserted: $s | Updated: $u | Time: ".round(microtime(true)-$t0,1)."s\n\n";
}
echo "=== Done — Inserted: $totIns | Updated: $totUpd | Errors: $totErr ===\n\n";
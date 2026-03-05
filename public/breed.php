<?php
session_start();
require_once __DIR__ . "/../src/Database.php";

$match = $_GET["match"] ?? "";
$view  = $_GET["view"] ?? ($match ? "bridge" : "grid");

$selectedBreed = $_GET['filter_breed'] ?? '';
$selectedAge = $_GET['filter_age'] ?? '';
$selectedLocation = $_GET['filter_location'] ?? '';
$sortOrder = $_GET['sort'] ?? 'newest';

$db = Database::getInstance();
$query = 'SELECT * FROM dogs WHERE status = "available"';
$params = [];

if ($match && $view === 'bridge') {
    $query .= ' AND breed_slug = ?';
    $params[] = $match;
    $breedDisplay = ucwords(str_replace("-", " ", $match));
} else {
    if ($selectedBreed) { $query .= ' AND breed_slug = ?'; $params[] = $selectedBreed; }
    if ($selectedAge) { $query .= ' AND age = ?'; $params[] = $selectedAge; }
    if ($selectedLocation) { $query .= ' AND location = ?'; $params[] = $selectedLocation; }
    $breedDisplay = "Dogs";
}

if ($sortOrder === 'name_asc') { $query .= ' ORDER BY name ASC'; } 
elseif ($sortOrder === 'name_desc') { $query .= ' ORDER BY name DESC'; } 
else { $query .= ' ORDER BY created_at DESC'; }

$stmt = $db->prepare($query);
$stmt->execute($params);
$dogs = $stmt->fetchAll();

$breedOptions = $db->query('SELECT DISTINCT breed_name, breed_slug FROM dogs WHERE status="available" ORDER BY breed_name')->fetchAll();
$locationOptions = $db->query('SELECT DISTINCT location FROM dogs WHERE status="available" AND location != "" ORDER BY location')->fetchAll();

$fallbackImage = (!empty($dogs) && isset($dogs[0]['image_url'])) ? $dogs[0]['image_url'] : 'https://images.unsplash.com/photo-1543466835-00a73410a2c0?w=400&q=80';

$breed_data = [
    'labrador-retriever' => ['name' => 'Labrador Retriever', 'image' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400&q=80', 'match_percent' => '97%', 'bullets' => ['Friendly, outgoing, and high-spirited.', 'Loves outdoor activities and swimming.', 'An amazing companion for family life.']],
    'french-bulldog' => ['name' => 'French Bulldog', 'image' => 'https://images.unsplash.com/photo-1583511655826-05700d52f4d9?w=400&q=80', 'match_percent' => '96%', 'bullets' => ['Perfectly sized for apartment living.', 'Low-maintenance grooming means more playtime.', 'An affectionate cuddle bug.']],
    'golden-retriever' => ['name' => 'Golden Retriever', 'image' => 'https://images.unsplash.com/photo-1612774412771-005ed8e861d2?w=400&q=80', 'match_percent' => '99%', 'bullets' => ['Incredibly patient and great with kids.', 'Eager to please and easy to train.', 'Your new best friend for outdoor adventures.']],
    'german-shepherd' => ['name' => 'German Shepherd', 'image' => 'https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?w=400&q=80', 'match_percent' => '98%', 'bullets' => ['The ultimate loyal protector for your family.', 'Highly intelligent—ready for advanced training.', 'Perfect energy match for your active weekends.']],
    'poodle' => ['name' => 'Poodle', 'image' => 'https://images.unsplash.com/photo-1595701962388-348cdfa2bba2?w=400&q=80', 'match_percent' => '97%', 'bullets' => ['Hypoallergenic and virtually non-shedding.', 'Highly intelligent and eager to please.', 'Adapts easily to apartment or house living.']],
    'bulldog' => ['name' => 'Bulldog', 'image' => 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=400&q=80', 'match_percent' => '96%', 'bullets' => ['Loves to lounge and relax indoors.', 'Incredibly affectionate with families.', 'Requires very little exercise.']],
    'beagle' => ['name' => 'Beagle', 'image' => 'https://images.unsplash.com/photo-1537151608804-ea2aa1427189?w=400&q=80', 'match_percent' => '95%', 'bullets' => ['Merry, friendly, and curious nature.', 'Great compact size for families.', 'Excellent companion for moderate walks.']],
    'rottweiler' => ['name' => 'Rottweiler', 'image' => 'https://images.unsplash.com/photo-1567752881298-894bb81f9379?w=400&q=80', 'match_percent' => '98%', 'bullets' => ['Fiercely loyal and protective of family.', 'Confident guardian with a calm demeanor.', 'Loves having a job or training to do.']],
    'dachshund' => ['name' => 'Dachshund', 'image' => 'https://images.unsplash.com/photo-1612222869049-d8ec83637a3c?w=400&q=80', 'match_percent' => '97%', 'bullets' => ['Spunky, bold, and full of personality.', 'Perfect size for cozy apartment living.', 'Loves to burrow in blankets and cuddle.']],
    'corgi' => ['name' => 'Corgi', 'image' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400&q=80', 'match_percent' => '96%', 'bullets' => ['Smart, alert, and affectionate.', 'Big dog personality in a small package.', 'Highly trainable herding instincts.']],
    'australian-shepherd' => ['name' => 'Australian Shepherd', 'image' => 'https://images.unsplash.com/photo-1601002361664-90db1740eeb2?w=400&q=80', 'match_percent' => '98%', 'bullets' => ['Endless energy for hiking and running.', 'Incredibly smart and easy to train.', 'A beautiful, loyal working companion.']],
    'yorkshire-terrier' => ['name' => 'Yorkshire Terrier', 'image' => 'https://images.unsplash.com/photo-1558245558-868bfdb60ce0?w=400&q=80', 'match_percent' => '96%', 'bullets' => ['Tiny, elegant, and fiercely loyal.', 'Excellent watchdog for apartments.', 'Loves being the center of attention.']],
    'cavalier-spaniel' => ['name' => 'Cavalier King Charles', 'image' => 'https://images.unsplash.com/photo-1588665046200-a68393e117ec?w=400&q=80', 'match_percent' => '99%', 'bullets' => ['The ultimate lap dog and cuddler.', 'Gentle, affectionate, and great with kids.', 'Adapts easily to your energy level.']],
    'doberman' => ['name' => 'Doberman Pinscher', 'image' => 'https://images.unsplash.com/photo-1601831411516-43b67962eb7a?w=400&q=80', 'match_percent' => '97%', 'bullets' => ['Sleek, powerful, and deeply loyal.', 'One of the best protection breeds.', 'Highly intelligent and responsive to training.']],
    'boxer' => ['name' => 'Boxer', 'image' => 'https://images.unsplash.com/photo-1559190394-df5a28aab5c5?w=400&q=80', 'match_percent' => '96%', 'bullets' => ['Fun-loving, playful, and energetic.', 'Patient and protective with children.', 'A highly social family companion.']],
    'miniature-schnauzer' => ['name' => 'Miniature Schnauzer', 'image' => 'https://images.unsplash.com/photo-1589182337358-2cb63099350c?w=400&q=80', 'match_percent' => '95%', 'bullets' => ['Sturdy, fearless, and smart.', 'Low-shedding coat is great for indoors.', 'Very trainable and eager to please.']],
    'shih-tzu' => ['name' => 'Shih Tzu', 'image' => 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=400&q=80', 'match_percent' => '98%', 'bullets' => ['Bred specifically for companionship.', 'Happy, playful, and affectionate.', 'Requires very minimal exercise.']],
    'siberian-husky' => ['name' => 'Siberian Husky', 'image' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?w=400&q=80', 'match_percent' => '95%', 'bullets' => ['High energy for active owners.', 'Stunning appearance and friendly demeanor.', 'Loves to run, hike, and explore.']],
    'pug' => ['name' => 'Pug', 'image' => 'https://images.unsplash.com/photo-1517423440428-a5a00ad493e8?w=400&q=80', 'match_percent' => '97%', 'bullets' => ['Charming, mischievous, and loving.', 'The perfect couch potato companion.', 'Thrives in apartments and small spaces.']],
    'border-collie' => ['name' => 'Border Collie', 'image' => 'https://images.unsplash.com/photo-1564883446869-6d6f9a0c4f82?w=400&q=80', 'match_percent' => '98%', 'bullets' => ['Widely considered the smartest dog breed.', 'Thrives on having tasks and advanced training.', 'Incredible agility and stamina for active owners.']],
    'mixed-breed' => ['name' => 'Mixed Breed', 'image' => 'https://images.unsplash.com/photo-1544568100-847a948585b9?w=400&q=80', 'match_percent' => '99%', 'bullets' => ['Unique genetics often mean fewer inherited health issues.', 'A one-of-a-kind appearance and distinct personality.', 'Highly adaptable and eager to bond with a loving family.']]
];

$current_breed = $breed_data[$match] ?? [
    'name' => $breedDisplay,
    'image' => $fallbackImage,
    'match_percent' => '95%',
    'bullets' => ['A wonderful companion ready for a loving home.', 'Eager to bond and become part of your family.', 'The perfect match for your lifestyle.']
];

$pageTitle = $view === 'bridge' ? "Available {$current_breed['name']}s - Foredog" : "Browse Rescue Dogs - Foredog";
$metaDescription = "Browse hundreds of premium rescue dogs available for adoption near you.";

require __DIR__ . "/../templates/header.php";
?>

<?php if ($view === 'bridge'): ?>
<style>
    .bridge-container { max-width: 640px; margin: 40px auto 80px; background: #FFFFFF; border: 1px solid #EAEAEA; border-radius: 32px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04); overflow: hidden; position: relative; text-align: center; font-family: 'Inter', sans-serif;}
    .blurred-bg { background-image: url('https://images.unsplash.com/photo-1543466835-00a73410a2c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover; background-position: center; height: 180px; filter: blur(12px); opacity: 0.4; position: absolute; top: 0; left: 0; width: 100%; z-index: 1; }
    .bridge-content { position: relative; z-index: 2; padding: 0 2.5rem 3rem; margin-top: 100px; }
    .hero-image { width: 150px; height: 150px; background: #FFFFFF; border-radius: 50%; padding: 6px; box-shadow: 0 12px 30px rgba(0,0,0,0.1); margin: 0 auto 1.5rem; position: relative; z-index: 3; }
    .hero-image img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
    .bridge-title { color: #1A1A1A; font-family: "Playfair Display", serif; font-size: 2.5rem; margin-bottom: 0.5rem; }
    .match-badge { display: inline-block; background: #F4F1FF; color: #7B52F4; padding: 6px 18px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; margin-bottom: 25px; letter-spacing: 0.05em; text-transform: uppercase; }
    .urgency-box { background: #FFFFFF; border: 1px solid #EAEAEA; border-left: 4px solid #7B52F4; padding: 1.2rem; margin: 1.5rem 0; border-radius: 0 16px 16px 0; font-size: 0.95rem; text-align: left; color: #1A1A1A; }
    .bridge-bullets { text-align: left; padding-left: 20px; margin-bottom: 2.5rem; color: #666666; line-height: 1.8; font-size: 1.05rem; }
    .btn-primary { display: inline-flex; align-items: center; justify-content: center; padding: 1.2rem; border-radius: 50px; font-family: 'Inter', sans-serif; font-weight: 700; font-size: 1rem; cursor: pointer; text-decoration: none; transition: all 0.3s ease; border: none; background: #7B52F4; color: #FFFFFF; box-shadow: 0 4px 15px rgba(123, 82, 244, 0.3); }
</style>

<div class="bridge-container reveal">
    <div class="blurred-bg"></div>
    <div class="hero-image">
        <img src="<?= htmlspecialchars($current_breed['image']) ?>" alt="<?= htmlspecialchars($current_breed['name']) ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1543466835-00a73410a2c0?w=400&q=80';">
    </div>
    <div class="bridge-content">
        <h1 class="bridge-title">It's a Match!</h1>
        <div class="match-badge">You are a <?= htmlspecialchars($current_breed['match_percent']) ?> match for a <?= htmlspecialchars($current_breed['name']) ?></div>
        
        <div class="urgency-box pulse">
            <strong>⚠️ Great Timing:</strong> We just found <strong><?= count($dogs) ?> <?= htmlspecialchars($current_breed['name']) ?>s</strong> available for adoption near you.
        </div>

        <p style="text-align: left; font-weight: 700; margin-bottom: 1rem; color: #1A1A1A; font-size: 1.1rem;">Why this is your perfect dog:</p>
        <ul class="bridge-bullets">
            <?php foreach($current_breed['bullets'] as $bullet): ?>
                <li><?= htmlspecialchars($bullet) ?></li>
            <?php endforeach; ?>
        </ul>

        <a href="?match=<?= urlencode($match) ?>&view=grid" class="btn-primary" style="width: 100%;">
            View Available <?= htmlspecialchars($current_breed['name']) ?>s Now »
        </a>
    </div>
</div>

<?php else: ?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap');

    :root {
        --fd-purple: #7B52F4;
        --fd-purple-light: #F4F1FF;
        --fd-bg: #FAFAFA;
        --text-main: #1A1A1A;
        --border-color: #EAEAEA;
    }

    body { background: var(--fd-bg); font-family: 'Inter', sans-serif; }

    .page-hero { padding: 4rem 2rem 2rem; text-align: center; margin-bottom: 2rem; }
    .page-hero h1 { font-family: "Playfair Display", serif; font-size: 3.5rem; color: var(--text-main); margin-bottom: 0.5rem; font-weight: 700; }
    .page-hero h1 em { color: var(--fd-purple); font-style: italic; font-weight: 600; }
    .page-hero p { color: #666; font-size: 1.1rem; font-weight: 500; }
    
    /* FILTER BAR */
    .filter-bar { background: #FFF; border: 1px solid var(--border-color); padding: 1rem 1.5rem; border-radius: 16px; margin-bottom: 3rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
    .filter-form { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; width: 100%; }
    .filter-group { display: flex; flex-direction: column; gap: 0.3rem; flex: 1; min-width: 180px; }
    .filter-group label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #888; letter-spacing: 0.05em; }
    .filter-select { width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid var(--border-color); background: #F9F9F9; color: var(--text-main); font-family: 'Inter', sans-serif; font-size: 0.95rem; cursor: pointer; outline: none; transition: border 0.2s; }
    .filter-select:focus { border-color: var(--fd-purple); background: #FFF; }
    .btn-clear { color: #888; text-decoration: none; font-size: 0.9rem; font-weight: 600; padding: 0.8rem 1.2rem; margin-top: 1.1rem; transition: color 0.2s; }
    .btn-clear:hover { color: var(--text-main); }

    /* PREMIUM GRID CARDS */
    .dog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2.5rem; }
    
    .dog-card { background: #FFF; border: 1px solid var(--border-color); border-radius: 20px; overflow: hidden; transition: all 0.3s ease; text-decoration: none; display: flex; flex-direction: column; padding-bottom: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
    .dog-card:hover { transform: translateY(-6px); box-shadow: 0 15px 35px rgba(0,0,0,0.06); border-color: var(--fd-purple-light); }
    
    .card-img-wrap { position: relative; width: 100%; aspect-ratio: 1/1; overflow: hidden; background: #F5F5F5; border-radius: 20px 20px 0 0; }
    .card-img-wrap img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover !important; display: block; transition: transform 0.6s cubic-bezier(0.2, 0, 0, 1); }
    .dog-card:hover .card-img-wrap img { transform: scale(1.04); }
    
    .badge-top { position: absolute; top: 1rem; left: 1rem; background: #FFF; color: var(--fd-purple); font-size: 0.75rem; font-weight: 700; padding: 0.5rem 1rem; border-radius: 50px; z-index: 2; box-shadow: 0 4px 10px rgba(0,0,0,0.1); font-family: 'Inter', sans-serif;}
    .badge-gender { position: absolute; top: 1rem; right: 1rem; background: var(--text-main); color: #FFF; font-size: 0.75rem; font-weight: 600; padding: 0.5rem 1rem; border-radius: 50px; z-index: 2; font-family: 'Inter', sans-serif; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    
    .card-content { padding: 1.5rem 1.5rem 0; flex-grow: 1; }
    .card-content h3 { font-family: "Playfair Display", serif; font-size: 1.8rem; margin-bottom: 0.2rem; color: var(--text-main); font-weight: 700; }
    .card-breed { color: var(--fd-purple); font-size: 0.95rem; font-weight: 600; margin-bottom: 1.2rem; display: block; }
    
    .card-tags { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .tag { background: var(--fd-purple-light); color: var(--fd-purple); font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.4rem 0.8rem; border-radius: 8px; border: none; display: inline-flex; align-items: center; gap: 4px; }
    
    .card-btn { display: flex; justify-content: space-between; align-items: center; color: var(--text-main); font-weight: 700; font-size: 1rem; transition: color 0.2s; padding: 0 1.5rem; }
    .dog-card:hover .card-btn { color: var(--fd-purple); }
    .btn-primary { display: inline-flex; align-items: center; justify-content: center; padding: 0.8rem 2rem; border-radius: 50px; font-family: 'DM Sans', sans-serif; font-weight: 700; font-size: 1rem; cursor: pointer; text-decoration: none; transition: all 0.3s ease; border: none; background: #8B5CF6; color: #FFFFFF; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3); }
</style>

<section class="page-hero reveal">
    <?php if ($match): ?>
        <h1>Meet the <em><?= htmlspecialchars($breedDisplay) ?>s</em></h1>
        <p>We found <?= count($dogs) ?> available near you</p>
    <?php else: ?>
        <h1>All Available <em>Dogs</em></h1>
        <p><?= count($dogs) ?> dogs are looking for a forever home</p>
    <?php endif; ?>
</section>

<div class="container">
    <?php if (!$match): ?>
    <div class="filter-bar reveal delay-1">
        <form method="GET" action="/breed.php" class="filter-form">
            <input type="hidden" name="view" value="grid">
            
            <div class="filter-group">
                <label>Filter By Breed</label>
                <select name="filter_breed" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Breeds</option>
                    <?php foreach($breedOptions as $opt): ?>
                        <option value="<?= htmlspecialchars($opt['breed_slug']) ?>" <?= $selectedBreed === $opt['breed_slug'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($opt['breed_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Filter By Age</label>
                <select name="filter_age" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Ages</option>
                    <option value="< 1 Year" <?= $selectedAge === '< 1 Year' ? 'selected' : '' ?>>Puppy (&lt; 1 Year)</option>
                    <option value="1 - 3 Years" <?= $selectedAge === '1 - 3 Years' ? 'selected' : '' ?>>Young (1 - 3 Years)</option>
                    <option value="3 - 7 Years" <?= $selectedAge === '3 - 7 Years' ? 'selected' : '' ?>>Adult (3 - 7 Years)</option>
                    <option value="7+ Years" <?= $selectedAge === '7+ Years' ? 'selected' : '' ?>>Senior (7+ Years)</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Filter By Location</label>
                <select name="filter_location" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Locations</option>
                    <?php foreach($locationOptions as $opt): ?>
                        <option value="<?= htmlspecialchars($opt['location']) ?>" <?= $selectedLocation === $opt['location'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($opt['location']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if ($selectedBreed || $selectedAge || $selectedLocation): ?>
                <a href="/breed.php?view=grid" class="btn-clear">Clear Filters</a>
            <?php endif; ?>
        </form>
    </div>
    <?php endif; ?>
</div>

<div class="container" style="padding-bottom: 6rem;">
    <?php if (empty($dogs)): ?>
        <div style="text-align:center; padding: 4rem;">
            <h2>No dogs found right now.</h2>
            <p>We couldn't find any dogs matching those exact filters.</p>
            <a href="/breed.php?view=grid" class="btn btn-primary" style="margin-top: 1.5rem;">View All Dogs</a>
        </div>
    <?php else: ?>
        <div class="dog-grid">
            <?php $delay = 1; foreach ($dogs as $dog): ?>
            <a href="/dog.php?id=<?= (int)$dog["id"] ?>" class="dog-card reveal delay-<?= $delay++ > 3 ? 1 : $delay ?>">
                <div class="card-img-wrap">
                    <img src="<?= htmlspecialchars($dog["image_url"] ?? "") ?>" alt="<?= htmlspecialchars($dog["name"]) ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1543466835-00a73410a2c0?w=600&q=80';">
                    <span class="badge-top">Available</span>
                    <span class="badge-gender"><?= htmlspecialchars(ucfirst(strtolower($dog["gender"]))) ?></span>
                </div>
                <div class="card-content">
                    <h3><?= htmlspecialchars($dog['name']) ?></h3>
                    <span class="card-breed"><?= htmlspecialchars(ucwords(strtolower($dog['breed_name']))) ?></span>
                    
                    <div class="card-tags">
                        <?php if (!empty($dog['age']) && strtolower($dog['age']) !== 'unknown'): ?>
                            <span class="tag"><?= htmlspecialchars(strtoupper($dog['age'])) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($dog['location'])): ?>
                            <span class="tag">📍 <?= htmlspecialchars($dog['location']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-btn">
                    <span>Adopt <?= htmlspecialchars($dog["name"]) ?></span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<?php require __DIR__ . "/../templates/footer.php"; ?>
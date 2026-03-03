<?php
session_start();
require_once __DIR__ . "/../src/Database.php";

$match = $_GET["match"] ?? "";
$view  = $_GET["view"] ?? "bridge"; // Default to the emotional bridge page

$db = Database::getInstance();

// Fetch dogs matching the breed
if ($match) {
    $stmt = $db->prepare('SELECT * FROM dogs WHERE breed_slug = ? AND status = "available" ORDER BY created_at DESC');
    $stmt->execute([$match]);
    $dogs = $stmt->fetchAll();
    $breedDisplay = ucwords(str_replace("-", " ", $match));
} else {
    // Fallback if no match is provided
    $stmt = $db->query('SELECT * FROM dogs WHERE status = "available" ORDER BY created_at DESC');
    $dogs = $stmt->fetchAll();
    $breedDisplay = "Dogs";
    $view = "grid"; // Skip bridge if browsing all dogs
}

// Simulated Content Dictionary for the Future-Pacing Bridge
$breed_data = [
    'german-shepherd' => [
        'name' => 'German Shepherd',
        'image' => 'https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?w=400&q=80',
        'match_percent' => '98%',
        'bullets' => [
            'The ultimate loyal protector for your family.',
            'Highly intelligent—ready for advanced training.',
            'Perfect energy match for your active weekends.'
        ]
    ],
    'french-bulldog' => [
        'name' => 'French Bulldog',
        'image' => 'https://images.unsplash.com/photo-1583511655826-05700d52f4d9?w=400&q=80',
        'match_percent' => '96%',
        'bullets' => [
            'Perfectly sized for apartment living.',
            'Low-maintenance grooming means more playtime.',
            'An affectionate cuddle bug for your movie nights.'
        ]
    ],
    'golden-retriever' => [
        'name' => 'Golden Retriever',
        'image' => 'https://images.unsplash.com/photo-1612774412771-005ed8e861d2?w=400&q=80',
        'match_percent' => '99%',
        'bullets' => [
            'Incredibly patient and great with kids.',
            'Eager to please and easy to train.',
            'Your new best friend for outdoor adventures.'
        ]
    ],
    'labrador-retriever' => [
        'name' => 'Labrador Retriever',
        'image' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400&q=80',
        'match_percent' => '97%',
        'bullets' => [
            'Friendly, outgoing, and high-spirited.',
            'Loves outdoor activities and swimming.',
            'An amazing companion for family life.'
        ]
    ]
];

// Fallback logic if the breed isn't in the dictionary
$current_breed = $breed_data[$match] ?? [
    'name' => $breedDisplay,
    'image' => $dogs[0]['image_url'] ?? 'https://images.unsplash.com/photo-1543466835-00a73410a2c0?w=400&q=80',
    'match_percent' => '95%',
    'bullets' => [
        'A wonderful companion ready for a loving home.',
        'Eager to bond and become part of your family.',
        'The perfect match for your lifestyle.'
    ]
];

$pageTitle = ($view === 'bridge') ? "It's a Match! - Foredog" : "Available " . htmlspecialchars($breedDisplay) . "s - Foredog";

require __DIR__ . "/../templates/header.php";
?>

<?php if ($view === 'bridge'): ?>
<style>
    body { background-color: var(--cream); }
    .bridge-container { max-width: 600px; margin: 40px auto 80px; background: var(--white); border-radius: 16px; box-shadow: 0 10px 30px rgba(44,24,16,0.08); overflow: hidden; position: relative; text-align: center; }
    .blurred-bg { background-image: url('https://images.unsplash.com/photo-1543466835-00a73410a2c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover; background-position: center; height: 200px; filter: blur(8px); opacity: 0.6; position: absolute; top: 0; left: 0; width: 100%; z-index: 1; }
    .bridge-content { position: relative; z-index: 2; padding: 30px 2rem; margin-top: 50px; }
    .hero-image { width: 140px; height: 140px; background: var(--white); border-radius: 50%; padding: 8px; box-shadow: 0 8px 24px rgba(44,24,16,0.15); margin: 0 auto 20px; position: relative; z-index: 3; }
    .hero-image img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
    .bridge-title { color: var(--bark); font-family: "Playfair Display", serif; font-size: 2.2rem; margin-bottom: 5px; }
    .match-badge { display: inline-block; background: var(--sage); color: var(--white); padding: 6px 18px; border-radius: 50px; font-weight: 500; font-size: .9rem; margin-bottom: 25px; letter-spacing: 0.05em; }
    .urgency-box { background: #FFF8F2; border-left: 4px solid var(--amber); padding: 18px; margin: 20px 0; border-radius: 0 8px 8px 0; font-size: 1rem; text-align: left; color: var(--bark); }
    .pulse { animation: pulse-animation 2.5s infinite; }
    @keyframes pulse-animation { 0% { transform: scale(1); } 50% { transform: scale(1.02); } 100% { transform: scale(1); } }
    .bridge-bullets { text-align: left; padding-left: 20px; margin-bottom: 35px; color: var(--muted); line-height: 1.8; font-size: 1.05rem; }
    .cta-button { display: block; background-color: var(--amber); color: var(--white); text-decoration: none; padding: 18px 20px; font-size: 1.1rem; font-family: 'DM Sans', sans-serif; font-weight: 500; border-radius: 50px; transition: all 0.2s; box-shadow: 0 8px 20px rgba(200, 115, 42, 0.3); border: none; width: 100%; cursor: pointer; }
    .cta-button:hover { background-color: var(--amber-light); transform: translateY(-2px); }
</style>

<div class="bridge-container">
    <div class="blurred-bg"></div>
    <div class="hero-image">
        <img src="<?= htmlspecialchars($current_breed['image']) ?>" alt="<?= htmlspecialchars($current_breed['name']) ?>">
    </div>
    
    <div class="bridge-content">
        <h1 class="bridge-title">It's a Match!</h1>
        <div class="match-badge">You are a <?= htmlspecialchars($current_breed['match_percent']) ?> lifestyle match for a <?= htmlspecialchars($current_breed['name']) ?></div>
        
        <div class="urgency-box pulse">
            <strong>⚠️ Great Timing:</strong> We just found <strong><?= count($dogs) ?> <?= htmlspecialchars($current_breed['name']) ?>s</strong> available for adoption near you.
        </div>

        <p style="text-align: left; font-weight: 600; margin-bottom: 15px; color: var(--bark); font-size: 1.1rem;">Why this is your perfect dog:</p>
        <ul class="bridge-bullets">
            <?php foreach($current_breed['bullets'] as $bullet): ?>
                <li><?= htmlspecialchars($bullet) ?></li>
            <?php endforeach; ?>
        </ul>

        <a href="?match=<?= urlencode($match) ?>&view=grid" class="cta-button">
            View Available <?= htmlspecialchars($current_breed['name']) ?>s Now »
        </a>
        <p style="font-size: .8rem; color: var(--muted); margin-top: 15px;">Clicking this link will securely transfer you to our adoption inventory.</p>

        <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--sand);">
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.4rem; color: var(--bark); margin-bottom: 1rem;">Other Great Matches For You</h3>
            <div style="display: flex; gap: 1rem; justify-content: center; margin-bottom: 1.5rem;">
                <?php 
                $runnerUps = $_SESSION['runner_ups'] ?? [];
                foreach($runnerUps as $ru_slug): 
                    $ru = $breed_data[$ru_slug] ?? null;
                    if($ru):
                ?>
                <a href="?match=<?= urlencode($ru_slug) ?>&view=bridge" style="text-decoration: none; background: var(--cream); border: 1px solid var(--sand); border-radius: 12px; padding: 1rem; width: 45%; transition: transform 0.2s;">
                    <img src="<?= $ru['image'] ?>" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 0.5rem;">
                    <strong style="display: block; color: var(--bark); font-size: .95rem;"><?= $ru['name'] ?></strong>
                    <span style="font-size: .75rem; color: var(--amber); font-weight: 500;">See Match »</span>
                </a>
                <?php endif; endforeach; ?>
            </div>

            <a href="?view=grid" style="display: inline-block; padding: .6rem 1.5rem; border: 2px solid var(--bark); color: var(--bark); border-radius: 50px; text-decoration: none; font-size: .9rem; font-weight: 500; transition: all 0.2s;">
                Or browse all 1,420 available dogs
            </a>
        </div>

    </div>
</div>

<?php else: ?>
<style>
    .page-hero { background: var(--bark); padding: 4rem 2rem; text-align: center; position: relative; overflow: hidden; }
    .page-hero::before { content: ""; position: absolute; inset: 0; background: url("https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=1400&q=60") center/cover; opacity: .1; }
    .page-hero-inner { position: relative; z-index: 1; }
    .page-hero h1 { font-family: "Playfair Display", serif; font-size: clamp(2rem, 5vw, 3rem); color: var(--white); }
    .page-hero h1 em { color: var(--amber-light); font-style: italic; }
    .page-hero p { color: rgba(255,255,255,.6); margin-top: .75rem; }
    .dog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.75rem; margin-top: 2rem; }
    .dog-card { background: var(--white); border-radius: 16px; overflow: hidden; transition: transform .25s, box-shadow .25s; text-decoration: none; color: inherit; box-shadow: 0 2px 12px rgba(44,24,16,.06); display: block; position: relative; }
    .dog-card:hover { transform: translateY(-6px); box-shadow: 0 20px 48px rgba(44,24,16,.15); }
    .dog-card-img { position: relative; overflow: hidden; height: 240px; }
    .dog-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
    .dog-card:hover .dog-card-img img { transform: scale(1.05); }
    .dog-card-badge { position: absolute; top: .75rem; left: .75rem; background: var(--sage); color: var(--white); font-size: .7rem; font-weight: 500; padding: .25rem .7rem; border-radius: 50px; }
    .dog-card-gender { position: absolute; top: .75rem; right: .75rem; background: rgba(44,24,16,.7); color: var(--white); font-size: .75rem; padding: .25rem .65rem; border-radius: 50px; }
    .dog-card-body { padding: 1.25rem 1.5rem 1.5rem; }
    .dog-card-body h3 { font-family: "Playfair Display", serif; font-size: 1.35rem; margin-bottom: .25rem; }
    .dog-card-breed { color: var(--amber); font-size: .85rem; font-weight: 500; margin-bottom: .75rem; }
    .dog-meta { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: .75rem; }
    .dog-tag { background: var(--sand); color: var(--muted); font-size: .72rem; padding: .2rem .6rem; border-radius: 50px; }
    .dog-location { color: var(--muted); font-size: .82rem; margin-bottom: 1rem; }
    .dog-card-cta { display: block; background: var(--bark); color: var(--white); text-align: center; padding: .65rem; border-radius: 8px; font-size: .875rem; font-weight: 500; transition: background .18s; }
    .dog-card:hover .dog-card-cta { background: var(--amber); }
    .no-results { text-align: center; padding: 5rem 2rem; color: var(--muted); }
</style>

<section class="page-hero">
    <div class="page-hero-inner">
        <?php if ($match): ?>
            <p style="font-size: .8rem; text-transform: uppercase; letter-spacing: .1em; color: var(--amber-light); margin-bottom: .5rem;">Your Top Match</p>
            <h1>Meet the <em><?= htmlspecialchars($breedDisplay) ?>s</em></h1>
            <p>We found <?= count($dogs) ?> available near you</p>
        <?php else: ?>
            <h1>All Available <em>Dogs</em></h1>
            <p><?= count($dogs) ?> dogs are looking for a forever home</p>
        <?php endif; ?>
    </div>
</section>

<div class="container" style="padding-top: 3rem; padding-bottom: 5rem;">
    <?php if (!empty($match)): ?>
        <div style="margin-bottom: 1.5rem;">
            <a href="/survey.php" style="color: var(--muted); font-size: .875rem; text-decoration: none;">&#8592; Retake the Quiz</a>
        </div>
    <?php endif; ?>

    <?php if (empty($dogs)): ?>
        <div class="no-results">
            <p style="font-size: 3rem; margin-bottom: 1rem;">&#128062;</p>
            <h2 style="font-family: Playfair Display, serif; font-size: 1.5rem; margin-bottom: .5rem;">No dogs found for this breed yet</h2>
            <p>New dogs are added daily. Check back soon or browse all available dogs.</p>
            <a href="/breed.php" class="btn btn-primary" style="margin-top: 1.5rem;">Browse All Dogs</a>
        </div>
    <?php else: ?>
        <div class="dog-grid">
            <?php foreach ($dogs as $dog): ?>
            <a href="/dog.php?id=<?= (int)$dog["id"] ?>" class="dog-card">
                <div class="dog-card-img">
                    <img src="<?= htmlspecialchars($dog["image_url"] ?? "") ?>" alt="<?= htmlspecialchars($dog["name"]) ?>">
                    <span class="dog-card-badge">Available</span>
                    <span class="dog-card-gender"><?= htmlspecialchars($dog["gender"]) ?></span>
                </div>
                <div class="dog-card-body">
                    <h3><?= htmlspecialchars($dog["name"]) ?></h3>
                    <div class="dog-card-breed"><?= htmlspecialchars($dog["breed_name"]) ?></div>
                    <div class="dog-meta">
                        <span class="dog-tag"><?= htmlspecialchars($dog["age"]) ?></span>
                        <span class="dog-tag"><?= htmlspecialchars($dog["color"]) ?></span>
                    </div>
                    <p class="dog-location">&#128205; <?= htmlspecialchars($dog["location"]) ?></p>
                    <span class="dog-card-cta">Adopt <?= htmlspecialchars($dog["name"]) ?> &#8250;</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . "/../templates/footer.php"; ?>
<?php
session_start();
require_once __DIR__ . "/../src/Database.php";

$match = $_GET["match"] ?? "";
$view  = $_GET["view"] ?? "bridge";

$db = Database::getInstance();

if ($match) {
    $stmt = $db->prepare('SELECT * FROM dogs WHERE breed_slug = ? AND status = "available" ORDER BY created_at DESC');
    $stmt->execute([$match]);
    $dogs = $stmt->fetchAll();
    $breedDisplay = ucwords(str_replace("-", " ", $match));
} else {
    $stmt = $db->query('SELECT * FROM dogs WHERE status = "available" ORDER BY created_at DESC');
    $dogs = $stmt->fetchAll();
    $breedDisplay = "Dogs";
    $view = "grid"; 
}

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
    'border-collie' => ['name' => 'Border Collie', 'image' => 'https://images.unsplash.com/photo-1564883446869-6d6f9a0c4f82?w=400&q=80', 'match_percent' => '98%', 'bullets' => ['Widely considered the smartest dog breed.', 'Thrives on having tasks and advanced training.', 'Incredible agility and stamina for active owners.']]
];

$current_breed = $breed_data[$match] ?? [
    'name' => $breedDisplay,
    'image' => $fallbackImage,
    'match_percent' => '95%',
    'bullets' => ['A wonderful companion ready for a loving home.', 'Eager to bond and become part of your family.', 'The perfect match for your lifestyle.']
];

$pageTitle = ($view === 'bridge') ? "It's a Match! - Foredog" : "Available " . htmlspecialchars($breedDisplay) . "s - Foredog";

require __DIR__ . "/../templates/header.php";
?>

<?php if ($view === 'bridge'): ?>
<style>
    .bridge-container { max-width: 640px; margin: 40px auto 80px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow-soft); overflow: hidden; position: relative; text-align: center; }
    .blurred-bg { background-image: url('https://images.unsplash.com/photo-1543466835-00a73410a2c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); background-size: cover; background-position: center; height: 180px; filter: blur(12px); opacity: 0.4; position: absolute; top: 0; left: 0; width: 100%; z-index: 1; }
    .bridge-content { position: relative; z-index: 2; padding: 0 2.5rem 3rem; margin-top: 100px; }
    .hero-image { width: 150px; height: 150px; background: var(--surface); border-radius: 50%; padding: 6px; box-shadow: 0 12px 30px rgba(0,0,0,0.1); margin: 0 auto 1.5rem; position: relative; z-index: 3; }
    .hero-image img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
    .bridge-title { color: var(--text-dark); font-family: "Playfair Display", serif; font-size: 2.5rem; margin-bottom: 0.5rem; }
    .match-badge { display: inline-block; background: var(--pn-purple-light); color: var(--pn-purple-dark); padding: 6px 18px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; margin-bottom: 25px; letter-spacing: 0.05em; text-transform: uppercase; }
    .urgency-box { background: var(--bg-main); border: 1px solid var(--border); border-left: 4px solid var(--pn-purple); padding: 1.2rem; margin: 1.5rem 0; border-radius: 0 var(--radius-md) var(--radius-md) 0; font-size: 0.95rem; text-align: left; color: var(--text-dark); }
    .pulse { animation: pulse-animation 2.5s infinite; }
    @keyframes pulse-animation { 0% { transform: scale(1); } 50% { transform: scale(1.02); } 100% { transform: scale(1); } }
    .bridge-bullets { text-align: left; padding-left: 20px; margin-bottom: 2.5rem; color: var(--text-muted); line-height: 1.8; font-size: 1.05rem; }
    
    .runner-ups-section { margin-top: 3rem; padding-top: 2.5rem; border-top: 1px solid var(--border); }
    .runner-ups-grid { display: flex; gap: 1rem; justify-content: center; margin-bottom: 2rem; flex-wrap: wrap; }
    .ru-card { background: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.2rem; width: calc(50% - 0.5rem); text-decoration: none; transition: all 0.2s; display: flex; flex-direction: column; align-items: center; }
    .ru-card:hover { border-color: var(--pn-purple-light); transform: translateY(-3px); box-shadow: var(--shadow-soft); }
    .ru-card img { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; margin-bottom: 0.75rem; }
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

        <p style="text-align: left; font-weight: 700; margin-bottom: 1rem; color: var(--text-dark); font-size: 1.1rem;">Why this is your perfect dog:</p>
        <ul class="bridge-bullets">
            <?php foreach($current_breed['bullets'] as $bullet): ?>
                <li><?= htmlspecialchars($bullet) ?></li>
            <?php endforeach; ?>
        </ul>

        <a href="?match=<?= urlencode($match) ?>&view=grid" class="btn btn-primary" style="width: 100%; padding: 1.2rem;">
            View Available <?= htmlspecialchars($current_breed['name']) ?>s Now »
        </a>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 1rem;">Clicking this link will securely transfer you to our adoption inventory.</p>

        <div class="runner-ups-section">
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--text-dark); margin-bottom: 1.5rem;">Other Great Matches For You</h3>
            <div class="runner-ups-grid">
                <?php 
                $runnerUps = $_SESSION['runner_ups'] ?? [];
                foreach($runnerUps as $ru_slug): 
                    $ru = $breed_data[$ru_slug] ?? null;
                    if($ru):
                ?>
                <a href="?match=<?= urlencode($ru_slug) ?>&view=bridge" class="ru-card">
                    <img src="<?= $ru['image'] ?>" alt="<?= $ru['name'] ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1543466835-00a73410a2c0?w=200&q=80';">
                    <strong style="color: var(--text-dark); font-size: 0.95rem; text-align: center;"><?= $ru['name'] ?></strong>
                    <span style="font-size: 0.8rem; color: var(--pn-purple); font-weight: 700; margin-top: 0.25rem;">See Match »</span>
                </a>
                <?php endif; endforeach; ?>
            </div>

            <a href="?view=grid" class="btn" style="background: transparent; color: var(--text-muted); border: 1px solid var(--border); font-size: 0.9rem;">
                Or browse all available dogs
            </a>
        </div>
    </div>
</div>

<?php else: ?>
<style>
    .page-hero { padding: 5rem 2rem; text-align: center; background: var(--surface); border-bottom: 1px solid var(--border); margin-bottom: 1rem; }
    .page-hero h1 { font-family: "Playfair Display", serif; font-size: clamp(2.5rem, 5vw, 3.5rem); color: var(--text-dark); margin-bottom: 0.5rem; }
    .page-hero h1 em { color: var(--pn-purple); font-style: italic; }
    .page-hero p { color: var(--text-muted); font-size: 1.15rem; font-weight: 400; max-width: 500px; margin: 0 auto; }
    
    .dog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2.5rem; }
    .dog-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; transition: all 0.3s ease; text-decoration: none; display: flex; flex-direction: column; }
    .dog-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-hover); border-color: var(--pn-purple-light); }
    
    /* FIX: Bulletproof Square Grid Images (No Stretching!) */
    .card-img-wrap { position: relative; width: 100%; padding-top: 100%; overflow: hidden; background: var(--border); }
    .card-img-wrap img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover !important; display: block; transition: transform 0.5s ease; }
    
    .dog-card:hover .card-img-wrap img { transform: scale(1.05); }
    
    .badge-top { position: absolute; top: 1rem; left: 1rem; background: var(--surface); color: var(--pn-purple); font-size: 0.75rem; font-weight: 800; padding: 0.4rem 1rem; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 2; }
    .badge-gender { position: absolute; top: 1rem; right: 1rem; background: var(--text-dark); color: var(--surface); font-size: 0.75rem; font-weight: 700; padding: 0.4rem 1rem; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 2; }
    
    .card-content { padding: 1.5rem; }
    .card-content h3 { font-family: "Playfair Display", serif; font-size: 1.6rem; margin-bottom: 0.25rem; color: var(--text-dark); }
    .card-breed { color: var(--pn-purple); font-size: 0.95rem; font-weight: 700; margin-bottom: 1rem; }
    
    .card-tags { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.2rem; }
    .tag { background: var(--pn-purple-light); color: var(--pn-purple-dark); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.35rem 0.8rem; border-radius: 8px; border: none; }
    
    .card-btn { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 1.2rem; color: var(--text-dark); font-weight: 700; font-size: 0.95rem; transition: color 0.2s; }
    .dog-card:hover .card-btn { color: var(--pn-purple); }
    .no-results { text-align: center; padding: 6rem 2rem; color: var(--text-muted); }
</style>

<section class="page-hero reveal">
    <?php if ($match): ?>
        <p style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--pn-purple); margin-bottom: 0.5rem;">Your Match Inventory</p>
        <h1>Meet the <em><?= htmlspecialchars($breedDisplay) ?>s</em></h1>
        <p>We found <?= count($dogs) ?> available near you</p>
    <?php else: ?>
        <h1>All Available <em>Dogs</em></h1>
        <p><?= count($dogs) ?> dogs are looking for a forever home</p>
    <?php endif; ?>
</section>

<div class="container"><div class="ad-placement">Strategic Ad Placement (728x90)</div></div>

<div class="container" style="padding-bottom: 6rem;">
    <?php if (!empty($match)): ?>
        <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;" class="reveal delay-1">
            <a href="/survey.php" style="color: var(--text-muted); font-size: 0.95rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; transition: color 0.2s;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg> Retake Quiz
            </a>
        </div>
    <?php endif; ?>

    <?php if (empty($dogs)): ?>
        <div class="no-results reveal">
            <div style="color: var(--pn-purple); margin-bottom: 1rem;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8.69 2 6 4.69 6 8c0 3.31 2.69 6 6 6s6-2.69 6-6c0-3.31-2.69-6-6-6zM22 14c0 1.18-.33 2.32-.94 3.18l-1.77-1.28c.58-.56.94-1.33.94-2.18 0-1.65-1.35-3-3-3h-1c-.55 0-1-.45-1-1s.45-1 1-1h1c2.76 0 5 2.24 5 5z"/></svg>
            </div>
            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.8rem; color: var(--text-dark); margin-bottom: 0.5rem;">No dogs found for this breed right now.</h2>
            <p>New dogs arrive daily. Check back soon or browse all available dogs.</p>
            <a href="/breed.php" class="btn btn-primary" style="margin-top: 1.5rem;">Browse All Dogs</a>
        </div>
    <?php else: ?>
        <div class="dog-grid">
            <?php $delay = 1; foreach ($dogs as $dog): ?>
            <a href="/dog.php?id=<?= (int)$dog["id"] ?>" class="dog-card reveal delay-<?= $delay++ > 3 ? 1 : $delay ?>">
                <div class="card-img-wrap">
                    <img src="<?= htmlspecialchars($dog["image_url"] ?? "") ?>" alt="<?= htmlspecialchars($dog["name"]) ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1543466835-00a73410a2c0?w=600&q=80';">
                    <span class="badge-top">Available</span>
                    <span class="badge-gender"><?= htmlspecialchars($dog["gender"]) ?></span>
                </div>
                <div class="card-content">
                    <h3><?= htmlspecialchars($dog['name']) ?></h3>
                    <div class="card-breed"><?= htmlspecialchars($dog['breed_name']) ?></div>
                    <div class="card-tags">
                        <?php if (!empty($dog['age']) && strtolower($dog['age']) !== 'unknown'): ?>
                            <span class="tag"><?= htmlspecialchars($dog['age']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($dog['color']) && strtolower($dog['color']) !== 'unknown'): ?>
                            <span class="tag"><?= htmlspecialchars($dog['color']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-btn">
                        <span>Adopt <?= htmlspecialchars($dog["name"]) ?></span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="container"><div class="ad-placement" style="margin-bottom: 4rem;">Strategic Ad Placement (728x90)</div></div>

<?php endif; ?>

<?php require __DIR__ . "/../templates/footer.php"; ?>
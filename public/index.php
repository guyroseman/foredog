<?php
$pageTitle = 'Foredog - Premium Pet Adoption';
require_once __DIR__ . '/../src/Database.php';
require __DIR__ . '/../templates/header.php';

$db = Database::getInstance();
// Fetch 3 featured dogs for the landing page
$dogs = $db->query('SELECT * FROM dogs WHERE status = "available" LIMIT 3')->fetchAll();
?>
<style>
    /* HERO SECTION */
    .hero { 
        padding: 6rem 0 4rem; 
        position: relative; 
        overflow: hidden;
    }
    .hero-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        align-items: center;
    }
    @media (max-width: 900px) { .hero-grid { grid-template-columns: 1fr; text-align: center; } }
    
    .hero-badge {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--pn-purple-light); color: var(--pn-purple-dark);
        padding: 8px 16px; border-radius: 50px; font-weight: 700; font-size: 0.85rem;
        margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.05em;
    }
    
    .hero h1 { 
        font-family: 'Playfair Display', serif; 
        font-size: clamp(3rem, 6vw, 4.5rem); 
        line-height: 1.1; 
        color: var(--text-dark); 
        margin-bottom: 1.5rem; 
    }
    .hero h1 span { 
        position: relative; 
        display: inline-block; 
        color: var(--pn-purple); 
    }
    .hero h1 span::after {
        content: ''; position: absolute; bottom: 8px; left: 0; width: 100%; height: 12px;
        background: var(--accent-yellow); opacity: 0.3; z-index: -1; transform: rotate(-2deg);
    }
    
    .hero p { 
        font-size: 1.25rem; 
        color: var(--text-muted); 
        margin-bottom: 2.5rem; 
        max-width: 480px; 
    }
    @media (max-width: 900px) { .hero p { margin: 0 auto 2.5rem; } }

    /* IMAGE COMPOSITION */
    .hero-visuals { position: relative; height: 500px; }
    .img-main {
        position: absolute; right: 0; top: 0; width: 85%; height: 100%;
        object-fit: cover; border-radius: 40px; box-shadow: var(--shadow-hover);
        z-index: 2;
    }
    .img-accent {
        position: absolute; left: 0; bottom: -20px; width: 45%; height: 60%;
        object-fit: cover; border-radius: 200px; border: 8px solid var(--bg-main);
        box-shadow: var(--shadow-soft); z-index: 3; animation: float 6s ease-in-out infinite;
    }
    .floating-card {
        position: absolute; top: 40px; left: -20px; background: var(--surface);
        padding: 1rem 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-soft);
        z-index: 4; display: flex; align-items: center; gap: 12px; animation: float 5s ease-in-out infinite reverse;
    }
    .floating-card div { font-size: 0.85rem; font-weight: 700; color: var(--text-dark); }
    .floating-card span { color: var(--text-muted); font-weight: 400; display: block; }
    .pulse-dot { width: 12px; height: 12px; background: #10B981; border-radius: 50%; box-shadow: 0 0 0 4px rgba(16,185,129,0.2); }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
    }

    /* STATS & HOW IT WORKS */
    .stats-container {
        background: var(--surface); border-radius: var(--radius-lg); padding: 3rem;
        box-shadow: var(--shadow-soft); display: flex; justify-content: space-around; flex-wrap: wrap; gap: 2rem;
        margin: -40px auto 5rem; position: relative; z-index: 10; border: 1px solid var(--border);
    }
    .stat-item { text-align: center; }
    .stat-num { font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--pn-purple); font-weight: 700; }
    .stat-label { font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.8rem; margin-top: 0.5rem; }

    /* DOG CARDS */
    .section-header { text-align: center; margin-bottom: 3.5rem; }
    .section-header h2 { font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--text-dark); }
    
    .dog-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2.5rem; }
    
    .dog-card { 
        background: var(--surface); border-radius: var(--radius-lg); overflow: hidden; 
        box-shadow: var(--shadow-soft); transition: all 0.4s ease; text-decoration: none; 
        display: flex; flex-direction: column; border: 1px solid var(--border);
    }
    .dog-card:hover { transform: translateY(-10px); box-shadow: var(--shadow-hover); border-color: var(--pn-purple-light); }
    
    .card-img-wrap { position: relative; height: 280px; overflow: hidden; }
    .card-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
    .dog-card:hover .card-img-wrap img { transform: scale(1.08); }
    
    .badge { position: absolute; top: 1rem; left: 1rem; background: rgba(255,255,255,0.9); backdrop-filter: blur(4px); padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.75rem; font-weight: 700; color: var(--pn-purple-dark); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    
    .card-content { padding: 1.5rem; }
    .card-content h3 { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--text-dark); margin-bottom: 0.25rem; }
    .card-breed { color: var(--pn-purple); font-weight: 700; font-size: 0.9rem; margin-bottom: 1rem; }
    
    .card-tags { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
    .tag { background: var(--bg-main); border: 1px solid var(--border); padding: 0.3rem 0.8rem; border-radius: 8px; font-size: 0.8rem; color: var(--text-muted); font-weight: 500; }
    
    .card-btn { display: flex; justify-content: space-between; align-items: center; width: 100%; padding-top: 1rem; border-top: 1px solid var(--border); color: var(--text-dark); font-weight: 700; font-size: 0.9rem; transition: color 0.2s; }
    .dog-card:hover .card-btn { color: var(--pn-purple); }

    /* CTA BANNER */
    .cta-banner { 
        background: var(--pn-purple); border-radius: var(--radius-lg); padding: 5rem 2rem; 
        text-align: center; color: var(--white); margin: 6rem 0; position: relative; overflow: hidden;
    }
    .cta-banner::before {
        content: ''; position: absolute; top: -50%; left: -10%; width: 400px; height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%); border-radius: 50%;
    }
</style>

<div class="container">
    <section class="hero reveal">
        <div class="hero-grid">
            <div>
                <div class="hero-badge">✨ 100% Free Matchmaking</div>
                <h1>Find a dog that <span>truly fits</span> your lifestyle.</h1>
                <p>Stop scrolling endlessly. Take our 2-minute quiz and let our algorithm match you with the perfect companion waiting for a home.</p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="/survey.php" class="btn btn-primary" style="padding: 1.1rem 2.5rem; font-size: 1.1rem;">Take the Match Quiz</a>
                    <a href="/breed.php" class="btn" style="background: var(--surface); color: var(--text-dark); border: 2px solid var(--border);">Browse Directory</a>
                </div>
            </div>
            
            <div class="hero-visuals">
                <div class="floating-card">
                    <div class="pulse-dot"></div>
                    <div>1,420+ <span>Dogs Available Today</span></div>
                </div>
                <img src="https://images.unsplash.com/photo-1543466835-00a73410a2c0?w=800&q=80" class="img-main" alt="Happy adopted dog">
                <img src="https://images.unsplash.com/photo-1583511655826-05700d52f4d9?w=400&q=80" class="img-accent" alt="Cute french bulldog">
            </div>
        </div>
    </section>

    <div class="stats-container reveal delay-1">
        <div class="stat-item"><div class="stat-num">2,400+</div><div class="stat-label">Happy Adoptions</div></div>
        <div class="stat-item"><div class="stat-num">180+</div><div class="stat-label">Partner Shelters</div></div>
        <div class="stat-item"><div class="stat-num">98%</div><div class="stat-label">Match Success Rate</div></div>
    </div>

    <section style="padding: 4rem 0;">
        <div class="section-header reveal">
            <p style="color: var(--pn-purple); font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.85rem; margin-bottom: 0.5rem;">Meet Your Best Friend</p>
            <h2>Ready to go home today.</h2>
        </div>
        
        <div class="dog-grid">
            <?php $delay = 1; foreach ($dogs as $dog): ?>
            <a href="/dog.php?id=<?= (int)$dog['id'] ?>" class="dog-card reveal delay-<?= $delay++ ?>">
                <div class="card-img-wrap">
                    <img src="<?= htmlspecialchars($dog['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($dog['name']) ?>">
                    <div class="badge">Available Now</div>
                </div>
                <div class="card-content">
                    <h3><?= htmlspecialchars($dog['name']) ?></h3>
                    <div class="card-breed"><?= htmlspecialchars($dog['breed_name']) ?></div>
                    <div class="card-tags">
                        <span class="tag"><?= htmlspecialchars($dog['age']) ?></span>
                        <span class="tag"><?= htmlspecialchars($dog['gender']) ?></span>
                        <span class="tag">📍 <?= htmlspecialchars($dog['location']) ?></span>
                    </div>
                    <div class="card-btn">
                        <span>Meet <?= htmlspecialchars($dog['name']) ?></span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 4rem;" class="reveal delay-2">
            <a href="/breed.php" class="btn" style="background: var(--surface); border: 2px solid var(--border); color: var(--text-dark); padding: 1rem 3rem;">View All Dogs</a>
        </div>
    </section>

    <div class="cta-banner reveal">
        <h2 style="font-family: 'Playfair Display', serif; font-size: 2.8rem; margin-bottom: 1rem;">Let's find your match.</h2>
        <p style="font-size: 1.1rem; opacity: 0.9; max-width: 500px; margin: 0 auto 2.5rem;">Take our interactive lifestyle quiz and we will pair you with the perfect dog waiting in a shelter near you.</p>
        <a href="/survey.php" class="btn" style="background: var(--white); color: var(--pn-purple-dark); font-size: 1.1rem; padding: 1.1rem 3rem; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">Start the Match Quiz</a>
    </div>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>
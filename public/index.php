<?php
$pageTitle = 'Foredog - Premium Pet Adoption';
require_once __DIR__ . '/../src/Database.php';
require __DIR__ . '/../templates/header.php';

$db = Database::getInstance();
$dogs = $db->query('SELECT * FROM dogs WHERE status = "available" LIMIT 3')->fetchAll();
?>
<style>
    /* HERO SECTION */
    .hero { padding: 5rem 0; position: relative; overflow: hidden; background: var(--bg-main); }
    .hero-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center; }
    @media (max-width: 900px) { .hero-grid { grid-template-columns: 1fr; text-align: center; } }
    
    .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: var(--pn-purple-light); color: var(--pn-purple-dark); padding: 6px 16px; border-radius: 50px; font-weight: 700; font-size: 0.8rem; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
    .hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(3rem, 5.5vw, 4.2rem); line-height: 1.1; color: var(--text-dark); margin-bottom: 1.5rem; }
    .hero h1 span { color: var(--pn-purple); border-bottom: 4px solid var(--pn-purple-light); display: inline-block; line-height: 0.9; }
    .hero p { font-size: 1.15rem; color: var(--text-muted); margin-bottom: 2.5rem; max-width: 480px; }
    @media (max-width: 900px) { .hero p { margin: 0 auto 2.5rem; } }

    /* MOCKUP EXACT VISUALS */
    .hero-visuals { position: relative; height: 520px; width: 100%; display: flex; justify-content: flex-end; align-items: center; }
    .img-main { position: absolute; right: 0; top: 0; width: 75%; height: 95%; object-fit: cover; border-radius: 40px; box-shadow: var(--shadow-hover); z-index: 1; }
    .img-accent { position: absolute; left: 0; bottom: 5%; width: 50%; aspect-ratio: 1/1; object-fit: cover; border-radius: 50%; border: 12px solid var(--bg-main); box-shadow: var(--shadow-soft); z-index: 3; animation: float 6s ease-in-out infinite; }
    .floating-card { position: absolute; top: 10%; left: -5%; background: var(--surface); padding: 1rem 1.5rem; border-radius: var(--radius-md); box-shadow: var(--shadow-soft); z-index: 4; display: flex; align-items: center; gap: 12px; animation: float 5s ease-in-out infinite reverse; }
    .floating-card div { font-size: 0.85rem; font-weight: 700; color: var(--text-dark); }
    .pulse-dot { width: 10px; height: 10px; background: #10B981; border-radius: 50%; box-shadow: 0 0 0 4px rgba(16,185,129,0.2); }

    @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-12px); } }

    /* STATS */
    .stats-container { background: var(--surface); border-radius: var(--radius-lg); padding: 2.5rem; box-shadow: var(--shadow-soft); display: flex; justify-content: space-around; flex-wrap: wrap; gap: 2rem; margin: -20px auto 5rem; position: relative; z-index: 10; border: 1px solid var(--border); max-width: 900px; }
    .stat-num { font-family: 'Playfair Display', serif; font-size: 2.2rem; color: var(--pn-purple); font-weight: 700; text-align: center; }
    .stat-label { font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem; margin-top: 0.25rem; text-align: center; }

    /* WHAT WE OFFER */
    .offer-section { background: var(--bg-alt); padding: 6rem 0; position: relative; }
    .offer-section h2 { text-align: center; font-family: 'DM Sans', sans-serif; font-size: 2rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 1rem; color: var(--text-dark); }
    .offer-section > p { text-align: center; max-width: 800px; margin: 0 auto 4rem; color: var(--text-muted); font-size: 1.05rem; }
    
    .offer-grid { display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 3rem; }
    @media (max-width: 900px) { .offer-grid { grid-template-columns: 1fr; gap: 2rem; } .offer-center-img { grid-row: 1; } }
    
    .offer-list { display: flex; flex-direction: column; gap: 2rem; }
    .offer-item { display: flex; align-items: center; gap: 1rem; font-weight: 700; font-size: 1rem; color: var(--text-dark); }
    .offer-item.right { flex-direction: row-reverse; }
    @media (max-width: 900px) { .offer-item, .offer-item.right { flex-direction: row; justify-content: center; } }
    .offer-icon { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: var(--pn-purple-light); color: var(--pn-purple-dark); border-radius: 50%; font-size: 1.2rem; }
    
    .offer-center-img { width: 340px; height: 340px; border-radius: 50%; overflow: hidden; margin: 0 auto; box-shadow: var(--shadow-hover); border: 8px solid var(--surface); }
    .offer-center-img img { width: 100%; height: 100%; object-fit: cover; }

    /* TIMELINE */
    .how-it-works { padding: 8rem 0; text-align: center; background: var(--surface); position: relative; }
    .how-it-works h2 { font-family: 'DM Sans', sans-serif; font-size: 2.2rem; font-weight: 800; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 5rem; color: var(--text-dark); }

    .timeline-container { position: relative; max-width: 1000px; margin: 0 auto; padding-top: 1rem; }
    .timeline-track { position: absolute; top: 38px; left: 16.66%; width: 66.66%; height: 3px; background: var(--border); z-index: 1; }
    .timeline-progress { position: absolute; top: 38px; left: 16.66%; width: 0%; height: 3px; background: var(--pn-purple); z-index: 2; transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1); }
    
    .timeline-container.run-animation .timeline-progress { width: 66.66%; }

    .timeline-steps { display: flex; justify-content: space-between; position: relative; z-index: 3; }
    .step { flex: 1; padding: 0 1rem; display: flex; flex-direction: column; align-items: center; }

    .dot { width: 36px; height: 36px; background: var(--border); border-radius: 50%; margin-bottom: 2rem; border: 8px solid var(--surface); box-sizing: content-box; transition: all 0.5s ease; opacity: 0.5; }
    .step-content { opacity: 0; transform: translateY(20px); transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); max-width: 260px; }
    
    .step h3 { font-family: 'DM Sans', sans-serif; font-size: 1.4rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-dark); }
    .step p { font-size: 0.95rem; color: var(--text-muted); line-height: 1.6; }

    .timeline-container.run-animation .step:nth-child(1) .dot { background: var(--pn-purple); box-shadow: 0 0 0 8px var(--pn-purple-light); opacity: 1; border-color: transparent; }
    .timeline-container.run-animation .step:nth-child(1) .step-content { opacity: 1; transform: translateY(0); }

    .timeline-container.run-animation .step:nth-child(2) .dot { transition-delay: 0.6s; background: var(--pn-purple); box-shadow: 0 0 0 8px var(--pn-purple-light); opacity: 1; border-color: transparent; }
    .timeline-container.run-animation .step:nth-child(2) .step-content { transition-delay: 0.6s; opacity: 1; transform: translateY(0); }

    .timeline-container.run-animation .step:nth-child(3) .dot { transition-delay: 1.2s; background: var(--pn-purple); box-shadow: 0 0 0 8px var(--pn-purple-light); opacity: 1; border-color: transparent; }
    .timeline-container.run-animation .step:nth-child(3) .step-content { transition-delay: 1.2s; opacity: 1; transform: translateY(0); }

    @media (max-width: 768px) {
        .how-it-works { padding: 5rem 2rem; text-align: left; }
        .how-it-works h2 { text-align: center; margin-bottom: 3rem; }
        .timeline-track { top: 16px; bottom: 0; left: 26px; width: 3px; height: calc(100% - 100px); }
        .timeline-progress { top: 16px; left: 26px; width: 3px; height: 0%; transition: height 1.5s cubic-bezier(0.4, 0, 0.2, 1); }
        .timeline-container.run-animation .timeline-progress { height: calc(100% - 100px); width: 3px; }
        .timeline-steps { flex-direction: column; gap: 3rem; }
        .step { flex-direction: row; text-align: left; gap: 2rem; padding: 0; align-items: flex-start; }
        .dot { margin-bottom: 0; flex-shrink: 0; width: 32px; height: 32px; z-index: 4; border-width: 6px; }
        .step-content { transform: translateX(20px); max-width: 100%; }
        .timeline-container.run-animation .step .step-content { transform: translateX(0); }
    }

    /* FEATURED DOGS */
    .section-header { text-align: center; margin-bottom: 3.5rem; }
    .section-header p { font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.9rem; margin-bottom: 0.5rem; color: var(--text-dark); }
    .section-header h2 { font-family: 'DM Sans', sans-serif; font-size: 2.2rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-dark); }
    
    .dog-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
    .dog-card { background: var(--surface); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-soft); transition: all 0.3s ease; text-decoration: none; display: flex; flex-direction: column; border: 1px solid var(--border); }
    .dog-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-hover); border-color: var(--pn-purple-light); }
    
    /* FIX: Bulletproof Square Grid Images (No Stretching!) */
    .card-img-wrap { position: relative; width: 100%; padding-top: 100%; overflow: hidden; background: var(--border); }
    .card-img-wrap img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover !important; display: block; transition: transform 0.5s ease; }
    
    .dog-card:hover .card-img-wrap img { transform: scale(1.05); }
    
    .badge-top { position: absolute; top: 1rem; left: 1rem; background: var(--surface); color: var(--pn-purple); font-size: 0.75rem; font-weight: 800; padding: 0.4rem 1rem; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 2; }
    .badge-gender { position: absolute; top: 1rem; right: 1rem; background: var(--text-dark); color: var(--surface); font-size: 0.75rem; font-weight: 700; padding: 0.4rem 1rem; border-radius: 50px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 2; }
    
    .card-content { padding: 1.25rem 1.5rem; text-align: center; }
    .card-content h3 { font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--text-dark); margin-bottom: 0.25rem; }
    .card-breed { color: var(--pn-purple); font-size: 0.95rem; font-weight: 700; margin-bottom: 1rem; }

    /* SUPPORT SECTION */
    .support-section { padding: 6rem 0; background: var(--bg-alt); }
    .support-section h2 { text-align: center; font-family: 'DM Sans', sans-serif; font-size: 2rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 4rem; color: var(--text-dark); }
    .support-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
    @media (max-width: 768px) { .support-cards { grid-template-columns: 1fr; gap: 4rem; } }
    
    .s-card { border: 2px solid var(--text-dark); border-radius: var(--radius-lg); padding: 3rem 2rem; text-align: center; position: relative; background: var(--surface); }
    .s-card h3 { font-family: 'DM Sans', sans-serif; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; font-size: 1.2rem; margin-bottom: 1rem; color: var(--text-dark); }
    .s-card p { color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; }
    .s-card-img { position: absolute; bottom: -30px; left: -20px; width: 120px; height: 120px; border-radius: 50%; object-fit: cover; }
    .s-card-img.right { left: auto; right: -20px; width: 100px; height: 100px; }

    /* CTA BANNER */
    .cta-banner { background: var(--pn-purple-dark); border-radius: var(--radius-lg); padding: 4.5rem 2rem; text-align: center; color: var(--white); margin: 6rem auto; max-width: 1000px; position: relative; overflow: hidden; box-shadow: var(--shadow-hover); }
    .cta-banner::before { content: ''; position: absolute; top: -50%; right: -10%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%); border-radius: 50%; pointer-events: none; }
    .cta-banner h2 { font-family: 'Playfair Display', serif; font-size: 2.8rem; margin-bottom: 1rem; }
</style>

<div class="container">
    <section class="hero reveal">
        <div class="hero-grid">
            <div style="z-index: 10;">
                <div class="hero-badge">✨ 100% Free Matchmaking</div>
                <h1>Find a dog that <span>truly fits</span> your lifestyle.</h1>
                <p>Stop searching blindly. Take our 2-minute quiz and let our algorithm pair you with the perfect companion waiting in a local shelter.</p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="/survey.php" class="btn btn-primary" style="padding: 1.1rem 2.5rem; font-size: 1.05rem;">Take the Match Quiz</a>
                    <a href="/breed.php" class="btn" style="background: var(--surface); color: var(--text-dark); border: 2px solid var(--border);">Browse Directory</a>
                </div>
            </div>
            
            <div class="hero-visuals">
                <div class="floating-card">
                    <div class="pulse-dot"></div>
                    <div>1,420+ <span style="font-weight:400; color:var(--text-muted);">Dogs Available Today</span></div>
                </div>
                <img src="https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=800&q=80" class="img-main" alt="Happy dog" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1543466835-00a73410a2c0?w=800&q=80';">
                <img src="https://images.unsplash.com/photo-1583511655826-05700d52f4d9?w=400&q=80" class="img-accent" alt="French bulldog" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1555680202-c86f0e12f086?w=400&q=80';">
            </div>
        </div>
    </section>

    <div class="stats-container reveal delay-1">
        <div class="stat-item"><div class="stat-num">2,400+</div><div class="stat-label">Happy Adoptions</div></div>
        <div class="stat-item"><div class="stat-num">180+</div><div class="stat-label">Partner Shelters</div></div>
        <div class="stat-item"><div class="stat-num">98%</div><div class="stat-label">Match Success Rate</div></div>
    </div>
</div>

<section class="offer-section">
    <div class="container reveal">
        <h2>What We Offer</h2>
        <p>At our pet adoption center, we offer a loving second chance for animals in need. Discover a variety of dogs waiting for a forever home. Our team is here to guide you through every step of the process.</p>
        
        <div class="offer-grid">
            <div class="offer-list">
                <div class="offer-item"><div class="offer-icon">🐾</div> Healthy Pets</div>
                <div class="offer-item"><div class="offer-icon">🐾</div> Vaccinated</div>
                <div class="offer-item"><div class="offer-icon">🐾</div> Basic Training</div>
            </div>
            <div class="offer-center-img">
                <img src="https://images.unsplash.com/photo-1544568100-847a948585b9?w=500&auto=format&fit=crop&q=80" alt="Happy Beagle" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=500&q=80';">
            </div>
            <div class="offer-list">
                <div class="offer-item right"><div class="offer-icon">🐾</div> Clear Background Info</div>
                <div class="offer-item right"><div class="offer-icon">🐾</div> Helps Reduce Strays</div>
                <div class="offer-item right"><div class="offer-icon">🐾</div> Post-Adoption Support</div>
            </div>
        </div>
    </div>
</section>

<section class="how-it-works">
    <div class="container">
        <h2>How It Works</h2>
        <div class="timeline-container" id="timelineContainer">
            <div class="timeline-track"></div>
            <div class="timeline-progress"></div>
            
            <div class="timeline-steps">
                <div class="step">
                    <div class="dot"></div>
                    <div class="step-content">
                        <h3>Find Your Pet</h3>
                        <p>Browse through a variety of adorable pets looking for a loving home. Use filters to find your perfect match.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="dot"></div>
                    <div class="step-content">
                        <h3>Know Your Pet</h3>
                        <p>Learn important details about your chosen pet, including behavior, care needs, and personality traits.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="dot"></div>
                    <div class="step-content">
                        <h3>Take Your Pet Home</h3>
                        <p>Complete the adoption process and welcome your new furry friend into your life with guidance and support.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section style="padding: 4rem 0; background: var(--bg-main);">
    <div class="container">
        <div class="section-header reveal">
            <p>Waiting Adoption</p>
            <h2>Ready to go home today.</h2>
        </div>
        
        <div class="dog-grid">
            <?php $delay = 1; foreach ($dogs as $dog): ?>
            <a href="/dog.php?id=<?= (int)$dog['id'] ?>" class="dog-card reveal delay-<?= $delay++ ?>">
                <div class="card-img-wrap">
                    <img src="<?= htmlspecialchars($dog['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($dog['name']) ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1543466835-00a73410a2c0?w=600&q=80';">
                    <div class="badge-top">Available</div>
                    <div class="badge-gender"><?= htmlspecialchars($dog["gender"]) ?></div>
                </div>
                <div class="card-content">
                    <h3><?= htmlspecialchars($dog['name']) ?></h3>
                    <p class="card-breed"><?= htmlspecialchars($dog['age']) ?> · <?= htmlspecialchars($dog['breed_name']) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: 4rem;" class="reveal delay-2">
            <a href="/breed.php" class="btn" style="background: var(--surface); border: 2px solid var(--border); color: var(--text-dark);">View All Dogs</a>
        </div>
    </div>
</section>

<section class="support-section">
    <div class="container reveal">
        <h2>Support</h2>
        <div class="support-cards">
            <div class="s-card">
                <h3>Adopt</h3>
                <p>Give a loving animal a second chance and a forever home today. Experience the joy of saving a life.</p>
                <img src="https://images.unsplash.com/photo-1583511655826-05700d52f4d9?w=300&q=80" class="s-card-img" alt="Adopt" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1544568100-847a948585b9?w=300&q=80';">
            </div>
            <div class="s-card">
                <h3>Donate</h3>
                <p>Your contributions help us feed, treat, and house animals in need. Every little bit makes a massive difference.</p>
            </div>
            <div class="s-card">
                <h3>Foster</h3>
                <p>Provide temporary love and shelter while we find them a permanent family. Open your home to save a pet.</p>
                <img src="https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=300&q=80" class="s-card-img right" alt="Foster" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1543466835-00a73410a2c0?w=300&q=80';">
            </div>
        </div>
    </div>
</section>

<div class="container">
    <div class="cta-banner reveal">
        <h2 style="font-family: 'Playfair Display', serif; font-size: 2.8rem; margin-bottom: 1rem;">Let's find your match.</h2>
        <p style="font-size: 1.15rem; opacity: 0.9; max-width: 500px; margin: 0 auto 2.5rem;">Take our interactive lifestyle quiz and we will pair you with the perfect dog waiting in a shelter near you.</p>
        <a href="/survey.php" class="btn" style="background: var(--accent-yellow); color: var(--text-dark); font-size: 1.1rem; padding: 1.1rem 3rem; box-shadow: 0 10px 30px rgba(251, 191, 36, 0.3);">Start the Match Quiz</a>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const timelineContainer = document.getElementById('timelineContainer');
    if (!timelineContainer) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('run-animation');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.4 });

    observer.observe(timelineContainer);
});
</script>

<?php require __DIR__ . '/../templates/footer.php'; ?>׳
<?php
$pageTitle = 'Foredog - Find Your Perfect Dog';
require_once __DIR__ . '/../src/Database.php';
require __DIR__ . '/../templates/header.php';
$db = Database::getInstance();
$dogs = $db->query('SELECT * FROM dogs WHERE status = "available" LIMIT 6')->fetchAll();
?>
<style>
.hero { min-height:88vh; display:flex; align-items:center; background:var(--bark); position:relative; overflow:hidden; }
.hero::before { content:''; position:absolute; inset:0; background:url('https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=1600&q=70') center/cover no-repeat; opacity:.18; }
.hero-content { position:relative; z-index:1; max-width:680px; padding:4rem 2rem; margin:0 auto; text-align:center; }
.hero-eyebrow { display:inline-block; background:var(--amber); color:var(--white); font-size:.8rem; font-weight:500; letter-spacing:.12em; text-transform:uppercase; padding:.35rem 1rem; border-radius:50px; margin-bottom:1.5rem; }
.hero h1 { font-family:'Playfair Display',serif; font-size:clamp(2.5rem,6vw,4rem); color:var(--white); line-height:1.15; margin-bottom:1.25rem; }
.hero h1 em { color:var(--amber-light); font-style:italic; }
.hero p { color:rgba(255,255,255,.7); font-size:1.125rem; margin-bottom:2.5rem; max-width:480px; margin-left:auto; margin-right:auto; }
.hero-actions { display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; }
.stats-strip { background:var(--amber); padding:1.25rem 2rem; display:flex; justify-content:center; gap:3rem; flex-wrap:wrap; }
.stat { color:var(--white); text-align:center; }
.stat strong { display:block; font-size:1.5rem; font-weight:700; }
.stat span { font-size:.8rem; opacity:.85; text-transform:uppercase; letter-spacing:.08em; }
.section-title { font-family:'Playfair Display',serif; font-size:clamp(1.75rem,4vw,2.5rem); margin-bottom:.5rem; }
.dog-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:1.75rem; margin-top:2rem; }
.dog-card { background:var(--white); border-radius:var(--radius); overflow:hidden; transition:transform .25s,box-shadow .25s; text-decoration:none; color:inherit; box-shadow:0 2px 12px rgba(44,24,16,.06); display:block; }
.dog-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(44,24,16,.14); }
.dog-card img { width:100%; height:220px; object-fit:cover; }
.dog-card-body { padding:1.25rem 1.5rem 1.5rem; }
.dog-card-body h3 { font-family:'Playfair Display',serif; font-size:1.3rem; margin-bottom:.35rem; }
.dog-meta { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:.6rem; }
.dog-tag { background:var(--sand); color:var(--muted); font-size:.75rem; padding:.2rem .6rem; border-radius:50px; }
.steps { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:2rem; margin-top:2.5rem; }
.step { text-align:center; }
.step-num { width:52px; height:52px; background:var(--amber); color:var(--white); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.25rem; font-weight:700; margin:0 auto 1rem; }
.step h4 { font-family:'Playfair Display',serif; font-size:1.1rem; margin-bottom:.4rem; }
.step p { color:var(--muted); font-size:.9rem; }
.cta-banner { background:var(--bark); border-radius:20px; padding:4rem 2rem; text-align:center; margin:5rem 0; }
.cta-banner h2 { font-family:'Playfair Display',serif; font-size:2.25rem; color:var(--white); margin-bottom:1rem; }
.cta-banner p { color:rgba(255,255,255,.65); margin-bottom:2rem; max-width:440px; margin-left:auto; margin-right:auto; }
</style>
<section class="hero">
  <div class="hero-content">
    <span class="hero-eyebrow">100% Free to Browse</span>
    <h1>Find Your <em>Perfect</em> Companion</h1>
    <p>Answer a few questions and we will match you with dogs looking for exactly the kind of home you offer.</p>
    <div class="hero-actions">
      <a href="/survey.php" class="btn btn-primary" style="font-size:1.1rem;padding:.9rem 2.5rem;">Take the Quiz</a>
      <a href="/breed.php" class="btn btn-outline" style="color:var(--white);border-color:rgba(255,255,255,.4);">Browse All Dogs</a>
    </div>
  </div>
</section>
<div class="stats-strip">
  <div class="stat"><strong>2,400+</strong><span>Dogs Rehomed</span></div>
  <div class="stat"><strong>180+</strong><span>Partner Shelters</span></div>
  <div class="stat"><strong>98%</strong><span>Match Satisfaction</span></div>
</div>
<section style="padding:5rem 0 2rem;">
  <div class="container">
    <p style="text-transform:uppercase;letter-spacing:.1em;font-size:.8rem;font-weight:500;color:var(--amber);margin-bottom:.25rem;">Available Now</p>
    <h2 class="section-title">Dogs Waiting for You</h2>
    <p style="color:var(--muted);margin-bottom:2rem;">Every dog has been photographed, vetted, and is ready to meet you.</p>
    <div class="dog-grid">
      <?php foreach ($dogs as $dog): ?>
      <a href="/dog.php?id=<?= (int)$dog['id'] ?>" class="dog-card">
        <img src="<?= htmlspecialchars($dog['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($dog['name']) ?>">
        <div class="dog-card-body">
          <h3><?= htmlspecialchars($dog['name']) ?></h3>
          <div class="dog-meta">
            <span class="dog-tag"><?= htmlspecialchars($dog['breed_name']) ?></span>
            <span class="dog-tag"><?= htmlspecialchars($dog['age']) ?></span>
            <span class="dog-tag"><?= htmlspecialchars($dog['gender']) ?></span>
          </div>
          <p style="color:var(--muted);font-size:.85rem;">📍 <?= htmlspecialchars($dog['location']) ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:3rem;"><a href="/breed.php" class="btn btn-outline">View All Available Dogs</a></div>
  </div>
</section>
<section style="padding:5rem 0;background:var(--sand);">
  <div class="container" style="text-align:center;">
    <h2 class="section-title">How It Works</h2>
    <p style="color:var(--muted);margin-bottom:2rem;">Adoption made simple, in three easy steps.</p>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><h4>Take the Quiz</h4><p>Tell us about your lifestyle and home. Takes 2 minutes.</p></div>
      <div class="step"><div class="step-num">2</div><h4>Meet Your Match</h4><p>We surface dogs that fit your life.</p></div>
      <div class="step"><div class="step-num">3</div><h4>Connect & Adopt</h4><p>Get the shelter contact and reach out directly.</p></div>
    </div>
  </div>
</section>
<div class="container">
  <div class="cta-banner">
    <h2>Ready to Meet Your Dog?</h2>
    <p>Our matching quiz takes 2 minutes and pairs you with dogs that truly fit your life.</p>
    <a href="/survey.php" class="btn btn-primary" style="font-size:1.1rem;padding:.9rem 2.5rem;">Start the Quiz - It is Free</a>
  </div>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>

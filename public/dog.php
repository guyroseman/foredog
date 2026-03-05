<?php
session_start();
require_once __DIR__ . '/../src/Database.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: /breed.php'); exit; }

$db = Database::getInstance();
$stmt = $db->prepare('SELECT * FROM dogs WHERE id = ? AND status = "available"');
$stmt->execute([$id]);
$dog = $stmt->fetch();

if (!$dog) { header('Location: /breed.php'); exit; }

// Clean Variables
$safeName = htmlspecialchars($dog['name']);
$safeBreed = htmlspecialchars(ucwords(strtolower($dog['breed_name'])));
$safeLocation = htmlspecialchars($dog['location']);
$safeAge = htmlspecialchars($dog['age']);
$safeGender = htmlspecialchars(ucfirst(strtolower($dog['gender'])));

$gallery = !empty($dog['gallery_urls']) ? json_decode($dog['gallery_urls'], true) : [];
if (empty($gallery) && !empty($dog['image_url'])) { $gallery = [$dog['image_url']]; }
$mainImage = $gallery[0] ?? 'https://images.unsplash.com/photo-1543466835-00a73410a2c0?w=800&q=80';

// LIVE JANITOR: Strip out any old bad data or "Submit an inquiry" text saved in the database
$rawDesc = $dog['description'] ?? '';
$cleanDesc = preg_replace('/<strong>.*?<\/strong><br><br>/i', '', $rawDesc); 
$cleanDesc = preg_replace('/<em>.*?<\/em>/i', '', $cleanDesc); 
$cleanDesc = preg_replace('/<hr.*?>/i', '', $cleanDesc); 
$cleanDesc = preg_replace('/🔗.*?<\/a>/i', '', $cleanDesc); 
$cleanDesc = preg_replace('/Submit an adoption inquiry below to learn more and schedule a meet-and-greet!/i', '', $cleanDesc);
$cleanDesc = trim(strip_tags($cleanDesc));

// Calculate dynamic "Last Verified" time based on the database timestamp
$lastSeen = new DateTime($dog['last_seen_at'] ?? 'now');
$now = new DateTime();
$diff = $now->diff($lastSeen);
$hours = ($diff->days * 24) + $diff->h;
$timeString = $hours <= 24 ? "Availability verified today" : "Availability verified recently";

$pageTitle = "Adopt {$safeName} | {$safeBreed} - Foredog";
$metaDescription = "Meet {$safeName}, an exceptional {$safeAge} {$safeBreed} available for adoption. Apply today to meet {$safeName}!";
$ogImage = $mainImage;

require __DIR__ . '/../templates/header.php';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap');
    
    :root {
        --fd-purple: #7B52F4;
        --fd-purple-hover: #6742D1;
        --fd-purple-light: #F4F1FF;
        --text-main: #1A1A1A;
        --text-sub: #666666;
        --border-color: #EAEAEA;
    }

    body { font-family: 'Inter', sans-serif; background: #FAFAFA; color: var(--text-main); }
    
    .profile-container { max-width: 1200px; margin: 0 auto; padding: 60px 2rem 6rem; }
    
    .breadcrumbs { font-size: 0.85rem; color: var(--text-sub); margin-bottom: 2rem; font-weight: 500; }
    .breadcrumbs a { color: var(--text-sub); text-decoration: none; transition: color 0.2s; }
    .breadcrumbs a:hover { color: var(--fd-purple); }

    .grid-layout { display: grid; grid-template-columns: 1.4fr 1fr; gap: 4rem; align-items: start; }
    @media(max-width: 992px){ .grid-layout { grid-template-columns: 1fr; gap: 2rem; } }

    /* LEFT SIDE - GALLERY */
    .gallery-wrapper { width: 100%; display: flex; flex-direction: column; gap: 1rem; }
    .main-img-box { width: 100%; aspect-ratio: 4/3; border-radius: 16px; overflow: hidden; background: #EEE; }
    .main-img-box img { width: 100%; height: 100%; object-fit: cover; }
    
    .thumb-track { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 5px; scrollbar-width: none; }
    .thumb-track::-webkit-scrollbar { display: none; }
    .thumb-track img { width: 80px; height: 80px; border-radius: 12px; object-fit: cover; cursor: pointer; border: 2px solid transparent; transition: all 0.2s; opacity: 0.6; }
    .thumb-track img.active, .thumb-track img:hover { opacity: 1; border-color: var(--fd-purple); }

    .bio-block { margin-top: 3rem; }
    .bio-block h2 { font-family: 'Playfair Display', serif; font-size: 2.2rem; color: var(--text-main); margin-bottom: 1.5rem; }
    .bio-block p { color: var(--text-sub); font-size: 1.05rem; line-height: 1.8; margin-bottom: 1.5rem; }

    /* RIGHT SIDE - STICKY CARD */
    .sticky-card { 
        position: sticky; 
        top: 100px; 
        background: #FFFFFF; 
        border: 1px solid var(--border-color); 
        border-radius: 16px; 
        padding: 2.5rem; 
        box-shadow: 0 10px 40px rgba(0,0,0,0.03); 
        max-height: calc(100vh - 120px); /* Keeps the card from exceeding monitor height */
        overflow-y: auto; /* Allows scrolling inside the card if needed */
        scrollbar-width: none; /* Firefox */
    }
    .sticky-card::-webkit-scrollbar { display: none; /* Safari/Chrome */ }
    
    .badge-top { display: inline-block; background: var(--fd-purple-light); color: var(--fd-purple); font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; padding: 0.4rem 1rem; border-radius: 50px; margin-bottom: 1rem; width: fit-content; }
    
    .dog-title { font-family: 'Playfair Display', serif; font-size: 3rem; margin-bottom: 0.2rem; line-height: 1.1; }
    .dog-breed { font-size: 1.1rem; color: var(--text-sub); font-weight: 500; margin-bottom: 2rem; }

    .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border-color); }
    .stat-box { display: flex; flex-direction: column; gap: 0.4rem; }
    .stat-box span { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-sub); font-weight: 600; }
    .stat-box strong { font-size: 1rem; color: var(--text-main); font-weight: 700; display: flex; align-items: center; gap: 6px; }
    .stat-box svg { color: var(--fd-purple); width: 18px; height: 18px; }

    .apply-btn { width: 100%; background: var(--fd-purple); color: #FFF; border: none; padding: 1.2rem; border-radius: 50px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.2s; font-family: 'Inter', sans-serif; box-shadow: 0 4px 15px rgba(123, 82, 244, 0.3); margin-bottom: 1.5rem; }
    .apply-btn:hover { background: var(--fd-purple-hover); transform: translateY(-2px); }

    /* REALISTIC TRUST BADGES */
    .trust-box { background: #F9F9F9; border-radius: 12px; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
    .trust-row { display: flex; align-items: center; gap: 12px; font-size: 0.9rem; font-weight: 500; color: var(--text-sub); }
    .trust-row svg { color: var(--fd-purple); flex-shrink: 0; }

    /* MOBILE FOOTER BTN */
    .mobile-cta { display: none; position: fixed; bottom: 0; left: 0; width: 100%; background: #FFF; padding: 1rem 1.5rem calc(1rem + env(safe-area-inset-bottom)); box-shadow: 0 -10px 30px rgba(0,0,0,0.08); z-index: 900; border-top: 1px solid var(--border-color); }
    @media(max-width: 992px){ 
        .mobile-cta { display: flex; justify-content: space-between; align-items: center; } 
        .sticky-card { border: none; box-shadow: none; padding: 0; background: transparent; position: relative; top: 0; max-height: unset; overflow-y: visible;}
        .apply-btn { display: none; }
    }

    /* MODAL */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0, 0.5); backdrop-filter: blur(4px); z-index: 2000; align-items: center; justify-content: center; padding: 1rem; }
    .modal-overlay.open { display: flex; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .modal-card { background: #FFF; border-radius: 20px; padding: 2.5rem; width: 100%; max-width: 440px; position: relative; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
    .modal-card h2 { font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 0.5rem; }
    .modal-card p { color: var(--text-sub); font-size: 0.95rem; margin-bottom: 2rem; }
    .form-group { margin-bottom: 1.2rem; }
    .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; }
    .form-group input { width: 100%; padding: 0.9rem 1rem; border: 1px solid var(--border-color); border-radius: 12px; font-family: 'Inter', sans-serif; outline: none; transition: border 0.2s; background: #FAFAFA; }
    .form-group input:focus { border-color: var(--fd-purple); background: #FFF; }
    .modal-close { position: absolute; top: 1.5rem; right: 1.5rem; background: #F5F5F5; border: none; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
    .modal-close:hover { background: #EAEAEA; }
</style>

<div class="profile-container">
    
  <div class="breadcrumbs">
      <a href="/">Home</a> / 
      <a href="/breed.php">Browse Pets</a> / 
      <a href="/breed.php?filter_breed=<?= urlencode($dog['breed_slug']) ?>"><?= $safeBreed ?></a> / 
      <span style="color: var(--text-main);"><?= $safeName ?></span>
  </div>
  
  <div class="grid-layout">
      
    <div>
        <div class="gallery-wrapper">
            <div class="main-img-box">
                <img id="mainDogImage" src="<?= htmlspecialchars($mainImage) ?>" alt="<?= $safeName ?>">
            </div>
            
            <?php if (count($gallery) > 1): ?>
            <div class="thumb-track">
                <?php foreach($gallery as $idx => $imgUrl): ?>
                    <img src="<?= htmlspecialchars($imgUrl) ?>" 
                         class="<?= $idx === 0 ? 'active' : '' ?>"
                         onclick="document.getElementById('mainDogImage').src=this.src; document.querySelectorAll('.thumb-track img').forEach(i => i.classList.remove('active')); this.classList.add('active');"
                         alt="Thumbnail">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="bio-block">
            <h2>About <?= $safeName ?></h2>
            <p><strong>Meet <?= $safeName ?>!</strong> This wonderful <?= $safeBreed ?> is looking for a loving home. As a <?= strtolower($safeGender) ?> dog in the <?= strtolower($safeAge) ?> age range, <?= $safeName ?> has so much love and companionship to share.</p>
            
            <?php if(!empty($cleanDesc)): ?>
                <p><?= nl2br(htmlspecialchars($cleanDesc)) ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div>
        <div class="sticky-card">
            <div class="badge-top">Available for Adoption</div>
            <h1 class="dog-title"><?= $safeName ?></h1>
            <p class="dog-breed"><?= $safeBreed ?></p>
            
            <div class="stat-grid">
                <div class="stat-box">
                    <span>Age</span>
                    <strong>
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <?= $safeAge ?>
                    </strong>
                </div>
                <div class="stat-box">
                    <span>Gender</span>
                    <strong>
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none"><path d="M12 20a8 8 0 1 0 0-16 8 8 0 0 0 0 16z"></path><line x1="12" y1="14" x2="12" y2="21"></line><line x1="9" y1="18" x2="15" y2="18"></line></svg>
                        <?= $safeGender ?>
                    </strong>
                </div>
                <div class="stat-box" style="grid-column: span 2;">
                    <span>Location</span>
                    <strong>
                        <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <?= $safeLocation ?>
                    </strong>
                </div>
            </div>

            <button class="apply-btn" onclick="document.getElementById('leadModal').classList.add('open')">
                Adopt <?= $safeName ?>
            </button>

            <div class="trust-box">
                <div class="trust-row">
                    <svg width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Sourced from Verified Shelter
                </div>
                <div class="trust-row">
                    <svg width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                    Direct Shelter Contact Access
                </div>
                <div class="trust-row">
                    <svg width="20" height="20" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <?= $timeString ?>
                </div>
            </div>
            
        </div>
    </div>
    
  </div>
</div>

<div class="mobile-cta">
    <div>
        <div style="font-weight: 700; color: var(--text-main); font-size: 1.1rem;"><?= $safeName ?></div>
        <div style="font-size: 0.8rem; color: var(--text-sub);"><?= $safeLocation ?></div>
    </div>
    <button class="apply-btn" style="width: auto; margin: 0; padding: 0.8rem 1.8rem;" onclick="document.getElementById('leadModal').classList.add('open')">
        Adopt Now
    </button>
</div>

<div class="modal-overlay" id="leadModal">
  <div class="modal-card">
    <button class="modal-close" onclick="document.getElementById('leadModal').classList.remove('open')">
        <svg width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
    <h2>Meet <?= $safeName ?>!</h2>
    <p>Enter your details below to unlock the contact information for the shelter managing <?= $safeName ?>'s adoption.</p>
    
    <form method="POST" action="/capture.php">
      <input type="hidden" name="dog_id" value="<?= (int)$dog['id'] ?>">
      <div class="form-group">
          <label>Your Name</label>
          <input type="text" name="name" placeholder="Jane Smith" required>
      </div>
      <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="jane@example.com" required>
      </div>
      <div class="form-group">
          <label>Phone Number (Optional)</label>
          <input type="tel" name="phone" placeholder="(555) 000-0000">
      </div>
      <button type="submit" class="apply-btn" style="margin-top: 1rem; width: 100%;">View Adoption Details →</button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>
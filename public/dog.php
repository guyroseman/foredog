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
$pageTitle = 'Meet ' . htmlspecialchars($dog['name']) . ' - Foredog';
require __DIR__ . '/../templates/header.php';
?>
<style>
/* TIGHTENED RATIOS & WIDER CONTAINER */
.dog-profile { max-width: 1100px; margin: 2rem auto; padding: 0 2rem 6rem; }

/* FIX: Prevent Grid Blowout */
.profile-grid { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); gap: 4rem; align-items: start; }
@media(max-width: 768px){ .profile-grid { grid-template-columns: minmax(0, 1fr); gap: 2rem; } }

/* STICKY GALLERY */
.profile-gallery { position: sticky; top: 2rem; width: 100%; }

/* FIX: Bulletproof Image Container (No Stretching or Squishing!) */
.profile-img { 
    position: relative;
    width: 100%; 
    padding-top: 75%; /* Forces a perfect 4:3 Aspect Ratio */
    border-radius: var(--radius-lg); 
    overflow: hidden; 
    box-shadow: var(--shadow-soft); 
    background: var(--bg-alt); 
    margin-bottom: 1rem; 
}
.profile-img img { 
    position: absolute;
    top: 0;
    left: 0;
    width: 100%; 
    height: 100%; 
    object-fit: cover !important; 
    object-position: center; 
    display: block; 
}

.thumbnail-track { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 8px; scrollbar-width: none; }
.thumbnail-track::-webkit-scrollbar { display: none; }
.thumbnail-track img { width: 70px; min-width: 70px; height: 70px; border-radius: 12px; object-fit: cover !important; cursor: pointer; border: 2px solid transparent; opacity: 0.6; transition: all 0.2s; }
.thumbnail-track img:hover { opacity: 1; border-color: var(--pn-purple); transform: translateY(-2px); }

/* TIGHTER TEXT & TYPOGRAPHY */
.profile-badge { display: inline-block; background: var(--pn-purple-light); color: var(--pn-purple-dark); font-size: 0.7rem; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; padding: 0.35rem 1rem; border-radius: 50px; margin-bottom: 1rem; }
.profile-name { font-family: 'Playfair Display', serif; font-size: clamp(2.2rem, 4vw, 3rem); color: var(--text-dark); margin-bottom: 0.2rem; line-height: 1.1; }
.profile-breed { color: var(--text-muted); font-size: 1rem; margin-bottom: 1.5rem; font-weight: 500; }

.profile-attrs { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 2rem; }
.attr { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 0.8rem; }
.attr strong { display: block; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); margin-bottom: 0.2rem; }
.attr span { color: var(--text-dark); font-weight: 700; font-size: 0.95rem; }

/* ELEGANT DESCRIPTION FORMATTING */
.profile-desc { color: var(--text-muted); line-height: 1.6; margin-bottom: 2.5rem; font-size: 0.95rem; }
.profile-desc strong { color: var(--text-dark); font-family: 'Playfair Display', serif; font-size: 1.2rem; display: block; margin-bottom: 0.5rem; line-height: 1.3; }
.profile-desc em { display: block; margin-top: 1.5rem; font-style: italic; color: var(--text-dark); font-weight: 500; background: var(--bg-main); padding: 1rem; border-left: 3px solid var(--pn-purple); border-radius: 0 var(--radius-md) var(--radius-md) 0; }

.adopt-panel { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.5rem; text-align: center; box-shadow: var(--shadow-soft); }

/* MODAL STYLES */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(26, 19, 17, 0.6); backdrop-filter: blur(4px); z-index: 2000; align-items: center; justify-content: center; padding: 1rem; }
.modal-overlay.open { display: flex; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.modal { background: var(--surface); border-radius: var(--radius-lg); padding: 2.5rem; width: 100%; max-width: 440px; position: relative; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); transform: translateY(20px); animation: slideUp 0.3s ease forwards; }
@keyframes slideUp { to { transform: translateY(0); } }
.modal h2 { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: var(--text-dark); margin-bottom: 0.5rem; }
.modal p { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem; }
.form-group { margin-bottom: 1rem; text-align: left; }
.form-group label { display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.4rem; }
.form-group input { width: 100%; padding: 0.8rem 1rem; border: 2px solid var(--border); background: var(--bg-main); border-radius: var(--radius-md); font-family: 'DM Sans', sans-serif; font-size: 0.95rem; outline: none; transition: border 0.2s; }
.form-group input:focus { border-color: var(--pn-purple); background: var(--surface); }
.modal-submit { width: 100%; padding: 1rem; background: var(--pn-purple); color: var(--white); border: none; border-radius: 50px; font-size: 1rem; font-weight: 700; cursor: pointer; margin-top: 0.5rem; transition: all 0.2s; box-shadow: 0 8px 24px rgba(162, 107, 250, 0.3); }
.modal-submit:hover { background: var(--pn-purple-dark); transform: translateY(-2px); }
.modal-close { background: var(--bg-main); border: 1px solid var(--border); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; position: absolute; top: 1.5rem; right: 1.5rem; color: var(--text-dark); transition: all 0.2s; }
.modal-close:hover { background: var(--border); }
</style>

<div class="dog-profile reveal">
  <a href="/breed.php" style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg> Back to all dogs
  </a>
  
  <div class="profile-grid">
    <div class="profile-gallery">
        <div class="profile-img">
            <?php 
            $gallery = !empty($dog['gallery_urls']) ? json_decode($dog['gallery_urls'], true) : [];
            if (empty($gallery) && !empty($dog['image_url'])) {
                $gallery = [$dog['image_url']];
            }
            $mainImage = $gallery[0] ?? 'https://images.unsplash.com/photo-1543466835-00a73410a2c0?w=800&q=80';
            ?>
            <img id="mainDogImage" src="<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($dog['name']) ?>">
        </div>
        
        <?php if (count($gallery) > 1): ?>
        <div class="thumbnail-track">
            <?php foreach($gallery as $imgUrl): ?>
                <img src="<?= htmlspecialchars($imgUrl) ?>" 
                     onclick="document.getElementById('mainDogImage').src=this.src"
                     alt="Thumbnail">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div>
      <span class="profile-badge">Available for Adoption</span>
      <h1 class="profile-name"><?= htmlspecialchars($dog['name']) ?></h1>
      <p class="profile-breed"><?= htmlspecialchars($dog['breed_name']) ?></p>
      
      <div class="profile-attrs">
        <?php if (strtolower($dog['age']) !== 'unknown'): ?>
            <div class="attr"><strong>Age</strong><span><?= htmlspecialchars($dog['age']) ?></span></div>
        <?php endif; ?>
        <?php if (strtolower($dog['gender']) !== 'unknown'): ?>
            <div class="attr"><strong>Gender</strong><span><?= htmlspecialchars($dog['gender']) ?></span></div>
        <?php endif; ?>
        <?php if (strtolower($dog['color']) !== 'unknown'): ?>
            <div class="attr"><strong>Color</strong><span><?= htmlspecialchars($dog['color']) ?></span></div>
        <?php endif; ?>
        <div class="attr"><strong>Location</strong><span><?= htmlspecialchars($dog['location']) ?></span></div>
      </div>
      
      <div class="profile-desc">
          <?= strip_tags($dog['description'], '<strong><em><br><hr>') ?>
      </div>
      
      <div class="adopt-panel">
          <button class="btn btn-primary" style="width: 100%; padding: 1.1rem; font-size: 1.05rem;" onclick="document.getElementById('leadModal').classList.add('open')">
              Adopt <?= htmlspecialchars($dog['name']) ?>
          </button>
          <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.75rem; margin-bottom: 0;">Free to apply. Subscription needed to view direct shelter contact details.</p>
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="leadModal">
  <div class="modal">
    <button class="modal-close" onclick="document.getElementById('leadModal').classList.remove('open')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
    <div style="color: var(--pn-purple); margin-bottom: 0.5rem;">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2C8.69 2 6 4.69 6 8c0 3.31 2.69 6 6 6s6-2.69 6-6c0-3.31-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/></svg>
    </div>
    <h2>Almost there!</h2>
    <p>Enter your details and we will show you how to connect with <?= htmlspecialchars($dog['name']) ?>'s shelter.</p>
    
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
          <label>Phone Number</label>
          <input type="tel" name="phone" placeholder="(555) 000-0000" required>
      </div>
      <button type="submit" class="modal-submit">Continue to Contact Details →</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>
<?php
session_start();
$pageTitle = 'Unlock Contact Details - Foredog';
require_once __DIR__ . '/../src/Database.php';
$dogId = (int)($_GET['dog'] ?? $_SESSION['pending_dog_id'] ?? 0);
if (!$dogId) { header('Location: /'); exit; }
$db = Database::getInstance();
$stmt = $db->prepare('SELECT id, name, breed_name, image_url, location FROM dogs WHERE id = ? AND status = "available"');
$stmt->execute([$dogId]);
$dog = $stmt->fetch();
if (!$dog) { header('Location: /'); exit; }
require __DIR__ . '/../templates/header.php';
?>
<style>
.paywall-wrap { max-width:560px; margin:4rem auto; padding:0 1.5rem 5rem; text-align:center; }
.dog-mini { display:flex; align-items:center; gap:1rem; background:var(--white); border-radius:var(--radius); padding:1rem 1.25rem; margin-bottom:2.5rem; box-shadow:0 2px 12px rgba(44,24,16,.06); text-align:left; }
.dog-mini img { width:64px; height:64px; border-radius:50%; object-fit:cover; }
.dog-mini h4 { font-family:'Playfair Display',serif; font-size:1.1rem; }
.paywall-card { background:var(--white); border-radius:20px; padding:2.5rem; box-shadow:0 8px 40px rgba(44,24,16,.1); }
.price { font-family:'Playfair Display',serif; font-size:3rem; color:var(--bark); margin:.75rem 0; }
.price small { font-size:1.1rem; color:var(--muted); font-family:'DM Sans',sans-serif; font-weight:300; }
.feature-list { list-style:none; text-align:left; margin:1.5rem 0; }
.feature-list li { padding:.5rem 0; border-bottom:1px solid var(--sand); font-size:.95rem; display:flex; align-items:center; gap:.75rem; }
.feature-list li:last-child { border-bottom:none; }
.blurred-preview { background:var(--sand); border-radius:var(--radius); padding:1.5rem; margin-bottom:2rem; filter:blur(3px); user-select:none; text-align:left; }
.blurred-preview p { margin:.35rem 0; font-size:.9rem; }
.preview-wrap { position:relative; }
.preview-overlay { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; }
.preview-badge { background:var(--bark); color:var(--white); padding:.5rem 1.25rem; border-radius:50px; font-size:.85rem; font-weight:500; }
.paywall-btn { width:100%; padding:1rem; background:var(--amber); color:var(--white); border:none; border-radius:50px; font-size:1.05rem; font-weight:500; cursor:pointer; font-family:'DM Sans',sans-serif; text-decoration:none; display:block; margin-top:1.5rem; transition:all .2s; }
.paywall-btn:hover { background:var(--amber-light); transform:translateY(-2px); }
</style>
<div class="paywall-wrap">
  <div class="dog-mini">
    <img src="<?= htmlspecialchars($dog['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($dog['name']) ?>">
    <div><h4><?= htmlspecialchars($dog['name']) ?></h4><p style="color:var(--muted);font-size:.85rem;"><?= htmlspecialchars($dog['breed_name']) ?> · <?= htmlspecialchars($dog['location']) ?></p></div>
  </div>
  <div class="paywall-card">
    <span style="font-size:2.5rem;display:block;margin-bottom:.75rem;">🔒</span>
    <h1 style="font-family:'Playfair Display',serif;font-size:1.75rem;margin-bottom:.5rem;">Unlock <?= htmlspecialchars($dog['name']) ?>'s Contact Details</h1>
    <p style="color:var(--muted);font-size:.95rem;">One subscription unlocks all dogs on Foredog.</p>
    <div class="price">$9.99 <small>/ month</small></div>
    <div class="preview-wrap">
      <div class="blurred-preview">
        <p><strong>Shelter / Owner:</strong> ████████ ██████</p>
        <p><strong>Phone:</strong> (███) ███-████</p>
        <p><strong>Email:</strong> ██████@██████.org</p>
      </div>
      <div class="preview-overlay"><span class="preview-badge">🔒 Subscribe to Reveal</span></div>
    </div>
    <ul class="feature-list">
      <li><span style="color:var(--sage);">✓</span> Full contact info for <?= htmlspecialchars($dog['name']) ?></li>
      <li><span style="color:var(--sage);">✓</span> Unlimited dog profiles unlocked</li>
      <li><span style="color:var(--sage);">✓</span> Cancel anytime</li>
      <li><span style="color:var(--sage);">✓</span> Priority matching on new dogs</li>
    </ul>
    <a href="/checkout.php?dog=<?= (int)$dog['id'] ?>" class="paywall-btn">Subscribe & Reveal Contact Details →</a>
    <p style="font-size:.75rem;color:var(--muted);margin-top:1rem;">Secure payment via Stripe. Cancel any time.</p>
  </div>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>

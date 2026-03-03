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
$pageTitle = 'Meet ' . $dog['name'] . ' - Foredog';
require __DIR__ . '/../templates/header.php';
?>
<style>
.dog-profile { max-width:960px; margin:3rem auto; padding:0 2rem 5rem; }
.profile-grid { display:grid; grid-template-columns:1fr 1fr; gap:3rem; align-items:start; }
@media(max-width:680px){ .profile-grid{grid-template-columns:1fr;} }
.profile-img { border-radius:16px; overflow:hidden; aspect-ratio:4/3; }
.profile-img img { width:100%; height:100%; object-fit:cover; display:block; }
.profile-badge { display:inline-block; background:var(--sage); color:var(--white); font-size:.75rem; letter-spacing:.08em; text-transform:uppercase; padding:.3rem .8rem; border-radius:50px; margin-bottom:1rem; }
.profile-name { font-family:'Playfair Display',serif; font-size:clamp(2rem,5vw,2.8rem); margin-bottom:.35rem; }
.profile-attrs { display:flex; flex-wrap:wrap; gap:.65rem; margin-bottom:1.5rem; }
.attr { background:var(--sand); border-radius:8px; padding:.5rem 1rem; font-size:.875rem; }
.attr strong { display:block; font-size:.7rem; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:.15rem; }
.adopt-btn { width:100%; padding:1rem; font-size:1.1rem; font-family:'DM Sans',sans-serif; font-weight:500; background:var(--amber); color:var(--white); border:none; border-radius:50px; cursor:pointer; transition:all .2s; }
.adopt-btn:hover { background:var(--amber-light); transform:translateY(-2px); box-shadow:0 8px 24px rgba(200,115,42,.35); }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:200; align-items:center; justify-content:center; padding:1rem; }
.modal-overlay.open { display:flex; }
.modal { background:var(--white); border-radius:20px; padding:2.5rem; width:100%; max-width:460px; position:relative; }
.modal h2 { font-family:'Playfair Display',serif; font-size:1.6rem; margin-bottom:.5rem; }
.modal p { color:var(--muted); font-size:.9rem; margin-bottom:1.5rem; }
.form-group { margin-bottom:1rem; }
.form-group label { display:block; font-size:.8rem; font-weight:500; text-transform:uppercase; letter-spacing:.06em; margin-bottom:.4rem; }
.form-group input { width:100%; padding:.75rem 1rem; border:2px solid var(--sand); border-radius:8px; font-family:'DM Sans',sans-serif; font-size:1rem; outline:none; transition:border .15s; }
.form-group input:focus { border-color:var(--amber); }
.modal-submit { width:100%; padding:.9rem; background:var(--amber); color:var(--white); border:none; border-radius:50px; font-size:1rem; font-weight:500; cursor:pointer; margin-top:.5rem; transition:all .2s; }
.modal-submit:hover { background:var(--amber-light); }
.modal-close { background:none; border:none; font-size:1.5rem; cursor:pointer; position:absolute; top:1.25rem; right:1.5rem; color:var(--muted); }
</style>
<div class="dog-profile">
  <a href="/breed.php" style="color:var(--muted);font-size:.875rem;text-decoration:none;">← Back to all dogs</a>
  <div class="profile-grid" style="margin-top:2rem;">
    <div class="profile-img"><img src="<?= htmlspecialchars($dog['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($dog['name']) ?>"></div>
    <div>
      <span class="profile-badge">Available for Adoption</span>
      <h1 class="profile-name"><?= htmlspecialchars($dog['name']) ?></h1>
      <p style="color:var(--muted);font-size:1.1rem;margin-bottom:1.5rem;"><?= htmlspecialchars($dog['breed_name']) ?></p>
      <div class="profile-attrs">
        <div class="attr"><strong>Age</strong><?= htmlspecialchars($dog['age']) ?></div>
        <div class="attr"><strong>Gender</strong><?= htmlspecialchars($dog['gender']) ?></div>
        <div class="attr"><strong>Color</strong><?= htmlspecialchars($dog['color']) ?></div>
        <div class="attr"><strong>Location</strong><?= htmlspecialchars($dog['location']) ?></div>
      </div>
      <p style="color:var(--muted);line-height:1.8;margin-bottom:2rem;"><?= nl2br(htmlspecialchars($dog['description'])) ?></p>
      <button class="adopt-btn" onclick="document.getElementById('leadModal').classList.add('open')">Adopt <?= htmlspecialchars($dog['name']) ?> →</button>
      <p style="font-size:.78rem;color:var(--muted);text-align:center;margin-top:.75rem;">Free to apply. Subscription needed to view contact details.</p>
    </div>
  </div>
</div>
<div class="modal-overlay" id="leadModal">
  <div class="modal">
    <button class="modal-close" onclick="document.getElementById('leadModal').classList.remove('open')">×</button>
    <p style="font-size:2rem;margin-bottom:.5rem;">🐾</p>
    <h2>Almost there!</h2>
    <p>Enter your details and we will show you how to connect with <?= htmlspecialchars($dog['name']) ?>'s shelter.</p>
    <form method="POST" action="/capture.php">
      <input type="hidden" name="dog_id" value="<?= (int)$dog['id'] ?>">
      <div class="form-group"><label>Your Name</label><input type="text" name="name" placeholder="Jane Smith" required></div>
      <div class="form-group"><label>Email Address</label><input type="email" name="email" placeholder="jane@example.com" required></div>
      <div class="form-group"><label>Phone Number</label><input type="tel" name="phone" placeholder="+1 (555) 000-0000" required></div>
      <button type="submit" class="modal-submit">Continue to Contact Details →</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>

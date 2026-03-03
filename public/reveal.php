<?php
session_start();
$pageTitle = 'Contact Details Unlocked - Foredog';
require_once __DIR__ . '/../src/Database.php';
if (empty($_SESSION['user_id'])) { header('Location: /'); exit; }
$dogId = (int)($_GET['dog'] ?? 0);
if (!$dogId) { header('Location: /'); exit; }
$db = Database::getInstance();
$user = $db->prepare('SELECT is_subscribed FROM users WHERE id = ?');
$user->execute([$_SESSION['user_id']]);
$userData = $user->fetch();
if (!$userData || !$userData['is_subscribed']) { header('Location: /paywall.php?dog=' . $dogId); exit; }
$stmt = $db->prepare('SELECT * FROM dogs WHERE id = ? AND status = "available"');
$stmt->execute([$dogId]);
$dog = $stmt->fetch();
if (!$dog) { header('Location: /'); exit; }
require __DIR__ . '/../templates/header.php';
?>
<style>
.reveal-wrap { max-width:560px; margin:4rem auto; padding:0 1.5rem 5rem; text-align:center; }
.contact-card { background:var(--white); border-radius:20px; padding:2.5rem; box-shadow:0 8px 40px rgba(44,24,16,.1); text-align:left; margin-top:1.5rem; }
.contact-row { display:flex; align-items:center; gap:1rem; padding:1rem 0; border-bottom:1px solid var(--sand); }
.contact-row:last-child { border-bottom:none; }
.contact-icon { font-size:1.5rem; width:2.5rem; text-align:center; }
.contact-label { font-size:.75rem; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); margin-bottom:.2rem; }
.contact-value { font-size:1.05rem; font-weight:500; }
.contact-value a { color:var(--amber); text-decoration:none; }
.contact-value a:hover { text-decoration:underline; }
</style>
<div class="reveal-wrap">
  <span style="background:#D4EDDA;color:#155724;border-radius:50px;display:inline-block;padding:.5rem 1.25rem;font-size:.875rem;font-weight:500;margin-bottom:1.5rem;">✓ Contact Details Unlocked</span>
  <h1 style="font-family:'Playfair Display',serif;font-size:2.25rem;margin-bottom:.5rem;">You are one step away from <?= htmlspecialchars($dog['name']) ?>!</h1>
  <p style="color:var(--muted);">Reach out directly to the shelter below. Mention Foredog when you call!</p>
  <div style="margin:1.5rem 0;"><img src="<?= htmlspecialchars($dog['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($dog['name']) ?>" style="width:100%;max-width:420px;border-radius:16px;object-fit:cover;aspect-ratio:4/3;"></div>
  <div class="contact-card">
    <h3 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:1rem;">Contact Details for <?= htmlspecialchars($dog['name']) ?></h3>
    <div class="contact-row"><span class="contact-icon">🏠</span><div><div class="contact-label">Shelter / Owner</div><div class="contact-value"><?= htmlspecialchars($dog['owner_contact_name']) ?></div></div></div>
    <div class="contact-row"><span class="contact-icon">📞</span><div><div class="contact-label">Phone</div><div class="contact-value"><a href="tel:<?= htmlspecialchars($dog['owner_contact_phone']) ?>"><?= htmlspecialchars($dog['owner_contact_phone']) ?></a></div></div></div>
    <div class="contact-row"><span class="contact-icon">✉️</span><div><div class="contact-label">Email</div><div class="contact-value"><a href="mailto:<?= htmlspecialchars($dog['owner_contact_email']) ?>"><?= htmlspecialchars($dog['owner_contact_email']) ?></a></div></div></div>
    <div class="contact-row"><span class="contact-icon">📍</span><div><div class="contact-label">Location</div><div class="contact-value"><?= htmlspecialchars($dog['location']) ?></div></div></div>
  </div>
  <div style="margin-top:2rem;"><a href="/breed.php" class="btn btn-outline">Browse More Dogs</a></div>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>

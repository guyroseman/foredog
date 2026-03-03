<?php
session_start();
$pageTitle = 'Available Dogs - Foredog';
require_once __DIR__ . '/../src/Database.php';
require __DIR__ . '/../templates/header.php';
$db = Database::getInstance();
$match = $_GET['match'] ?? '';
if ($match) { $stmt = $db->prepare('SELECT * FROM dogs WHERE breed_slug = ? AND status = "available"'); $stmt->execute([$match]); $breedDisplay = ucwords(str_replace('-', ' ', $match)); }
else { $stmt = $db->query('SELECT * FROM dogs WHERE status = "available" ORDER BY created_at DESC'); $breedDisplay = ''; }
$dogs = $stmt->fetchAll();
?>
<style>
.page-hero { background:var(--bark); padding:4rem 2rem; text-align:center; }
.page-hero h1 { font-family:'Playfair Display',serif; font-size:clamp(2rem,5vw,3rem); color:var(--white); }
.page-hero h1 em { color:var(--amber-light); font-style:italic; }
.page-hero p { color:rgba(255,255,255,.6); margin-top:.75rem; }
.dog-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:1.75rem; margin-top:2.5rem; }
.dog-card { background:var(--white); border-radius:var(--radius); overflow:hidden; transition:transform .25s,box-shadow .25s; text-decoration:none; color:inherit; box-shadow:0 2px 12px rgba(44,24,16,.06); display:block; }
.dog-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(44,24,16,.14); }
.dog-card img { width:100%; height:220px; object-fit:cover; }
.dog-card-body { padding:1.25rem 1.5rem 1.5rem; }
.dog-card-body h3 { font-family:'Playfair Display',serif; font-size:1.3rem; margin-bottom:.35rem; }
.dog-meta { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:.6rem; }
.dog-tag { background:var(--sand); color:var(--muted); font-size:.75rem; padding:.2rem .6rem; border-radius:50px; }
</style>
<section class="page-hero">
  <?php if ($breedDisplay): ?>
    <p style="font-size:.8rem;text-transform:uppercase;letter-spacing:.1em;color:var(--amber-light);margin-bottom:.5rem;">Your Match</p>
    <h1>Meet the <em><?= htmlspecialchars($breedDisplay) ?>s</em></h1>
    <p>We found <?= count($dogs) ?> dog(s) available for you.</p>
  <?php else: ?>
    <h1>All Available <em>Dogs</em></h1>
    <p><?= count($dogs) ?> dogs are looking for a forever home.</p>
  <?php endif; ?>
</section>
<div class="container" style="padding-top:3rem;padding-bottom:5rem;">
  <?php if (empty($dogs)): ?>
    <div style="text-align:center;padding:4rem 2rem;color:var(--muted);">
      <p style="font-size:2rem;margin-bottom:1rem;">🐾</p><p>No dogs found for this breed right now.</p>
      <a href="/breed.php" class="btn btn-outline" style="margin-top:1.5rem;">Browse All Dogs</a>
    </div>
  <?php else: ?>
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
            <span class="dog-tag"><?= htmlspecialchars($dog['color']) ?></span>
          </div>
          <p style="color:var(--muted);font-size:.85rem;">📍 <?= htmlspecialchars($dog['location']) ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>

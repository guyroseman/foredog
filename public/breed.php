<?php
session_start();
$pageTitle = "Available Dogs - Foredog";
require_once __DIR__ . "/../src/Database.php";
require __DIR__ . "/../templates/header.php";
$db = Database::getInstance();
$match = $_GET["match"] ?? "";
if ($match) {
    $stmt = $db->prepare("SELECT * FROM dogs WHERE breed_slug = ? AND status = "available" ORDER BY created_at DESC");
    $stmt->execute([$match]);
    $breedDisplay = ucwords(str_replace("-", " ", $match));
} else {
    $stmt = $db->query("SELECT * FROM dogs WHERE status = "available" ORDER BY created_at DESC");
    $breedDisplay = "";
}
$dogs = $stmt->fetchAll();
?>
<style>
.page-hero{background:var(--bark);padding:4rem 2rem;text-align:center;position:relative;overflow:hidden;}
.page-hero::before{content:"";position:absolute;inset:0;background:url("https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=1400&q=60") center/cover;opacity:.1;}
.page-hero-inner{position:relative;z-index:1;}
.page-hero h1{font-family:"Playfair Display",serif;font-size:clamp(2rem,5vw,3rem);color:var(--white);}
.page-hero h1 em{color:var(--amber-light);font-style:italic;}
.page-hero p{color:rgba(255,255,255,.6);margin-top:.75rem;}
.dog-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.75rem;margin-top:2rem;}
.dog-card{background:var(--white);border-radius:16px;overflow:hidden;transition:transform .25s,box-shadow .25s;text-decoration:none;color:inherit;box-shadow:0 2px 12px rgba(44,24,16,.06);display:block;position:relative;}
.dog-card:hover{transform:translateY(-6px);box-shadow:0 20px 48px rgba(44,24,16,.15);}
.dog-card-img{position:relative;overflow:hidden;height:240px;}
.dog-card-img img{width:100%;height:100%;object-fit:cover;transition:transform .4s;}
.dog-card:hover .dog-card-img img{transform:scale(1.05);}
.dog-card-badge{position:absolute;top:.75rem;left:.75rem;background:var(--sage);color:var(--white);font-size:.7rem;font-weight:500;padding:.25rem .7rem;border-radius:50px;}
.dog-card-gender{position:absolute;top:.75rem;right:.75rem;background:rgba(44,24,16,.7);color:var(--white);font-size:.75rem;padding:.25rem .65rem;border-radius:50px;}
.dog-card-body{padding:1.25rem 1.5rem 1.5rem;}
.dog-card-body h3{font-family:"Playfair Display",serif;font-size:1.35rem;margin-bottom:.25rem;}
.dog-card-breed{color:var(--amber);font-size:.85rem;font-weight:500;margin-bottom:.75rem;}
.dog-meta{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.75rem;}
.dog-tag{background:var(--sand);color:var(--muted);font-size:.72rem;padding:.2rem .6rem;border-radius:50px;}
.dog-location{color:var(--muted);font-size:.82rem;margin-bottom:1rem;}
.dog-card-cta{display:block;background:var(--bark);color:var(--white);text-align:center;padding:.65rem;border-radius:8px;font-size:.875rem;font-weight:500;transition:background .18s;}
.dog-card:hover .dog-card-cta{background:var(--amber);}
.no-results{text-align:center;padding:5rem 2rem;color:var(--muted);}
</style>
<section class="page-hero">
  <div class="page-hero-inner">
    <?php if ($breedDisplay): ?>
    <p style="font-size:.8rem;text-transform:uppercase;letter-spacing:.1em;color:var(--amber-light);margin-bottom:.5rem;">Your Match</p>
    <h1>Meet the <em><?= htmlspecialchars($breedDisplay) ?>s</em></h1>
    <p>We found <?= count($dogs) ?> dog(s) available for you</p>
    <?php else: ?>
    <h1>All Available <em>Dogs</em></h1>
    <p><?= count($dogs) ?> dogs are looking for a forever home</p>
    <?php endif; ?>
  </div>
</section>
<div class="container" style="padding-top:3rem;padding-bottom:5rem;">
  <?php if (!empty($match)): ?>
  <div style="margin-bottom:1.5rem;"><a href="/survey.php" style="color:var(--muted);font-size:.875rem;text-decoration:none;">&#8592; Choose a different breed</a></div>
  <?php endif; ?>
  <?php if (empty($dogs)): ?>
    <div class="no-results">
      <p style="font-size:3rem;margin-bottom:1rem;">&#128062;</p>
      <h2 style="font-family:Playfair Display,serif;font-size:1.5rem;margin-bottom:.5rem;">No dogs found for this breed yet</h2>
      <p>New dogs are added daily. Check back soon or browse all available dogs.</p>
      <a href="/breed.php" class="btn btn-primary" style="margin-top:1.5rem;">Browse All Dogs</a>
    </div>
  <?php else: ?>
    <div class="dog-grid">
      <?php foreach ($dogs as $dog): ?>
      <a href="/dog.php?id=<?= (int)$dog["id"] ?>" class="dog-card">
        <div class="dog-card-img">
          <img src="<?= htmlspecialchars($dog["image_url"] ?? "") ?>" alt="<?= htmlspecialchars($dog["name"]) ?>">
          <span class="dog-card-badge">Available</span>
          <span class="dog-card-gender"><?= htmlspecialchars($dog["gender"]) ?></span>
        </div>
        <div class="dog-card-body">
          <h3><?= htmlspecialchars($dog["name"]) ?></h3>
          <div class="dog-card-breed"><?= htmlspecialchars($dog["breed_name"]) ?></div>
          <div class="dog-meta">
            <span class="dog-tag"><?= htmlspecialchars($dog["age"]) ?></span>
            <span class="dog-tag"><?= htmlspecialchars($dog["color"]) ?></span>
          </div>
          <p class="dog-location">&#128205; <?= htmlspecialchars($dog["location"]) ?></p>
          <span class="dog-card-cta">Meet <?= htmlspecialchars($dog["name"]) ?> &#8250;</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . "/../templates/footer.php"; ?>
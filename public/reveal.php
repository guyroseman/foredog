<?php
session_start();
$pageTitle = 'Contact Details Unlocked - Foredog';
require_once __DIR__ . '/../src/Database.php';
if (empty($_SESSION['user_id'])) { header('Location: /'); exit; }
$dogId = (int)($_GET['dog'] ?? 0);
if (!$dogId) { header('Location: /'); exit; }
$db = Database::getInstance();
$user = $db->prepare('SELECT is_subscribed, name FROM users WHERE id = ?');
$user->execute([$_SESSION['user_id']]);
$userData = $user->fetch();
if (!$userData || !$userData['is_subscribed']) { header('Location: /paywall.php?dog=' . $dogId); exit; }
$stmt = $db->prepare('SELECT * FROM dogs WHERE id = ? AND status = "available"');
$stmt->execute([$dogId]);
$dog = $stmt->fetch();
if (!$dog) { header('Location: /'); exit; }
$hasCoords = !empty($dog['latitude']) && !empty($dog['longitude']);
require __DIR__ . '/../templates/header.php';
?>
<style>
.reveal-page { max-width: 680px; margin: 3rem auto; padding: 0 1.5rem 6rem; }
.unlock-badge { display: inline-flex; align-items: center; gap: 0.5rem; background: #d4edda; color: #155724; border-radius: 50px; padding: 0.5rem 1.25rem; font-size: 0.85rem; font-weight: 600; margin-bottom: 2rem; }
.dog-hero { width: 100%; max-width: 680px; border-radius: 20px; aspect-ratio: 16/9; object-fit: cover; margin-bottom: 2rem; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
.reveal-heading { font-family: 'Playfair Display', serif; font-size: clamp(1.8rem, 4vw, 2.4rem); color: var(--text-dark); margin-bottom: 0.5rem; }
.reveal-sub { color: var(--text-muted); margin-bottom: 2.5rem; font-size: 1rem; }

/* Contact Card */
.contact-card {
    background: var(--white);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 40px rgba(44,24,16,.1);
    margin-bottom: 1.5rem;
    border: 1px solid var(--border);
}
.contact-card-header {
    background: linear-gradient(135deg, #1a0f0a 0%, #2d1810 100%);
    padding: 1.5rem 2rem;
    display: flex; align-items: center; gap: 1rem;
}
.contact-card-header img {
    width: 56px; height: 56px; border-radius: 50%; object-fit: cover;
    border: 2px solid rgba(201,169,110,0.4);
}
.contact-card-header h3 { color: #f5f0eb; font-family: 'Playfair Display', serif; font-size: 1.2rem; margin-bottom: 0.2rem; }
.contact-card-header p { color: #c9a96e; font-size: 0.8rem; }
.contact-rows { padding: 0.5rem 0; }
.contact-row {
    display: flex; align-items: center; gap: 1.25rem;
    padding: 1.1rem 2rem;
    border-bottom: 1px solid var(--border);
    transition: background 0.15s;
}
.contact-row:last-child { border-bottom: none; }
.contact-row:hover { background: var(--surface); }
.contact-icon-wrap {
    width: 40px; height: 40px; border-radius: 10px;
    background: var(--pn-purple-light);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.contact-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); margin-bottom: 0.2rem; font-weight: 600; }
.contact-value { color: var(--text-dark); font-weight: 600; font-size: 1rem; }
.contact-value a { color: var(--pn-purple); text-decoration: none; }
.contact-value a:hover { text-decoration: underline; }
.contact-action { margin-left: auto; }
.contact-action a {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: var(--pn-purple); color: white; border-radius: 50px;
    padding: 0.45rem 1rem; font-size: 0.8rem; font-weight: 600;
    text-decoration: none; transition: all 0.2s;
}
.contact-action a:hover { background: var(--pn-purple-dark); transform: translateY(-1px); }

/* Map Card */
.map-card {
    background: var(--white);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 40px rgba(44,24,16,.1);
    margin-bottom: 1.5rem;
    border: 1px solid var(--border);
}
.map-card-header { padding: 1.25rem 2rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 0.75rem; }
.map-card-header h4 { font-size: 0.9rem; font-weight: 700; color: var(--text-dark); }
.map-card-header span { font-size: 0.8rem; color: var(--text-muted); }
.map-frame { width: 100%; height: 280px; border: none; display: block; }
.map-footer { padding: 1rem 2rem; background: var(--surface); display: flex; align-items: center; justify-content: space-between; }
.map-footer p { font-size: 0.85rem; color: var(--text-muted); }
.map-footer a { font-size: 0.85rem; color: var(--pn-purple); text-decoration: none; font-weight: 600; }

/* Tips */
.tip-card { background: #fffbf0; border: 1px solid #f0e0b0; border-radius: 16px; padding: 1.5rem 2rem; margin-bottom: 1.5rem; }
.tip-card h4 { color: #8b6914; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem; }
.tip-card ul { list-style: none; display: flex; flex-direction: column; gap: 0.6rem; }
.tip-card li { font-size: 0.9rem; color: #5c4a1e; display: flex; gap: 0.6rem; align-items: flex-start; }
</style>

<div class="reveal-page reveal">
    <div class="unlock-badge">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20,6 9,17 4,12"/></svg>
        Contact Details Unlocked
    </div>

    <h1 class="reveal-heading">You're one step away from <?= htmlspecialchars($dog['name']) ?>! 🐾</h1>
    <p class="reveal-sub">Everything you need to connect with the shelter is below. Mention Foredog when you call!</p>

    <?php if(!empty($dog['image_url'])): ?>
    <img src="<?= htmlspecialchars($dog['image_url']) ?>" alt="<?= htmlspecialchars($dog['name']) ?>" class="dog-hero" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1543466835-00a73410a2c0?w=800&q=80';">
    <?php endif; ?>

    <!-- Contact Card -->
    <div class="contact-card">
        <div class="contact-card-header">
            <img src="<?= htmlspecialchars($dog['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($dog['name']) ?>" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1543466835-00a73410a2c0?w=120&q=80';">
            <div>
                <h3><?= htmlspecialchars($dog['name']) ?>'s Shelter Contact</h3>
                <p><?= htmlspecialchars($dog['breed_name']) ?> · <?= htmlspecialchars($dog['location']) ?></p>
            </div>
        </div>
        <div class="contact-rows">
            <div class="contact-row">
                <div class="contact-icon-wrap">🏠</div>
                <div>
                    <div class="contact-label">Shelter Name</div>
                    <div class="contact-value"><?= htmlspecialchars($dog['owner_contact_name']) ?></div>
                </div>
            </div>
            <div class="contact-row">
                <div class="contact-icon-wrap">📞</div>
                <div>
                    <div class="contact-label">Phone Number</div>
                    <div class="contact-value"><a href="tel:<?= htmlspecialchars($dog['owner_contact_phone']) ?>"><?= htmlspecialchars($dog['owner_contact_phone']) ?></a></div>
                </div>
                <div class="contact-action"><a href="tel:<?= htmlspecialchars($dog['owner_contact_phone']) ?>">📞 Call Now</a></div>
            </div>
            <div class="contact-row">
                <div class="contact-icon-wrap">✉️</div>
                <div>
                    <div class="contact-label">Email Address</div>
                    <div class="contact-value"><a href="mailto:<?= htmlspecialchars($dog['owner_contact_email']) ?>"><?= htmlspecialchars($dog['owner_contact_email']) ?></a></div>
                </div>
                <div class="contact-action"><a href="mailto:<?= htmlspecialchars($dog['owner_contact_email']) ?>?subject=Interested in adopting <?= urlencode($dog['name']) ?>&body=Hi, I found <?= urlencode($dog['name']) ?> on Foredog and I'm interested in adoption.">✉️ Email</a></div>
            </div>
            <div class="contact-row">
                <div class="contact-icon-wrap">📍</div>
                <div>
                    <div class="contact-label">Location</div>
                    <div class="contact-value"><?= htmlspecialchars($dog['location']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Card (subscribers only) -->
    <?php if($hasCoords): ?>
    <div class="map-card">
        <div class="map-card-header">
            <span style="font-size:1.2rem;">🗺️</span>
            <div>
                <h4>Shelter Location</h4>
                <span><?= htmlspecialchars($dog['location']) ?></span>
            </div>
        </div>
        <iframe
            class="map-frame"
            src="https://maps.google.com/maps?q=<?= $dog['latitude'] ?>,<?= $dog['longitude'] ?>&z=14&output=embed"
            allowfullscreen
            loading="lazy">
        </iframe>
        <div class="map-footer">
            <p>Approximate shelter location</p>
            <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $dog['latitude'] ?>,<?= $dog['longitude'] ?>" target="_blank">Get Directions →</a>
        </div>
    </div>
    <?php else: ?>
    <div class="map-card">
        <div class="map-card-header">
            <span style="font-size:1.2rem;">🗺️</span>
            <div><h4>Shelter Location</h4><span><?= htmlspecialchars($dog['location']) ?></span></div>
        </div>
        <iframe
            class="map-frame"
            src="https://maps.google.com/maps?q=<?= urlencode($dog['location']) ?>&z=13&output=embed"
            allowfullscreen loading="lazy">
        </iframe>
        <div class="map-footer">
            <p>Search area for <?= htmlspecialchars($dog['location']) ?></p>
            <a href="https://www.google.com/maps/search/<?= urlencode($dog['owner_contact_name'].' '.$dog['location']) ?>" target="_blank">Open in Maps →</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Adoption Tips -->
    <div class="tip-card">
        <h4>💡 Tips for a Successful Adoption</h4>
        <ul>
            <li><span>1.</span> Call the shelter first to confirm <?= htmlspecialchars($dog['name']) ?> is still available</li>
            <li><span>2.</span> Mention you found them on Foredog — shelters love to know!</li>
            <li><span>3.</span> Ask about any adoption fees, required documents, and meet-and-greet policies</li>
            <li><span>4.</span> If there's a waiting list, put your name down immediately</li>
        </ul>
    </div>

    <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-top:2rem;">
        <a href="/breed.php" class="btn btn-outline">← Browse More Dogs</a>
        <a href="/breed.php?state=<?= htmlspecialchars($dog['state'] ?? '') ?>" class="btn btn-outline">More Dogs in <?= htmlspecialchars($dog['state'] ?? $dog['location']) ?></a>
    </div>
</div>
<?php require __DIR__ . '/../templates/footer.php'; ?>
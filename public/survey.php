<?php
session_start();
$pageTitle = 'Find Your Perfect Dog - Foredog';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Survey.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['weekend'])) {
    $answers = [
        'activity_level' => $_POST['weekend']    ?? '',
        'living_space'   => $_POST['living']     ?? '',
        'experience'     => $_POST['experience'] ?? '',
        'trait'          => $_POST['trait']      ?? '',
    ];

    $topBreeds = Survey::recommend($answers);
    $primaryBreed = $topBreeds[0];
    
    $_SESSION['recommended_breed'] = $primaryBreed;
    $_SESSION['runner_ups'] = array_slice($topBreeds, 1, 2);
    $_SESSION['quiz_answers'] = $answers;
    
    $db = Database::getInstance();
    // FIX: Allows user to retake quiz without database constraint crashing
    $db->prepare('INSERT INTO survey_sessions (session_id, recommended_breed_slug) VALUES (?, ?) ON DUPLICATE KEY UPDATE recommended_breed_slug = VALUES(recommended_breed_slug)')->execute([session_id(), $primaryBreed]);
    
    header('Content-Type: application/json');
    // FIX: Send them specifically to the bridge view to show matches
    echo json_encode(['redirect' => '/breed.php?match=' . urlencode($primaryBreed) . '&view=bridge']);
    exit;
}
require __DIR__ . '/../templates/header.php';
?>
<style>
/* =========================================================
   FUNNEL ISOLATION: 
   Hides the logo, header nav, and footer to prevent drop-off! 
   ========================================================= */
header, footer, .site-header, .site-footer, nav { 
    display: none !important; 
}

body { background: var(--bg-main); margin: 0; padding: 0; }

/* Changed to 100vh since there is no header taking up space anymore */
.quiz-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1.5rem; position: relative; overflow: hidden; }
.quiz-wrap::before { content: ''; position: absolute; inset: 0; background: url('https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=1600&q=60') center/cover; opacity: 0.04; z-index: -1; }

.quiz-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); width: 100%; max-width: 640px; padding: 3rem 2.5rem; box-shadow: var(--shadow-soft); position: relative; z-index: 1; }
.progress-wrap { margin-bottom: 2.5rem; }
.progress-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
.progress-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); }
.progress-pct { font-size: 0.85rem; font-weight: 700; color: var(--pn-purple); }
.progress-bar { height: 6px; background: var(--border); border-radius: 4px; overflow: hidden; }
.progress-fill { height: 100%; background: var(--pn-purple); border-radius: 4px; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); }

.quiz-step { display: none; animation: fadeUp 0.4s ease both; }
.quiz-step.active { display: block; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

.step-eyebrow { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: var(--pn-purple); margin-bottom: 0.5rem; display: block; }
.step-question { font-family: 'Playfair Display', serif; font-size: clamp(1.5rem, 4vw, 2rem); line-height: 1.2; margin-bottom: 1rem; color: var(--text-dark); }
.step-hint { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 2rem; padding: 0.75rem 1rem; background: var(--bg-main); border-radius: var(--radius-md); border-left: 3px solid var(--pn-purple-light); }
.step-hint strong { color: var(--pn-purple-dark); }

.tiles { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.tiles.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
@media(max-width: 500px) { .tiles, .tiles.cols-3 { grid-template-columns: 1fr; } }

.tile { border: 2px solid var(--border); border-radius: var(--radius-md); padding: 1.5rem 1rem; cursor: pointer; transition: all 0.2s ease; background: var(--surface); text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; }
.tile:hover { border-color: var(--pn-purple-light); background: var(--bg-main); transform: translateY(-3px); box-shadow: var(--shadow-soft); }
.tile.selected { border-color: var(--pn-purple); background: var(--pn-purple); box-shadow: var(--shadow-hover); }

.tile-icon { color: var(--pn-purple); transition: color 0.2s; margin-bottom: 0.25rem; }
.tile.selected .tile-icon { color: var(--white); }
.tile-label { font-weight: 700; font-size: 1rem; color: var(--text-dark); font-family: 'DM Sans', sans-serif; }
.tile.selected .tile-label { color: var(--white); }
.tile-sub { font-size: 0.8rem; color: var(--text-muted); line-height: 1.3; font-family: 'DM Sans', sans-serif; }
.tile.selected .tile-sub { color: rgba(255,255,255,0.85); }

.quiz-nav { display: flex; justify-content: flex-start; margin-top: 2rem; }
.btn-back { background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 0.95rem; font-weight: 500; font-family: 'DM Sans', sans-serif; transition: color 0.2s; display: flex; align-items: center; gap: 0.5rem; }
.btn-back:hover { color: var(--pn-purple); }

/* ---- PROCESSING SCREEN ---- */
.proc { display: none; position: fixed; inset: 0; background: var(--surface); z-index: 9999; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 2rem; }
.proc.show { display: flex; }
.proc-inner { position: relative; z-index: 1; max-width: 480px; width: 100%; }
.proc-icon { color: var(--pn-purple); margin-bottom: 1.5rem; animation: pulseIcon 1.5s infinite; }
@keyframes pulseIcon { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
.proc-title { font-family: 'Playfair Display', serif; font-size: clamp(1.8rem, 4vw, 2.5rem); color: var(--text-dark); margin-bottom: 0.75rem; }
.proc-status { font-size: 1.05rem; color: var(--text-muted); min-height: 1.5em; margin-bottom: 2rem; font-weight: 500; }
.proc-bar { width: 100%; max-width: 320px; height: 6px; background: var(--border); border-radius: 4px; margin: 0 auto; overflow: hidden; }
.proc-bar-fill { height: 100%; background: var(--pn-purple); border-radius: 4px; width: 0; transition: width 0.8s ease; }
.proc-match { margin-top: 2rem; padding: 1.2rem 1.5rem; background: var(--pn-purple-light); border-radius: var(--radius-md); color: var(--pn-purple-dark); font-size: 1rem; font-weight: 700; opacity: 0; transform: translateY(10px); transition: all 0.4s; }
.proc-match.show { opacity: 1; transform: translateY(0); }
</style>

<div class="quiz-wrap">
<form method="POST" id="quizForm">
<div class="quiz-card">

  <div class="progress-wrap">
    <div class="progress-top">
      <span class="progress-label" id="progressLabel">Step 1 of 4</span>
      <span class="progress-pct" id="progressPct">25%</span>
    </div>
    <div class="progress-bar"><div class="progress-fill" id="progressFill" style="width:25%"></div></div>
  </div>

  <div class="quiz-step active" data-step="1">
    <span class="step-eyebrow">Your Lifestyle</span>
    <h2 class="step-question">What does your perfect weekend look like?</h2>
    <div class="step-hint"><strong>Why we ask:</strong> Matching a dog's energy to your lifestyle is the #1 predictor of a successful adoption.</div>
    <div class="tiles">
      <button type="button" class="tile" data-name="weekend" data-value="low">
        <div class="tile-icon"><svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 14v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2"></path><path d="M2 10a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4H2z"></path><path d="M6 18v2"></path><path d="M18 18v2"></path></svg></div>
        <span class="tile-label">Couch &amp; Netflix</span>
        <span class="tile-sub">Relaxed, slow-paced days</span>
      </button>
      <button type="button" class="tile" data-name="weekend" data-value="active">
        <div class="tile-icon"><svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m8 3 4 8 5-5 5 15H2L8 3z"></path></svg></div>
        <span class="tile-label">Hiking &amp; Running</span>
        <span class="tile-sub">Outdoors, high energy</span>
      </button>
      <button type="button" class="tile" data-name="weekend" data-value="moderate">
        <div class="tile-icon"><svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div>
        <span class="tile-label">Friends &amp; Family</span>
        <span class="tile-sub">Social, lively gatherings</span>
      </button>
      <button type="button" class="tile" data-name="weekend" data-value="moderate">
        <div class="tile-icon"><svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path><path d="M8 6h.01"></path><path d="M16 6h.01"></path><path d="M12 6h.01"></path><path d="M12 10h.01"></path><path d="M12 14h.01"></path></svg></div>
        <span class="tile-label">Exploring the City</span>
        <span class="tile-sub">Urban walks, cafes</span>
      </button>
    </div>
    <input type="hidden" name="weekend" id="input_weekend">
  </div>

  <div class="quiz-step" data-step="2">
    <span class="step-eyebrow">Your Home</span>
    <h2 class="step-question">Where will your new best friend be living?</h2>
    <div class="step-hint"><strong>Did you know?</strong> Many large breeds actually thrive in apartments if given a daily walk!</div>
    <div class="tiles cols-3">
      <button type="button" class="tile" data-name="living" data-value="apartment">
        <div class="tile-icon"><svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path><path d="M8 6h.01"></path><path d="M16 6h.01"></path><path d="M12 6h.01"></path><path d="M12 10h.01"></path><path d="M12 14h.01"></path></svg></div>
        <span class="tile-label">Apartment</span>
      </button>
      <button type="button" class="tile" data-name="living" data-value="house">
        <div class="tile-icon"><svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg></div>
        <span class="tile-label">House + Yard</span>
      </button>
      <button type="button" class="tile" data-name="living" data-value="house">
        <div class="tile-icon"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg></div>
        <span class="tile-label">Lots of Land</span>
      </button>
    </div>
    <input type="hidden" name="living" id="input_living">
  </div>

  <div class="quiz-step" data-step="3">
    <span class="step-eyebrow">Your Perfect Companion</span>
    <h2 class="step-question">What trait matters most to you in a dog?</h2>
    <div class="step-hint"><strong>Why we ask:</strong> Every dog has a dominant personality. We match you based on actual behavior, not just looks.</div>
    <div class="tiles cols-3">
      <button type="button" class="tile" data-name="trait" data-value="experienced">
        <div class="tile-icon"><svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
        <span class="tile-label">Loyal Guard</span>
      </button>
      <button type="button" class="tile" data-name="trait" data-value="some">
        <div class="tile-icon"><svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg></div>
        <span class="tile-label">Cuddle Bug</span>
      </button>
      <button type="button" class="tile" data-name="trait" data-value="first">
        <div class="tile-icon"><svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg></div>
        <span class="tile-label">Highly Trainable</span>
      </button>
    </div>
    <input type="hidden" name="trait" id="input_trait">
  </div>

  <div class="quiz-step" data-step="4">
    <span class="step-eyebrow">Your Experience</span>
    <h2 class="step-question">Have you owned a dog before?</h2>
    <div class="step-hint"><strong>Why we ask:</strong> We want to match you with a dog you can confidently care for from day one.</div>
    <div class="tiles">
      <button type="button" class="tile" data-name="experience" data-value="first">
        <div class="tile-icon"><svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path></svg></div>
        <span class="tile-label">First-time owner</span>
      </button>
      <button type="button" class="tile" data-name="experience" data-value="some">
        <div class="tile-icon"><svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg></div>
        <span class="tile-label">Had a dog before</span>
      </button>
      <button type="button" class="tile" data-name="experience" data-value="experienced">
        <div class="tile-icon"><svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path></svg></div>
        <span class="tile-label">Very experienced</span>
      </button>
      <button type="button" class="tile" data-name="experience" data-value="some">
        <div class="tile-icon"><svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div>
        <span class="tile-label">Family decision</span>
      </button>
    </div>
    <input type="hidden" name="experience" id="input_experience">
  </div>

  <div class="quiz-nav">
    <button type="button" class="btn-back" id="btnBack" style="visibility:hidden">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        Back
    </button>
  </div>

</div>
</form>
</div>

<div class="proc" id="procScreen">
  <div class="proc-inner">
    <div class="proc-icon">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8.69 2 6 4.69 6 8c0 3.31 2.69 6 6 6s6-2.69 6-6c0-3.31-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm-8.83 5.46C2.33 16.32 2 15.18 2 14c0-2.76 2.24-5 5-5h1c.55 0 1 .45 1 1s-.45 1-1 1H7c-1.65 0-3 1.35-3 3 0 .85.36 1.62.94 2.18L3.17 17.46zM22 14c0 1.18-.33 2.32-.94 3.18l-1.77-1.28c.58-.56.94-1.33.94-2.18 0-1.65-1.35-3-3-3h-1c-.55 0-1-.45-1-1s.45-1 1-1h1c2.76 0 5 2.24 5 5zM12 16c-3.31 0-6 2.69-6 6h12c0-3.31-2.69-6-6-6zm0 4c-1.1 0-2-.9-2-2h4c0 1.1-.9 2-2 2z"/></svg>
    </div>
    <h2 class="proc-title">Finding your perfect match...</h2>
    <p class="proc-status" id="procStatus">Analyzing your lifestyle profile...</p>
    <div class="proc-bar"><div class="proc-bar-fill" id="procBarFill"></div></div>
    <div class="proc-match" id="procMatch">
      ✓ Match found! Preparing your personalized results...
    </div>
  </div>
</div>

<script>
var steps = document.querySelectorAll('.quiz-step');
var btnBack = document.getElementById('btnBack');
var current = 1;
var total = steps.length;
var answers = {};

function showStep(n) {
  steps.forEach(function(s){ s.classList.remove('active'); });
  steps[n-1].classList.add('active');
  var pct = Math.round(n / total * 100);
  document.getElementById('progressFill').style.width = pct + '%';
  document.getElementById('progressLabel').textContent = 'Step ' + n + ' of ' + total;
  document.getElementById('progressPct').textContent = pct + '%';
  btnBack.style.visibility = n > 1 ? 'visible' : 'hidden';
}

document.querySelectorAll('.tile').forEach(function(tile) {
  tile.addEventListener('click', function(e) {
    e.preventDefault();
    var name = this.dataset.name;
    answers[name] = this.dataset.value;
    document.getElementById('input_' + name).value = this.dataset.value;
    document.querySelectorAll('[data-name="' + name + '"]').forEach(function(t){ t.classList.remove('selected'); });
    this.classList.add('selected');
    
    // UI FIX: Smooth Auto-Advance so they don't have to click a continue button
    setTimeout(function() {
        if (current < total) { 
            current++; showStep(current); 
        } else { 
            runProcessingScreen(); 
        }
    }, 350); 
  });
});

btnBack.addEventListener('click', function() {
  if (current > 1) { current--; showStep(current); }
});

function runProcessingScreen() {
  document.getElementById('procScreen').classList.add('show');
  var statusEl = document.getElementById('procStatus');
  var barFill  = document.getElementById('procBarFill');
  var matchBox = document.getElementById('procMatch');

  var messages = [
    { text: 'Analyzing your lifestyle profile...',      pct: '20%',  delay: 0    },
    { text: 'Scanning available shelter dogs...',       pct: '45%',  delay: 1000 },
    { text: 'Calculating top breed matches...',         pct: '72%',  delay: 2100 },
    { text: 'Scoring compatibility factors...',         pct: '90%',  delay: 3000 },
    { text: 'Match found! Formatting results...',       pct: '100%', delay: 3800 },
  ];

  messages.forEach(function(m) {
    setTimeout(function() {
      statusEl.textContent = m.text;
      barFill.style.width  = m.pct;
    }, m.delay);
  });

  setTimeout(function() { matchBox.classList.add('show'); }, 3800);

  // Submit in background and auto-redirect to the Bridge view
  var formData = new FormData(document.getElementById('quizForm'));
  fetch('/survey.php', { method: 'POST', body: formData })
    .then(function(r){ return r.json(); })
    .then(function(data) {
      setTimeout(function() {
        window.location.href = data.redirect;
      }, 4700);
    })
    .catch(function() {
      setTimeout(function() {
        window.location.href = '/breed.php';
      }, 4700);
    });
}
</script>
<?php require __DIR__ . '/../templates/footer.php'; ?>
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
        'has_kids'       => 'no',
    ];
    $breedSlug = Survey::recommend($answers);
    $_SESSION['recommended_breed'] = $breedSlug;
    $_SESSION['quiz_answers']      = $answers;
    $_SESSION['quiz_trait']        = $_POST['trait'] ?? '';
    $db = Database::getInstance();
    $db->prepare('INSERT IGNORE INTO survey_sessions (session_id, recommended_breed_slug) VALUES (?, ?)')->execute([session_id(), $breedSlug]);
    header('Content-Type: application/json');
    echo json_encode(['redirect' => '/breed.php?match=' . urlencode($breedSlug)]);
    exit;
}
require __DIR__ . '/../templates/header.php';
?>
<style>
body{background:var(--bark);}
.quiz-wrap{min-height:calc(100vh - 64px);display:flex;align-items:center;justify-content:center;padding:2rem 1.5rem;background:var(--bark);position:relative;overflow:hidden;}
.quiz-wrap::before{content:'';position:absolute;inset:0;background:url('https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=1600&q=60') center/cover;opacity:.07;}
.quiz-card{background:var(--white);border-radius:24px;width:100%;max-width:620px;padding:2.5rem;position:relative;z-index:1;box-shadow:0 24px 80px rgba(0,0,0,.4);}
.progress-wrap{margin-bottom:2rem;}
.progress-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:.6rem;}
.progress-label{font-size:.72rem;font-weight:500;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);}
.progress-pct{font-size:.72rem;font-weight:600;color:var(--amber);}
.progress-bar{height:5px;background:var(--sand);border-radius:3px;overflow:hidden;}
.progress-fill{height:100%;background:linear-gradient(90deg,var(--amber),var(--amber-light));border-radius:3px;transition:width .5s cubic-bezier(.4,0,.2,1);}
.quiz-step{display:none;animation:fadeUp .35s ease both;}
.quiz-step.active{display:block;}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.step-eyebrow{font-size:.72rem;font-weight:500;text-transform:uppercase;letter-spacing:.12em;color:var(--amber);margin-bottom:.4rem;}
.step-question{font-family:'Playfair Display',serif;font-size:clamp(1.3rem,3vw,1.7rem);line-height:1.3;margin-bottom:.6rem;color:var(--bark);}
.step-hint{font-size:.8rem;color:var(--muted);margin-bottom:1.5rem;padding:.55rem .85rem;background:var(--sand);border-radius:8px;border-left:3px solid var(--amber);line-height:1.5;}
.step-hint strong{color:var(--bark);}
.tiles{display:grid;grid-template-columns:1fr 1fr;gap:.8rem;}
.tiles.cols-3{grid-template-columns:1fr 1fr 1fr;}
@media(max-width:500px){.tiles,.tiles.cols-3{grid-template-columns:1fr 1fr;}}
.tile{border:2px solid var(--sand);border-radius:14px;padding:1.1rem .75rem .9rem;cursor:pointer;transition:all .18s;background:var(--cream);text-align:center;display:flex;flex-direction:column;align-items:center;gap:.35rem;font-family:'DM Sans',sans-serif;}
.tile:hover{border-color:var(--amber);background:#FFF8F2;transform:translateY(-3px);box-shadow:0 8px 24px rgba(200,115,42,.18);}
.tile.selected{border-color:var(--amber);background:var(--amber);}
.tile-emoji{font-size:2rem;line-height:1.1;}
.tile-label{font-weight:500;font-size:.9rem;color:var(--bark);}
.tile.selected .tile-label{color:var(--white);}
.tile-sub{font-size:.72rem;color:var(--muted);line-height:1.3;}
.tile.selected .tile-sub{color:rgba(255,255,255,.75);}
.quiz-nav{display:flex;justify-content:space-between;align-items:center;margin-top:1.75rem;}
.btn-back{background:none;border:none;color:var(--muted);cursor:pointer;font-size:.875rem;font-family:'DM Sans',sans-serif;padding:.4rem 0;transition:color .15s;}
.btn-back:hover{color:var(--bark);}
.btn-next{background:var(--amber);color:var(--white);border:none;border-radius:50px;padding:.75rem 2rem;font-size:1rem;font-weight:500;font-family:'DM Sans',sans-serif;cursor:pointer;transition:all .2s;}
.btn-next:hover:not(:disabled){background:var(--amber-light);transform:translateY(-1px);box-shadow:0 6px 20px rgba(200,115,42,.35);}
.btn-next:disabled{opacity:.35;cursor:not-allowed;}

/* ---- PROCESSING SCREEN ---- */
.proc{display:none;position:fixed;inset:0;background:var(--bark);z-index:9999;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:2rem;}
.proc.show{display:flex;}
.proc::before{content:'';position:absolute;inset:0;background:url('https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=1600&q=60') center/cover;opacity:.07;}
.proc-inner{position:relative;z-index:1;max-width:480px;width:100%;}
.proc-dog{font-size:4.5rem;margin-bottom:1.25rem;display:inline-block;animation:dogBounce .9s ease-in-out infinite;}
@keyframes dogBounce{0%,100%{transform:translateY(0) rotate(-3deg)}50%{transform:translateY(-14px) rotate(3deg)}}
.proc-title{font-family:'Playfair Display',serif;font-size:clamp(1.6rem,4vw,2.2rem);color:var(--white);margin-bottom:.6rem;}
.proc-status{font-size:.95rem;color:rgba(255,255,255,.65);min-height:1.5em;margin-bottom:1.5rem;}
.proc-bar{width:100%;max-width:320px;height:5px;background:rgba(255,255,255,.15);border-radius:3px;margin:0 auto;overflow:hidden;}
.proc-bar-fill{height:100%;background:var(--amber);border-radius:3px;width:0;transition:width .9s ease;}
.proc-match{margin-top:1.75rem;padding:.9rem 1.5rem;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:12px;color:var(--white);font-size:.9rem;opacity:0;transform:translateY(8px);transition:opacity .4s,transform .4s;}
.proc-match.show{opacity:1;transform:translateY(0);}
.proc-match strong{color:var(--amber-light);}
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

  <!-- STEP 1 -->
  <div class="quiz-step active" data-step="1">
    <p class="step-eyebrow">Your Lifestyle</p>
    <h2 class="step-question">What does your perfect weekend look like?</h2>
    <div class="step-hint"><strong>Why we ask:</strong> Matching a dog's energy to your lifestyle is the #1 predictor of a successful, lifelong adoption.</div>
    <div class="tiles">
      <button type="button" class="tile" data-name="weekend" data-value="low">
        <span class="tile-emoji">&#128715;</span>
        <span class="tile-label">Couch &amp; Netflix</span>
        <span class="tile-sub">Relaxed, slow-paced days</span>
      </button>
      <button type="button" class="tile" data-name="weekend" data-value="active">
        <span class="tile-emoji">&#127939;</span>
        <span class="tile-label">Hiking &amp; Running</span>
        <span class="tile-sub">Outdoors, high energy</span>
      </button>
      <button type="button" class="tile" data-name="weekend" data-value="moderate">
        <span class="tile-emoji">&#128106;</span>
        <span class="tile-label">Friends &amp; Family</span>
        <span class="tile-sub">Social, lively gatherings</span>
      </button>
      <button type="button" class="tile" data-name="weekend" data-value="moderate">
        <span class="tile-emoji">&#127961;</span>
        <span class="tile-label">Exploring the City</span>
        <span class="tile-sub">Urban walks, cafes, parks</span>
      </button>
    </div>
    <input type="hidden" name="weekend" id="input_weekend">
  </div>

  <!-- STEP 2 -->
  <div class="quiz-step" data-step="2">
    <p class="step-eyebrow">Your Home</p>
    <h2 class="step-question">Where will your new best friend be living?</h2>
    <div class="step-hint"><strong>Did you know?</strong> Some large breeds like Greyhounds thrive in apartments, while some small breeds need constant yard space to burn energy!</div>
    <div class="tiles cols-3">
      <button type="button" class="tile" data-name="living" data-value="apartment">
        <span class="tile-emoji">&#127962;</span>
        <span class="tile-label">Apartment</span>
        <span class="tile-sub">No private outdoor space</span>
      </button>
      <button type="button" class="tile" data-name="living" data-value="house">
        <span class="tile-emoji">&#127968;</span>
        <span class="tile-label">House + Yard</span>
        <span class="tile-sub">Small or medium garden</span>
      </button>
      <button type="button" class="tile" data-name="living" data-value="house">
        <span class="tile-emoji">&#127963;</span>
        <span class="tile-label">Lots of Land</span>
        <span class="tile-sub">Big open outdoor space</span>
      </button>
    </div>
    <input type="hidden" name="living" id="input_living">
  </div>

  <!-- STEP 3 -->
  <div class="quiz-step" data-step="3">
    <p class="step-eyebrow">Your Perfect Companion</p>
    <h2 class="step-question">What trait matters most to you in a dog?</h2>
    <div class="step-hint"><strong>Why we ask:</strong> Every dog has a dominant personality. Knowing yours helps us find the dog you will genuinely bond with for life.</div>
    <div class="tiles cols-3">
      <button type="button" class="tile" data-name="trait" data-value="experienced">
        <span class="tile-emoji">&#128737;</span>
        <span class="tile-label">Loyal &amp; Protective</span>
        <span class="tile-sub">My guardian and shadow</span>
      </button>
      <button type="button" class="tile" data-name="trait" data-value="some">
        <span class="tile-emoji">&#129294;</span>
        <span class="tile-label">Total Cuddle Bug</span>
        <span class="tile-sub">Affectionate and warm</span>
      </button>
      <button type="button" class="tile" data-name="trait" data-value="first">
        <span class="tile-emoji">&#127941;</span>
        <span class="tile-label">Smart &amp; Trainable</span>
        <span class="tile-sub">Learns fast, loves tasks</span>
      </button>
    </div>
    <input type="hidden" name="trait" id="input_trait">
  </div>

  <!-- STEP 4 -->
  <div class="quiz-step" data-step="4">
    <p class="step-eyebrow">Your Experience</p>
    <h2 class="step-question">Have you owned a dog before?</h2>
    <div class="step-hint"><strong>Why we ask:</strong> Some breeds need an experienced hand. We want to match you with a dog you can confidently care for from day one.</div>
    <div class="tiles">
      <button type="button" class="tile" data-name="experience" data-value="first">
        <span class="tile-emoji">&#128003;</span>
        <span class="tile-label">First-time owner</span>
        <span class="tile-sub">Brand new to dogs</span>
      </button>
      <button type="button" class="tile" data-name="experience" data-value="some">
        <span class="tile-emoji">&#128049;</span>
        <span class="tile-label">Had a dog before</span>
        <span class="tile-sub">Some experience</span>
      </button>
      <button type="button" class="tile" data-name="experience" data-value="experienced">
        <span class="tile-emoji">&#127942;</span>
        <span class="tile-label">Very experienced</span>
        <span class="tile-sub">Multiple dogs, trained them</span>
      </button>
      <button type="button" class="tile" data-name="experience" data-value="some">
        <span class="tile-emoji">&#128106;</span>
        <span class="tile-label">Family decision</span>
        <span class="tile-sub">First dog for the household</span>
      </button>
    </div>
    <input type="hidden" name="experience" id="input_experience">
  </div>

  <div class="quiz-nav">
    <button type="button" class="btn-back" id="btnBack" style="visibility:hidden">&#8592; Back</button>
    <button type="button" class="btn-next" id="btnNext" disabled>Continue &#8250;</button>
  </div>

</div>
</form>
</div>

<!-- PROCESSING SCREEN -->
<div class="proc" id="procScreen">
  <div class="proc-inner">
    <span class="proc-dog">&#128054;</span>
    <h2 class="proc-title">Finding your perfect match...</h2>
    <p class="proc-status" id="procStatus">Analyzing your lifestyle profile...</p>
    <div class="proc-bar"><div class="proc-bar-fill" id="procBarFill"></div></div>
    <div class="proc-match" id="procMatch">
      <strong>&#10003; Match found!</strong> Preparing your personalized results...
    </div>
  </div>
</div>

<script>
var steps = document.querySelectorAll('.quiz-step');
var btnNext = document.getElementById('btnNext');
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
  var stepName = steps[n-1].querySelector('.tile').dataset.name;
  btnNext.disabled = !answers[stepName];
  btnNext.innerHTML = n === total ? 'Find My Match &#128062;' : 'Continue &#8250;';
}

document.querySelectorAll('.tile').forEach(function(tile) {
  tile.addEventListener('click', function() {
    var name = this.dataset.name;
    answers[name] = this.dataset.value;
    document.getElementById('input_' + name).value = this.dataset.value;
    document.querySelectorAll('[data-name="' + name + '"]').forEach(function(t){ t.classList.remove('selected'); });
    this.classList.add('selected');
    btnNext.disabled = false;
  });
});

btnNext.addEventListener('click', function() {
  if (current < total) { current++; showStep(current); }
  else { runProcessingScreen(); }
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
    { text: 'Scanning 1,420 available shelter dogs...', pct: '45%',  delay: 1000 },
    { text: 'Calculating top breed matches...',         pct: '72%',  delay: 2100 },
    { text: 'Scoring compatibility factors...',         pct: '90%',  delay: 3000 },
    { text: 'Match found! Preparing your results...',   pct: '100%', delay: 3800 },
  ];

  messages.forEach(function(m) {
    setTimeout(function() {
      statusEl.textContent = m.text;
      barFill.style.width  = m.pct;
    }, m.delay);
  });

  setTimeout(function() { matchBox.classList.add('show'); }, 3800);

  // POST quiz in background while animation plays
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
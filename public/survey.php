<?php
session_start();
$pageTitle = 'Find Your Match - Foredog';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Survey.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = ['activity_level' => $_POST['activity_level'] ?? '', 'living_space' => $_POST['living_space'] ?? '', 'experience' => $_POST['experience'] ?? '', 'has_kids' => $_POST['has_kids'] ?? ''];
    $breedSlug = Survey::recommend($answers);
    $_SESSION['recommended_breed'] = $breedSlug;
    $db = Database::getInstance();
    $db->prepare('INSERT IGNORE INTO survey_sessions (session_id, recommended_breed_slug) VALUES (?, ?)')->execute([session_id(), $breedSlug]);
    header('Location: /breed.php?match=' . urlencode($breedSlug)); exit;
}
require __DIR__ . '/../templates/header.php';
?>
<style>
.survey-wrap { min-height:calc(100vh - 64px); display:flex; align-items:center; justify-content:center; padding:3rem 1.5rem; }
.survey-card { background:var(--white); border-radius:20px; box-shadow:0 8px 48px rgba(44,24,16,.1); width:100%; max-width:600px; padding:3rem; }
.progress-bar { height:4px; background:var(--sand); border-radius:2px; overflow:hidden; margin-bottom:.5rem; }
.progress-fill { height:100%; background:var(--amber); border-radius:2px; transition:width .4s; }
.progress-label { font-size:.8rem; color:var(--muted); margin-bottom:2rem; }
.survey-step { display:none; }
.survey-step.active { display:block; }
.step-label { font-size:.75rem; font-weight:500; letter-spacing:.1em; text-transform:uppercase; color:var(--amber); margin-bottom:.5rem; }
.step-question { font-family:'Playfair Display',serif; font-size:1.6rem; margin-bottom:2rem; line-height:1.3; }
.options { display:grid; grid-template-columns:1fr 1fr; gap:.85rem; }
.options.cols-1 { grid-template-columns:1fr; }
.option-btn { padding:1rem 1.25rem; border:2px solid var(--sand); border-radius:var(--radius); background:var(--cream); cursor:pointer; font-family:'DM Sans',sans-serif; font-size:.95rem; text-align:left; transition:all .18s; color:var(--bark); }
.option-btn:hover { border-color:var(--amber); background:#FFF5EE; }
.option-btn.selected { border-color:var(--amber); background:var(--amber); color:var(--white); }
.option-emoji { font-size:1.4rem; display:block; margin-bottom:.3rem; }
.survey-nav { display:flex; justify-content:space-between; align-items:center; margin-top:2rem; }
.btn-back { background:none; border:none; color:var(--muted); cursor:pointer; font-size:.9rem; }
.btn-next { background:var(--amber); color:var(--white); border:none; border-radius:50px; padding:.75rem 2rem; font-size:1rem; font-weight:500; cursor:pointer; transition:all .2s; }
.btn-next:hover { background:var(--amber-light); }
.btn-next:disabled { opacity:.4; cursor:not-allowed; }
</style>
<div class="survey-wrap"><div class="survey-card">
  <div style="text-align:center;margin-bottom:1.5rem;"><span style="font-family:'Playfair Display',serif;font-size:1.1rem;">🐾 Foredog Match Quiz</span></div>
  <div class="progress-bar"><div class="progress-fill" id="progressFill" style="width:25%"></div></div>
  <div class="progress-label" id="progressLabel">Step 1 of 4</div>
  <form method="POST" id="surveyForm">
    <div class="survey-step active" data-step="1">
      <p class="step-label">Your Lifestyle</p><h2 class="step-question">How active are you on a typical day?</h2>
      <div class="options">
        <button type="button" class="option-btn" data-name="activity_level" data-value="active"><span class="option-emoji">🏃</span>Very Active<br><small style="opacity:.7">Daily runs, hikes</small></button>
        <button type="button" class="option-btn" data-name="activity_level" data-value="moderate"><span class="option-emoji">🚶</span>Moderate<br><small style="opacity:.7">Daily walks</small></button>
        <button type="button" class="option-btn" data-name="activity_level" data-value="low"><span class="option-emoji">🛋️</span>Relaxed<br><small style="opacity:.7">Homebody lifestyle</small></button>
      </div>
      <input type="hidden" name="activity_level" id="input_activity_level">
    </div>
    <div class="survey-step" data-step="2">
      <p class="step-label">Your Home</p><h2 class="step-question">What kind of home do you live in?</h2>
      <div class="options">
        <button type="button" class="option-btn" data-name="living_space" data-value="house"><span class="option-emoji">🏡</span>House<br><small style="opacity:.7">With a yard</small></button>
        <button type="button" class="option-btn" data-name="living_space" data-value="apartment"><span class="option-emoji">🏢</span>Apartment<br><small style="opacity:.7">No private outdoor</small></button>
        <button type="button" class="option-btn" data-name="living_space" data-value="condo"><span class="option-emoji">🏙️</span>Condo<br><small style="opacity:.7">Shared outdoor space</small></button>
      </div>
      <input type="hidden" name="living_space" id="input_living_space">
    </div>
    <div class="survey-step" data-step="3">
      <p class="step-label">Your Experience</p><h2 class="step-question">Have you owned a dog before?</h2>
      <div class="options cols-1">
        <button type="button" class="option-btn" data-name="experience" data-value="first">🐣 This will be my first dog</button>
        <button type="button" class="option-btn" data-name="experience" data-value="some">🐕 Yes, I have had a dog or two</button>
        <button type="button" class="option-btn" data-name="experience" data-value="experienced">🏆 Very experienced dog owner</button>
      </div>
      <input type="hidden" name="experience" id="input_experience">
    </div>
    <div class="survey-step" data-step="4">
      <p class="step-label">Your Family</p><h2 class="step-question">Do you have children at home?</h2>
      <div class="options">
        <button type="button" class="option-btn" data-name="has_kids" data-value="yes"><span class="option-emoji">👨‍👩‍👧</span>Yes, I have kids</button>
        <button type="button" class="option-btn" data-name="has_kids" data-value="no"><span class="option-emoji">🧑‍💻</span>No kids at home</button>
      </div>
      <input type="hidden" name="has_kids" id="input_has_kids">
    </div>
    <div class="survey-nav">
      <button type="button" class="btn-back" id="btnBack" style="visibility:hidden;">Back</button>
      <button type="button" class="btn-next" id="btnNext" disabled>Continue</button>
    </div>
  </form>
</div></div>
<script>
const steps=document.querySelectorAll('.survey-step'),btnNext=document.getElementById('btnNext'),btnBack=document.getElementById('btnBack'),progress=document.getElementById('progressFill'),progLabel=document.getElementById('progressLabel');
let current=1;const total=steps.length,answers={};
function showStep(n){steps.forEach(s=>s.classList.remove('active'));steps[n-1].classList.add('active');progress.style.width=(n/total*100)+'%';progLabel.textContent='Step '+n+' of '+total;btnBack.style.visibility=n>1?'visible':'hidden';const nm=steps[n-1].querySelector('.option-btn').dataset.name;btnNext.disabled=!answers[nm];btnNext.textContent=n===total?'Find My Match':'Continue';}
document.querySelectorAll('.option-btn').forEach(btn=>{btn.addEventListener('click',()=>{const name=btn.dataset.name,val=btn.dataset.value;answers[name]=val;document.getElementById('input_'+name).value=val;document.querySelectorAll('[data-name="'+name+'"]').forEach(b=>b.classList.remove('selected'));btn.classList.add('selected');btnNext.disabled=false;});});
btnNext.addEventListener('click',()=>{if(current<total){current++;showStep(current);}else{document.getElementById('surveyForm').submit();}});
btnBack.addEventListener('click',()=>{if(current>1){current--;showStep(current);}});
</script>
<?php require __DIR__ . '/../templates/footer.php'; ?>

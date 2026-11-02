<?php
declare(strict_types=1);
session_start();
date_default_timezone_set('Asia/Kolkata');

/* ====== அமைப்புகள் ====== */
const ADMIN_PASSWORD = 'Anju632013*';          // <-- இதை கண்டிப்பாக மாற்றவும்
const DB_FILE        = __DIR__ . '/nutrition.sqlite';
const UPLOAD_DIR     = __DIR__ . '/uploads';
const SESSIONS = ['morning' => 'காலை', 'afternoon' => 'மதியம்', 'evening' => 'மாலை'];
const HEALTH   = [
	
    'none'      => '-',
	'improving' => 'முன்னேற்றம் உள்ளது',
    'ill'       => 'முன்னேற்றம் இல்லை',
	'recovered' => 'குணமாகிவிட்டார்',
];
// உடல் அமைப்பு அளவீடுகள் (பாடி கம்போசிஷன் மெஷினில் வருபவை)
const METRICS = [
    'bmi'          => 'BMI',
    'fat_pct'      => 'FAT %',
    'bmr'          => 'BMR (kcal)',
    'visceral_fat' => 'Visceral Fat',
    'subcut_fat'   => 'Subcutaneous Fat %',
    'meta_age'     => 'Metabolic Age',
];

/* ====== நோக்கங்கள் மற்றும் அதற்கான கூடுதல் கேள்விகள் ======
   புதிய நோக்கம் வேண்டுமானால் இங்கே ஒரு பிளாக் சேர்த்தால் போதும்; படிவத்தில் தானாக வரும்.
   type: text | number | select (options தேவை) */
const GOALS = [
    'weight_loss' => ['label' => 'Weight Loss (உடல் எடை குறைப்பு)', 'questions' => [
        'target_weight' => ['label' => 'இலக்கு எடை (kg)', 'type' => 'number'],
        'timeline'      => ['label' => 'எவ்வளவு காலத்தில் வேண்டும்?', 'type' => 'select', 'options' => ['1 மாதம்', '3 மாதம்', '6 மாதம்', '6 மாதத்திற்கு மேல்']],
        'past_attempts' => ['label' => 'முன்பு முயற்சித்தவை', 'type' => 'select', 'options' => ['இதுவே முதல் முறை', 'டயட் முயன்றுள்ளேன்', 'ஜிம் / உடற்பயிற்சி', 'மருந்து / சப்ளிமெண்ட்']],
        'activity'      => ['label' => 'தினசரி உடல் இயக்கம்', 'type' => 'select', 'options' => ['குறைவு', 'நடுத்தரம்', 'அதிகம்']],
        'weight_cause'  => ['label' => 'எடை கூடியதற்கான காரணம் (தெரிந்தால்)', 'type' => 'text'],
    ]],
    'weight_gain' => ['label' => 'Weight Gain (உடல் எடை அதிகரிப்பு)', 'questions' => [
        'target_weight' => ['label' => 'இலக்கு எடை (kg)', 'type' => 'number'],
        'appetite'      => ['label' => 'பசி', 'type' => 'select', 'options' => ['குறைவு', 'சாதாரணம்', 'அதிகம்']],
        'meals_per_day' => ['label' => 'ஒரு நாளில் உணவு வேளைகள்', 'type' => 'number'],
        'thin_reason'   => ['label' => 'மெலிவுக்கான காரணம்', 'type' => 'select', 'options' => ['மரபு / இயல்பு', 'நோய்க்குப் பின்', 'பசியின்மை', 'செரிமான பிரச்சனை', 'தெரியவில்லை']],
        'timeline'      => ['label' => 'எவ்வளவு காலத்தில் வேண்டும்?', 'type' => 'select', 'options' => ['1 மாதம்', '3 மாதம்', '6 மாதம்', '6 மாதத்திற்கு மேல்']],
    ]],
    'diabetes' => ['label' => 'Diabetes / Blood Sugar (சர்க்கரை நோய்)', 'questions' => [
        'type'          => ['label' => 'வகை', 'type' => 'select', 'options' => ['Type 1', 'Type 2', 'Pre-diabetes (ஆரம்ப நிலை)', 'Gestational (கர்ப்பகால)', 'தெரியவில்லை']],
        'years'         => ['label' => 'எத்தனை ஆண்டுகளாக?', 'type' => 'number'],
        'fbs'           => ['label' => 'Fasting Sugar (mg/dL)', 'type' => 'number'],
        'ppbs'          => ['label' => 'Post Meal Sugar (mg/dL)', 'type' => 'number'],
        'hba1c'         => ['label' => 'HbA1c (%)', 'type' => 'number'],
        'treatment'     => ['label' => 'தற்போதைய சிகிச்சை', 'type' => 'select', 'options' => ['எதுவும் இல்லை', 'மாத்திரை', 'இன்சுலின்', 'மாத்திரை + இன்சுலின்']],
    ]],
    'bp' => ['label' => 'Blood Pressure (இரத்த அழுத்தம்)', 'questions' => [
        'type'          => ['label' => 'வகை', 'type' => 'select', 'options' => ['High BP (உயர்)', 'Low BP (குறைவு)', 'மாறி மாறி வரும்']],
        'reading'       => ['label' => 'சமீபத்திய அளவு (எ.கா: 140/90)', 'type' => 'text'],
        'years'         => ['label' => 'எத்தனை ஆண்டுகளாக?', 'type' => 'number'],
        'treatment'     => ['label' => 'மாத்திரை எடுக்கிறாரா?', 'type' => 'select', 'options' => ['இல்லை', 'ஆம்']],
        'salt'          => ['label' => 'உப்பு பயன்பாடு', 'type' => 'select', 'options' => ['குறைவு', 'சாதாரணம்', 'அதிகம்']],
    ]],
    'thyroid' => ['label' => 'Thyroid (தைராய்டு பிரச்சனை)', 'questions' => [
        'type'          => ['label' => 'வகை', 'type' => 'select', 'options' => ['Hypothyroid (குறைவு)', 'Hyperthyroid (அதிகம்)', 'தெரியவில்லை / சந்தேகம்']],
        'tsh'           => ['label' => 'TSH மதிப்பு (µIU/mL)', 'type' => 'number'],
        'years'         => ['label' => 'எத்தனை ஆண்டுகளாக?', 'type' => 'number'],
        'treatment'     => ['label' => 'மாத்திரை எடுக்கிறாரா?', 'type' => 'select', 'options' => ['இல்லை', 'ஆம்']],
        'family'        => ['label' => 'குடும்பத்தில் தைராய்டு உள்ளதா?', 'type' => 'select', 'options' => ['இல்லை', 'ஆம்']],
    ]],
    'cholesterol' => ['label' => 'Cholesterol (கொழுப்பு)', 'questions' => [
        'total'         => ['label' => 'Total Cholesterol (mg/dL)', 'type' => 'number'],
        'ldl'           => ['label' => 'LDL (mg/dL)', 'type' => 'number'],
        'tg'            => ['label' => 'Triglycerides (mg/dL)', 'type' => 'number'],
        'treatment'     => ['label' => 'மாத்திரை எடுக்கிறாரா?', 'type' => 'select', 'options' => ['இல்லை', 'ஆம்']],
    ]],
    'pcod' => ['label' => 'PCOD / PCOS', 'questions' => [
        'cycle'         => ['label' => 'மாதவிடாய் சுழற்சி', 'type' => 'select', 'options' => ['சீராக உள்ளது', 'சீரற்றது', 'பல மாதங்களாக வரவில்லை']],
        'years'         => ['label' => 'எத்தனை ஆண்டுகளாக?', 'type' => 'number'],
        'conceive'      => ['label' => 'கருத்தரிக்க முயற்சிக்கிறாரா?', 'type' => 'select', 'options' => ['இல்லை', 'ஆம்']],
        'treatment'     => ['label' => 'மருந்து எடுக்கிறாரா?', 'type' => 'select', 'options' => ['இல்லை', 'ஆம்']],
    ]],
    'digestion' => ['label' => 'Digestion / Gastric (செரிமானம் / அசிடிட்டி)', 'questions' => [
        'problem'       => ['label' => 'பிரச்சனை', 'type' => 'select', 'options' => ['அசிடிட்டி / நெஞ்செரிச்சல்', 'மலச்சிக்கல்', 'வாயு தொல்லை', 'அல்சர்', 'IBS / வயிற்றுப்போக்கு']],
        'frequency'     => ['label' => 'எவ்வளவு அடிக்கடி?', 'type' => 'select', 'options' => ['தினமும்', 'வாரத்தில் சில நாட்கள்', 'எப்போதாவது']],
        'years'         => ['label' => 'எத்தனை ஆண்டுகளாக?', 'type' => 'number'],
    ]],
    'uric' => ['label' => 'Uric Acid / Joint Pain (யூரிக் அமிலம் / மூட்டு வலி)', 'questions' => [
        'uric_value'    => ['label' => 'Uric Acid மதிப்பு (mg/dL)', 'type' => 'number'],
        'pain_site'     => ['label' => 'வலி உள்ள இடம்', 'type' => 'text'],
        'treatment'     => ['label' => 'மாத்திரை எடுக்கிறாரா?', 'type' => 'select', 'options' => ['இல்லை', 'ஆம்']],
    ]],
    'wellness' => ['label' => 'General Wellness (பொது ஆரோக்கியம்)', 'questions' => [
        'sleep'         => ['label' => 'தூக்கம் (மணி நேரம்)', 'type' => 'number'],
        'water'         => ['label' => 'தண்ணீர் (லிட்டர்/நாள்)', 'type' => 'number'],
        'stress'        => ['label' => 'மன அழுத்தம்', 'type' => 'select', 'options' => ['குறைவு', 'நடுத்தரம்', 'அதிகம்']],
    ]],
];

// எந்த நோக்கத்தை தேர்வு செய்தாலும் வரும் பொதுவான கேள்விகள்
const COMMON_QUESTIONS = [
    'food_pref'     => ['label' => 'உணவு முறை ', 'type' => 'select', 'options' => ['சைவம் (Veg)', 'அசைவம் (Non-Veg)', 'முட்டை சைவம்']],
    'allergies'     => ['label' => 'உணவு ஒவ்வாமை (Allergy)', 'type' => 'text'],
    'medicines'     => ['label' => 'தற்போது எடுக்கும் மருந்துகள்', 'type' => 'text'],
    'smoking'       => ['label' => 'புகை / மது பழக்கம்', 'type' => 'select', 'options' => ['இல்லை', 'புகை', 'மது', 'இரண்டும்']],
];

/* ====== உதவி செயல்பாடுகள் ====== */
function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $pdo = new PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        phone TEXT,
        age INTEGER,
        gender TEXT,
        height_cm REAL,
        start_weight REAL,
        condition_name TEXT,
        photo TEXT,
        joined_date TEXT NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS visits (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_id INTEGER NOT NULL,
        visit_date TEXT NOT NULL,
        session TEXT NOT NULL CHECK (session IN ('morning','afternoon','evening')),
        weight REAL,
        health TEXT NOT NULL DEFAULT 'none',
        notes TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (customer_id, visit_date, session),
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
    )");
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_visits_date ON visits(visit_date)');
    $haveC = array_column($pdo->query('PRAGMA table_info(customers)')->fetchAll(), 'name');
    if (!in_array('goal_data', $haveC, true)) $pdo->exec('ALTER TABLE customers ADD COLUMN goal_data TEXT');
    // பழைய டேட்டாபேஸில் புதிய நெடுவரிசைகளை தானாக சேர்க்கும் (தரவு அழியாது)
    $have = array_column($pdo->query('PRAGMA table_info(visits)')->fetchAll(), 'name');
    foreach (['bmi' => 'REAL', 'fat_pct' => 'REAL', 'bmr' => 'REAL', 'visceral_fat' => 'REAL', 'subcut_fat' => 'REAL', 'meta_age' => 'REAL'] as $col => $type) {
        if (!in_array($col, $have, true)) $pdo->exec("ALTER TABLE visits ADD COLUMN $col $type");
    }
    return $pdo;
}

function csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function check_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('பாதுகாப்பு சரிபார்ப்பு தோல்வி. பக்கத்தை refresh செய்யவும்.');
    }
}
function flash(string $msg, string $type = 'ok'): void { $_SESSION['flash'] = [$msg, $type]; }
function redirect(string $url): never { header('Location: ' . $url); exit; }

function save_photo(array $f): ?string {
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('புகைப்படம் பதிவேற்றம் தோல்வி.');
    if ($f['size'] > 5 * 1024 * 1024) throw new RuntimeException('புகைப்படம் 5MB-க்கு குறைவாக இருக்க வேண்டும்.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) throw new RuntimeException('JPG, PNG அல்லது WEBP படங்கள் மட்டுமே அனுமதிக்கப்படும்.');
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $name = bin2hex(random_bytes(12)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $name)) throw new RuntimeException('புகைப்படத்தை சேமிக்க முடியவில்லை.');
    return $name;
}

function change_html(?float $from, ?float $to): string {
    if ($from === null || $to === null) return '-';
    $d = round($to - $from, 1);
    if ($d == 0) return '<span class="muted">மாற்றம் இல்லை</span>';
    return $d > 0 ? '<span class="up">▲ +' . $d . ' kg</span>' : '<span class="down">▼ ' . $d . ' kg</span>';
}

function health_badge(?string $h): string {
    if (!$h) return '-';
    return '<span class="badge b-' . e($h) . '">' . e(HEALTH[$h] ?? $h) . '</span>';
}

/* ====== உள்நுழைவு ====== */
if (isset($_GET['logout'])) { session_destroy(); redirect('index.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    check_csrf();
    if (hash_equals(ADMIN_PASSWORD, (string)($_POST['password'] ?? ''))) {
        session_regenerate_id(true);
        $_SESSION['auth'] = true;
        redirect('index.php');
    }
    flash('கடவுச்சொல் தவறு.', 'err');
    redirect('index.php');
}

function layout_start(string $title): void { ?>
<!DOCTYPE html>
<html lang="ta">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> - நியூட்ரிஷன் சென்டர்</title>
<style>
:root{--bg:#f4f7f5;--card:#fff;--ink:#1d2b24;--muted:#6b7a72;--brand:#1f7a4d;--line:#dfe7e2;--err:#b3261e}
@media(prefers-color-scheme:dark){:root{--bg:#121a16;--card:#1b2620;--ink:#e6efe9;--muted:#9bb0a4;--brand:#4fc58a;--line:#2b3a32;--err:#ff8a80}}
*{box-sizing:border-box}
body{margin:0;font-family:system-ui,"Noto Sans Tamil",Latha,sans-serif;background:var(--bg);color:var(--ink)}
header{background:var(--brand);color:#fff;padding:12px 16px;display:flex;flex-wrap:wrap;gap:12px;align-items:center}
header h1{font-size:18px;margin:0 auto 0 0}
header a{color:#fff;text-decoration:none;padding:6px 10px;border-radius:6px;background:rgba(255,255,255,.15);font-size:14px}
main{max-width:1000px;margin:16px auto;padding:0 12px}
.card{background:var(--card);border:1px solid var(--line);border-radius:10px;padding:16px;margin-bottom:16px}
h2{margin:0 0 12px;font-size:17px}
table{width:100%;border-collapse:collapse;font-size:14px}
th,td{padding:8px;border-bottom:1px solid var(--line);text-align:left;vertical-align:middle}
.scroll{overflow-x:auto}
label{display:block;font-size:13px;color:var(--muted);margin:10px 0 4px}
input,select,textarea{width:100%;padding:9px;border:1px solid var(--line);border-radius:6px;background:var(--bg);color:var(--ink);font:inherit}
button,.btn{background:var(--brand);color:#fff;border:0;border-radius:6px;padding:9px 16px;font:inherit;cursor:pointer;text-decoration:none;display:inline-block}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:0 14px}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px}
.stat{background:var(--bg);border-radius:8px;padding:12px;text-align:center}
.stat b{display:block;font-size:24px;color:var(--brand)}
.stat span{font-size:13px;color:var(--muted)}
.thumb{width:44px;height:44px;border-radius:50%;object-fit:cover;background:var(--line)}
.big{width:120px;height:120px;border-radius:12px;object-fit:cover;background:var(--line)}
.muted{color:var(--muted)}.up{color:#c77700;font-weight:600}.down{color:#1f7a4d;font-weight:600}
.badge{padding:3px 8px;border-radius:12px;font-size:12px;background:var(--line)}
.b-ill{background:#fde0dd;color:#8a1c14}.b-improving{background:#fff1cc;color:#7a5600}.b-recovered{background:#d6f3e3;color:#0f5c36}
.flash{padding:10px 14px;border-radius:8px;margin-bottom:14px}
.flash.ok{background:#d6f3e3;color:#0f5c36}.flash.err{background:#fde0dd;color:#8a1c14}
.row{display:flex;gap:14px;flex-wrap:wrap;align-items:center}
a{color:var(--brand)}
.btn.danger,button.danger{background:var(--err)}
[hidden]{display:none!important}
.chips{display:flex;flex-wrap:wrap;gap:8px}
.chip{display:flex;align-items:center;gap:6px;margin:0;padding:7px 13px;border:1px solid var(--line);border-radius:20px;background:var(--bg);color:var(--ink);font-size:14px;cursor:pointer}
.chip input{width:auto;margin:0}
.chip:has(input:checked){background:var(--brand);border-color:var(--brand);color:#fff}
.goal-q{margin-top:14px;padding:4px 14px 12px;border-left:4px solid var(--brand);background:var(--bg);border-radius:0 8px 8px 0}
.goal-q h3{margin:10px 0 0;font-size:15px;color:var(--brand)}
.qa{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:4px 14px}
.qa div{font-size:14px;padding:3px 0}
@media print{header,.noprint{display:none!important}.card{break-inside:avoid}}
</style>
</head>
<body>
<header>
  <h1>🥗 நியூட்ரிஷன் சென்டர்</h1>
  <?php if (!empty($_SESSION['auth'])): ?>
  <a href="index.php">முகப்பு</a>
  <a href="index.php?page=customers">வாடிக்கையாளர்கள்</a>
  <a href="index.php?page=report">📊 மாதாந்திர அறிக்கை</a>
  <a href="index.php?page=add">+ புதிய வாடிக்கையாளர்</a>
  <a href="index.php?logout=1">வெளியேறு</a>
  <?php endif; ?>
</header>
<main>
<?php
    if (!empty($_SESSION['flash'])) {
        [$m, $t] = $_SESSION['flash']; unset($_SESSION['flash']);
        echo '<div class="flash ' . e($t) . '">' . e($m) . '</div>';
    }
}
function layout_end(): void { echo '</main></body></html>'; }

if (empty($_SESSION['auth'])) {
    layout_start('உள்நுழைவு'); ?>
    <div class="card" style="max-width:360px;margin:40px auto">
      <h2>உள்நுழைவு</h2>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
        <input type="hidden" name="action" value="login">
        <label>கடவுச்சொல்</label>
        <input type="password" name="password" required autofocus>
        <p><button>உள்நுழை</button></p>
      </form>
    </div>
    <?php layout_end(); exit;
}

$pdo = db();

/* ====== POST செயல்கள் ====== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_customer') {
            $name = trim((string)$_POST['name']);
            if ($name === '') throw new RuntimeException('பெயர் அவசியம்.');
            $photo = save_photo($_FILES['photo'] ?? []);
            $num = fn($k) => ($_POST[$k] ?? '') === '' ? null : (float)$_POST[$k];
            $goals = goals_from_post();
            $st = $pdo->prepare('INSERT INTO customers (name, phone, age, gender, height_cm, start_weight, condition_name, goal_data, photo, joined_date)
                                 VALUES (?,?,?,?,?,?,?,?,?,?)');
            $st->execute([
                $name, trim((string)$_POST['phone']), $num('age'), $_POST['gender'] ?? '',
                $num('height_cm'), $num('start_weight'), $goals['summary'], $goals['data'],
                $photo, date('Y-m-d'),
            ]);
            flash('வாடிக்கையாளர் சேர்க்கப்பட்டார்.');
            redirect('index.php?page=view&id=' . $pdo->lastInsertId());
        }

        if ($action === 'checkin') {
            $cid = (int)$_POST['customer_id'];
            $session = $_POST['session'] ?? '';
            $health = $_POST['health'] ?? 'none';
            $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$_POST['visit_date']) ? $_POST['visit_date'] : date('Y-m-d');
            if (!isset(SESSIONS[$session]) || !isset(HEALTH[$health]) || $cid < 1) throw new RuntimeException('தவறான தகவல்.');
            $w = ($_POST['weight'] ?? '') === '' ? null : (float)$_POST['weight'];
            try {
                $hq = $pdo->prepare('SELECT height_cm FROM customers WHERE id = ?');
                $hq->execute([$cid]);
                $hc = $hq->fetchColumn();
                $m = metrics_from_post($w, ($hc !== false && $hc !== null) ? (float)$hc : null);
                $st = $pdo->prepare('INSERT INTO visits (customer_id, visit_date, session, weight, health, notes, bmi, fat_pct, bmr, visceral_fat, subcut_fat, meta_age)
                                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
                $st->execute([$cid, $date, $session, $w, $health, trim((string)$_POST['notes']),
                              $m['bmi'], $m['fat_pct'], $m['bmr'], $m['visceral_fat'], $m['subcut_fat'], $m['meta_age']]);
                flash('வருகை பதிவு செய்யப்பட்டது (' . SESSIONS[$session] . ').');
            } catch (PDOException $ex) {
                if (str_contains($ex->getMessage(), 'UNIQUE')) throw new RuntimeException('இந்த வாடிக்கையாளருக்கு இந்த வேளை ஏற்கனவே பதிவாகியுள்ளது.');
                throw $ex;
            }
            redirect($_POST['back'] ?? 'index.php');
        }

        if ($action === 'update_customer') {
            $id = (int)$_POST['id'];
            $st = $pdo->prepare('SELECT photo FROM customers WHERE id = ?');
            $st->execute([$id]);
            $old = $st->fetch();
            if (!$old) throw new RuntimeException('வாடிக்கையாளர் கிடைக்கவில்லை.');
            $name = trim((string)$_POST['name']);
            if ($name === '') throw new RuntimeException('பெயர் அவசியம்.');
            $newPhoto = save_photo($_FILES['photo'] ?? []);
            $num = fn($k) => ($_POST[$k] ?? '') === '' ? null : (float)$_POST[$k];
            $goals = goals_from_post();
            $st = $pdo->prepare('UPDATE customers SET name=?, phone=?, age=?, gender=?, height_cm=?, start_weight=?, condition_name=?, goal_data=?, photo=? WHERE id=?');
            $st->execute([
                $name, trim((string)$_POST['phone']), $num('age'), $_POST['gender'] ?? '',
                $num('height_cm'), $num('start_weight'), $goals['summary'], $goals['data'],
                $newPhoto ?? $old['photo'], $id,
            ]);
            if ($newPhoto && $old['photo']) @unlink(UPLOAD_DIR . '/' . basename($old['photo']));
            flash('விவரங்கள் புதுப்பிக்கப்பட்டன.');
            redirect('index.php?page=view&id=' . $id);
        }

        if ($action === 'delete_customer') {
            $id = (int)$_POST['id'];
            $st = $pdo->prepare('SELECT photo FROM customers WHERE id = ?');
            $st->execute([$id]);
            $old = $st->fetch();
            $pdo->prepare('DELETE FROM customers WHERE id = ?')->execute([$id]);
            if ($old && $old['photo']) @unlink(UPLOAD_DIR . '/' . basename($old['photo']));
            flash('வாடிக்கையாளர் மற்றும் அவரது வருகை பதிவுகள் நீக்கப்பட்டன.');
            redirect('index.php?page=customers');
        }

        if ($action === 'update_visit') {
            $vid = (int)$_POST['visit_id'];
            $session = $_POST['session'] ?? '';
            $health = $_POST['health'] ?? 'none';
            $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$_POST['visit_date']) ? $_POST['visit_date'] : null;
            if (!$date || !isset(SESSIONS[$session]) || !isset(HEALTH[$health])) throw new RuntimeException('தவறான தகவல்.');
            $w = ($_POST['weight'] ?? '') === '' ? null : (float)$_POST['weight'];
            try {
                $hq = $pdo->prepare('SELECT c.height_cm FROM visits v JOIN customers c ON c.id = v.customer_id WHERE v.id = ?');
                $hq->execute([$vid]);
                $hc = $hq->fetchColumn();
                $m = metrics_from_post($w, ($hc !== false && $hc !== null) ? (float)$hc : null);
                $st = $pdo->prepare('UPDATE visits SET visit_date=?, session=?, weight=?, health=?, notes=?,
                                     bmi=?, fat_pct=?, bmr=?, visceral_fat=?, subcut_fat=?, meta_age=? WHERE id=?');
                $st->execute([$date, $session, $w, $health, trim((string)$_POST['notes']),
                              $m['bmi'], $m['fat_pct'], $m['bmr'], $m['visceral_fat'], $m['subcut_fat'], $m['meta_age'], $vid]);
            } catch (PDOException $ex) {
                if (str_contains($ex->getMessage(), 'UNIQUE')) throw new RuntimeException('அந்த தேதி மற்றும் வேளைக்கு ஏற்கனவே பதிவு உள்ளது.');
                throw $ex;
            }
            $st = $pdo->prepare('SELECT customer_id FROM visits WHERE id = ?');
            $st->execute([$vid]);
            flash('வருகை பதிவு திருத்தப்பட்டது.');
            redirect('index.php?page=view&id=' . (int)$st->fetchColumn());
        }

        if ($action === 'delete_visit') {
            $pdo->prepare('DELETE FROM visits WHERE id = ?')->execute([(int)$_POST['visit_id']]);
            flash('வருகை பதிவு நீக்கப்பட்டது.');
            redirect($_POST['back'] ?? 'index.php');
        }
    } catch (RuntimeException $ex) {
        flash($ex->getMessage(), 'err');
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
    }
}

/* ====== மாதம் / அறிக்கை / ஏற்றுமதி உதவி ====== */
function month_range(?string $m): array {
    $d = ($m && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $m)) ? $m : date('Y-m');
    $first = $d . '-01';
    return [$d, $first, date('Y-m-t', strtotime($first))];
}

function monthly_rows(PDO $pdo, string $first, string $last): array {
    $sql = "SELECT c.id, c.name, c.phone, c.start_weight,
        COUNT(v.id) AS visit_count, COUNT(DISTINCT v.visit_date) AS days,
        (SELECT weight FROM visits WHERE customer_id=c.id AND weight IS NOT NULL AND visit_date < :first ORDER BY visit_date DESC, id DESC LIMIT 1) AS prev_w,
        (SELECT weight FROM visits WHERE customer_id=c.id AND weight IS NOT NULL AND visit_date BETWEEN :first AND :last ORDER BY visit_date ASC, id ASC LIMIT 1) AS first_w,
        (SELECT weight FROM visits WHERE customer_id=c.id AND weight IS NOT NULL AND visit_date BETWEEN :first AND :last ORDER BY visit_date DESC, id DESC LIMIT 1) AS last_w,
        (SELECT health FROM visits WHERE customer_id=c.id AND visit_date BETWEEN :first AND :last ORDER BY visit_date DESC, id DESC LIMIT 1) AS last_health
        FROM customers c
        JOIN visits v ON v.customer_id = c.id AND v.visit_date BETWEEN :first AND :last
        GROUP BY c.id ORDER BY c.name";
    $st = $pdo->prepare($sql);
    $st->execute([':first' => $first, ':last' => $last]);
    $rows = $st->fetchAll();
    foreach ($rows as &$r) {
        $base = $r['prev_w'] ?? $r['start_weight'] ?? $r['first_w'];
        $r['base_w'] = $base !== null ? (float)$base : null;
        $r['last_w'] = $r['last_w'] !== null ? (float)$r['last_w'] : null;
        $r['diff'] = ($r['base_w'] !== null && $r['last_w'] !== null) ? round($r['last_w'] - $r['base_w'], 1) : null;
    }
    unset($r);
    return $rows;
}

// Excel-ல் தமிழ் சரியாக திறக்க UTF-8 BOM சேர்க்கப்படுகிறது
function csv_out(string $file, array $head, array $rows): never {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $file . '"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    $clean = fn($c) => (is_string($c) && preg_match('/^[=+\-@\t\r]/', $c)) ? "'" . $c : $c;
    fputcsv($out, $head, ',', '"', '\\');
    foreach ($rows as $r) fputcsv($out, array_map($clean, $r), ',', '"', '\\');
    fclose($out);
    exit;
}

if (isset($_GET['export'])) {
    [$month, $first, $last] = month_range($_GET['month'] ?? null);
    $type = $_GET['export'];

    if ($type === 'customers') {
        $rows = $pdo->query("SELECT c.*,
            (SELECT weight FROM visits WHERE customer_id=c.id AND weight IS NOT NULL ORDER BY visit_date DESC, id DESC LIMIT 1) AS last_w,
            (SELECT health FROM visits WHERE customer_id=c.id ORDER BY visit_date DESC, id DESC LIMIT 1) AS last_h,
            (SELECT COUNT(*) FROM visits WHERE customer_id=c.id) AS n
            FROM customers c ORDER BY c.name")->fetchAll();
        $data = [];
        foreach ($rows as $r) {
            $diff = ($r['start_weight'] !== null && $r['last_w'] !== null) ? round((float)$r['last_w'] - (float)$r['start_weight'], 1) : '';
            $data[] = [$r['name'], $r['phone'], $r['age'], $r['gender'], $r['height_cm'], $r['condition_name'], $r['joined_date'],
                       $r['start_weight'], $r['last_w'], $diff, HEALTH[$r['last_h']] ?? '', $r['n']];
        }
        csv_out('customers.csv',
            ['பெயர்', 'தொலைபேசி', 'வயது', 'பாலினம்', 'உயரம் (cm)', 'நோய்/நோக்கம்', 'சேர்ந்த தேதி', 'தொடக்க எடை', 'தற்போதைய எடை', 'மாற்றம் (kg)', 'உடல்நிலை', 'வருகைகள்'],
            $data);
    }

    if ($type === 'visits') {
        $st = $pdo->prepare("SELECT v.visit_date, v.session, c.name, c.phone, v.weight, v.health, v.notes,
            v.bmi, v.fat_pct, v.bmr, v.visceral_fat, v.subcut_fat, v.meta_age
            FROM visits v JOIN customers c ON c.id = v.customer_id
            WHERE v.visit_date BETWEEN ? AND ?
            ORDER BY v.visit_date, CASE v.session WHEN 'morning' THEN 1 WHEN 'afternoon' THEN 2 ELSE 3 END, c.name");
        $st->execute([$first, $last]);
        $data = [];
        foreach ($st->fetchAll() as $r) {
            $data[] = [$r['visit_date'], SESSIONS[$r['session']], $r['name'], $r['phone'], $r['weight'], HEALTH[$r['health']] ?? '',
                       $r['bmi'], $r['fat_pct'], $r['bmr'], $r['visceral_fat'], $r['subcut_fat'], $r['meta_age'], $r['notes']];
        }
        csv_out("visits-$month.csv", array_merge(
            ['தேதி', 'வேளை', 'பெயர்', 'தொலைபேசி', 'எடை (kg)', 'உடல்நிலை'],
            array_values(METRICS),
            ['குறிப்பு']
        ), $data);
    }

    if ($type === 'summary') {
        $data = [];
        foreach (monthly_rows($pdo, $first, $last) as $r) {
            $data[] = [$r['name'], $r['phone'], $r['visit_count'], $r['days'], $r['base_w'], $r['last_w'], $r['diff'], HEALTH[$r['last_health']] ?? ''];
        }
        csv_out("summary-$month.csv", ['பெயர்', 'தொலைபேசி', 'வருகைகள்', 'வந்த நாட்கள்', 'மாத தொடக்க எடை', 'மாத இறுதி எடை', 'மாற்றம் (kg)', 'உடல்நிலை'], $data);
    }
    redirect('index.php');
}

/* ====== நோக்கம் / கூடுதல் கேள்விகள் ====== */
function render_question(string $g, string $qk, array $q, string $val): void {
    $name = "q[$g][$qk]"; ?>
    <div>
      <label><?= e($q['label']) ?></label>
      <?php if ($q['type'] === 'select'): ?>
        <select name="<?= e($name) ?>">
          <option value="">--</option>
          <?php foreach ($q['options'] as $o): ?>
            <option value="<?= e($o) ?>" <?= $val === $o ? 'selected' : '' ?>><?= e($o) ?></option>
          <?php endforeach; ?>
        </select>
      <?php elseif ($q['type'] === 'number'): ?>
        <input type="number" step="any" min="0" name="<?= e($name) ?>" value="<?= e($val) ?>">
      <?php else: ?>
        <input type="text" maxlength="200" name="<?= e($name) ?>" value="<?= e($val) ?>">
      <?php endif; ?>
    </div>
<?php }

// நோக்கம் தேர்வு + தேர்வுக்கு ஏற்ப தானாக தோன்றும் கேள்விகள்
function goal_fields(array $c = []): void {
    $d = json_decode((string)($c['goal_data'] ?? ''), true);
    $sel = is_array($d['goals'] ?? null) ? $d['goals'] : [];
    $ans = is_array($d['answers'] ?? null) ? $d['answers'] : [];
    // பழைய பதிவுகளில் goal_data இல்லையென்றால் பழைய உரையை "மற்றவை"-ல் காட்டு
    $other = is_array($d) ? (string)($d['other'] ?? '') : (string)($c['condition_name'] ?? ''); ?>
    <div class="goalbox">
      <label><b>நோக்கம் / பிரச்சனை</b> – ஒன்றுக்கு மேற்பட்டதையும் தேர்வு செய்யலாம்</label>
      <div class="chips">
        <?php foreach (GOALS as $gk => $g): ?>
          <label class="chip"><input type="checkbox" name="goals[]" value="<?= e($gk) ?>" <?= in_array($gk, $sel, true) ? 'checked' : '' ?>> <?= e($g['label']) ?></label>
        <?php endforeach; ?>
      </div>

      <?php foreach (GOALS as $gk => $g): ?>
        <div class="goal-q" data-goal="<?= e($gk) ?>" hidden>
          <h3><?= e($g['label']) ?> – கூடுதல் கேள்விகள்</h3>
          <div class="grid">
            <?php foreach ($g['questions'] as $qk => $q) render_question($gk, $qk, $q, (string)($ans[$gk][$qk] ?? '')); ?>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="goal-q" data-goal="common" hidden>
        <h3>பொதுவான கேள்விகள்</h3>
        <div class="grid">
          <?php foreach (COMMON_QUESTIONS as $qk => $q) render_question('common', $qk, $q, (string)($ans['common'][$qk] ?? '')); ?>
        </div>
      </div>

      <label>மற்றவை (வேறு நோக்கம் / நோய் இருந்தால் எழுதவும்)</label>
      <input name="goal_other" maxlength="200" value="<?= e($other) ?>">

      <script>
      (function () {
        const root = document.currentScript.parentElement;
        function sync() {
          const chosen = Array.from(root.querySelectorAll('input[name="goals[]"]:checked')).map(i => i.value);
          root.querySelectorAll('.goal-q').forEach(s => {
            const g = s.dataset.goal;
            const show = g === 'common' ? chosen.length > 0 : chosen.includes(g);
            s.hidden = !show;
            s.querySelectorAll('input,select').forEach(i => { i.disabled = !show; });
          });
        }
        root.addEventListener('change', ev => { if (ev.target.name === 'goals[]') sync(); });
        sync();
      })();
      </script>
    </div>
<?php }

// POST தரவிலிருந்து நோக்கம் + பதில்களை சரிபார்த்து எடுக்கும்
function goals_from_post(): array {
    $selected = array_values(array_filter((array)($_POST['goals'] ?? []), fn($g) => is_string($g) && isset(GOALS[$g])));
    $post = is_array($_POST['q'] ?? null) ? $_POST['q'] : [];
    $answers = [];
    foreach ($selected ? array_merge($selected, ['common']) : [] as $g) {
        $defs = $g === 'common' ? COMMON_QUESTIONS : GOALS[$g]['questions'];
        foreach ($defs as $qk => $q) {
            $v = $post[$g][$qk] ?? '';
            if (!is_string($v)) continue;
            $v = mb_substr(trim($v), 0, 200);
            if ($v === '') continue;
            if ($q['type'] === 'select' && !in_array($v, $q['options'], true)) continue;
            if ($q['type'] === 'number' && !is_numeric($v)) continue;
            $answers[$g][$qk] = $v;
        }
    }
    $other = mb_substr(trim((string)($_POST['goal_other'] ?? '')), 0, 200);
    $labels = array_map(fn($g) => GOALS[$g]['label'], $selected);
    if ($other !== '') $labels[] = $other;
    return [
        'data'    => json_encode(['goals' => $selected, 'other' => $other, 'answers' => $answers], JSON_UNESCAPED_UNICODE),
        'summary' => implode(', ', $labels),
    ];
}

// வாடிக்கையாளர் பக்கத்தில் காட்ட
function goals_display(array $c): void {
    $d = json_decode((string)($c['goal_data'] ?? ''), true);
    $sel = is_array($d['goals'] ?? null) ? $d['goals'] : [];
    $ans = is_array($d['answers'] ?? null) ? $d['answers'] : [];
    $other = is_array($d) ? (string)($d['other'] ?? '') : (string)($c['condition_name'] ?? '');
    if (!$sel && $other === '') return; ?>
    <div class="card">
      <h2>நோக்கம் / உடல்நல விவரம்</h2>
      <p class="chips">
        <?php foreach ($sel as $g): if (isset(GOALS[$g])): ?><span class="badge"><?= e(GOALS[$g]['label']) ?></span><?php endif; endforeach; ?>
        <?php if ($other !== ''): ?><span class="badge"><?= e($other) ?></span><?php endif; ?>
      </p>
      <?php foreach ($sel as $g): if (!isset(GOALS[$g]) || empty($ans[$g])) continue; ?>
        <h3 style="margin:14px 0 4px;font-size:15px;color:var(--brand)"><?= e(GOALS[$g]['label']) ?></h3>
        <div class="qa">
          <?php foreach (GOALS[$g]['questions'] as $qk => $q): if (isset($ans[$g][$qk])): ?>
            <div><span class="muted"><?= e($q['label']) ?>:</span> <b><?= e((string)$ans[$g][$qk]) ?></b></div>
          <?php endif; endforeach; ?>
        </div>
      <?php endforeach; ?>
      <?php if (!empty($ans['common'])): ?>
        <h3 style="margin:14px 0 4px;font-size:15px;color:var(--brand)">பொதுவான விவரங்கள்</h3>
        <div class="qa">
          <?php foreach (COMMON_QUESTIONS as $qk => $q): if (isset($ans['common'][$qk])): ?>
            <div><span class="muted"><?= e($q['label']) ?>:</span> <b><?= e((string)$ans['common'][$qk]) ?></b></div>
          <?php endif; endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
<?php }

/* ====== உடல் அமைப்பு அளவீடுகள் ====== */
function metrics_from_post(?float $weight, ?float $heightCm): array {
    $out = [];
    foreach (METRICS as $k => $_) {
        $v = $_POST[$k] ?? '';
        $out[$k] = (is_numeric($v) && (float)$v >= 0) ? (float)$v : null;
    }
    // BMI காலியாக இருந்தால் எடை மற்றும் உயரத்திலிருந்து தானாக கணக்கிடப்படும்
    if ($out['bmi'] === null && $weight && $heightCm) {
        $out['bmi'] = round($weight / pow($heightCm / 100, 2), 1);
    }
    return $out;
}

function metric_fields(array $v = []): void { ?>
    <label style="margin-top:14px"><b>உடல் அமைப்பு அளவீடுகள்</b> – தெரியாதவற்றை காலியாக விடலாம்</label>
    <div class="grid">
      <?php foreach (METRICS as $k => $label): ?>
        <div>
          <label><?= e($label) ?></label>
          <input type="number" name="<?= $k ?>" step="any" min="0" value="<?= e((string)($v[$k] ?? '')) ?>">
        </div>
      <?php endforeach; ?>
    </div>
<?php }

/* ====== பொது: செக்-இன் படிவம் ====== */
function checkin_form(PDO $pdo, ?int $cid, string $back): void {
    $customers = $pdo->query('SELECT id, name, phone, height_cm FROM customers ORDER BY name')->fetchAll();
    $hour = (int)date('G');
    $defaultSession = $hour < 12 ? 'morning' : ($hour < 17 ? 'afternoon' : 'evening'); ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
      <input type="hidden" name="action" value="checkin">
      <input type="hidden" name="back" value="<?= e($back) ?>">
      <div class="grid">
        <div>
          <label>வாடிக்கையாளர்</label>
          <select name="customer_id" required>
            <option value="">-- தேர்வு செய்க --</option>
            <?php foreach ($customers as $c): ?>
              <option value="<?= $c['id'] ?>" data-height="<?= e((string)$c['height_cm']) ?>" <?= $cid === (int)$c['id'] ? 'selected' : '' ?>>
                <?= e($c['name']) ?> <?= $c['phone'] ? '(' . e($c['phone']) . ')' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>வேளை</label>
          <select name="session">
            <?php foreach (SESSIONS as $k => $v): ?>
              <option value="<?= $k ?>" <?= $k === $defaultSession ? 'selected' : '' ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label>தேதி</label>
          <input type="date" name="visit_date" value="<?= date('Y-m-d') ?>">
        </div>
        <div>
          <label>இன்றைய எடை (kg)</label>
          <input type="number" name="weight" step="0.1" min="1" max="400">
        </div>
        <div>
          <label>உடல்நிலை</label>
          <select name="health">
            <?php foreach (HEALTH as $k => $v): ?><option value="<?= $k ?>"><?= e($v) ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <?php metric_fields(); ?>
      <label>குறிப்பு</label>
      <input type="text" name="notes" maxlength="300">
      <p><button>✔ வருகையை பதிவு செய்</button></p>
      <script>
      (function () {
        const f = document.currentScript.parentElement;
        const sel = f.querySelector('[name=customer_id]'), w = f.querySelector('[name=weight]'), b = f.querySelector('[name=bmi]');
        let manual = false;
        b.addEventListener('input', () => { manual = b.value !== ''; });
        function calc() {
          if (manual) return;
          const h = parseFloat(sel.selectedOptions[0] && sel.selectedOptions[0].dataset.height), kg = parseFloat(w.value);
          b.value = (h > 0 && kg > 0) ? (kg / Math.pow(h / 100, 2)).toFixed(1) : '';
        }
        sel.addEventListener('change', calc);
        w.addEventListener('input', calc);
        calc();
      })();
      </script>
    </form>
<?php }

/* ====== பக்கங்கள் ====== */
$page = $_GET['page'] ?? 'dashboard';

/* ---- வாடிக்கையாளர் திருத்து ---- */
if ($page === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $st = $pdo->prepare('SELECT * FROM customers WHERE id = ?');
    $st->execute([$id]);
    $c = $st->fetch();
    if (!$c) { flash('வாடிக்கையாளர் கிடைக்கவில்லை.', 'err'); redirect('index.php?page=customers'); }
    layout_start('திருத்து'); ?>
    <div class="card">
      <h2>வாடிக்கையாளர் விவரம் திருத்து</h2>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
        <input type="hidden" name="action" value="update_customer">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="grid">
          <div><label>பெயர் *</label><input name="name" required maxlength="100" value="<?= e($c['name']) ?>"></div>
          <div><label>தொலைபேசி</label><input name="phone" maxlength="20" inputmode="tel" value="<?= e($c['phone']) ?>"></div>
          <div><label>வயது</label><input type="number" name="age" min="0" max="120" value="<?= e((string)$c['age']) ?>"></div>
          <div><label>பாலினம்</label>
            <select name="gender">
              <option value="">--</option>
              <?php foreach (['ஆண்', 'பெண்', 'மற்றவை'] as $g): ?>
                <option <?= $c['gender'] === $g ? 'selected' : '' ?>><?= e($g) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div><label>உயரம் (cm)</label><input type="number" name="height_cm" step="0.1" value="<?= e((string)$c['height_cm']) ?>"></div>
          <div><label>தொடக்க எடை (kg)</label><input type="number" name="start_weight" step="0.1" value="<?= e((string)$c['start_weight']) ?>"></div>
        </div>
        <?php goal_fields($c); ?>
        <label>புகைப்படம் (புதிதாக தேர்வு செய்தால் மட்டுமே மாறும்)</label>
        <?php if ($c['photo']): ?><p><img class="big" src="uploads/<?= e($c['photo']) ?>" alt=""></p><?php endif; ?>
        <input type="file" name="photo" accept="image/*" capture="environment">
        <p class="row"><button>சேமி</button> <a class="btn" style="background:var(--muted)" href="?page=view&id=<?= $id ?>">ரத்து</a></p>
      </form>
    </div>
    <div class="card">
      <h2>ஆபத்தான பகுதி</h2>
      <p class="muted">வாடிக்கையாளரை நீக்கினால் அவரது எல்லா வருகை பதிவுகளும் புகைப்படமும் நிரந்தரமாக அழியும்.</p>
      <form method="post" onsubmit="return confirm('இந்த வாடிக்கையாளரையும் அவரது எல்லா பதிவுகளையும் நிரந்தரமாக நீக்கலாமா?')">
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
        <input type="hidden" name="action" value="delete_customer">
        <input type="hidden" name="id" value="<?= $id ?>">
        <button class="danger">🗑 வாடிக்கையாளரை நீக்கு</button>
      </form>
    </div>
    <?php layout_end(); exit;
}

/* ---- வருகை திருத்து ---- */
if ($page === 'edit_visit') {
    $vid = (int)($_GET['id'] ?? 0);
    $st = $pdo->prepare('SELECT v.*, c.name FROM visits v JOIN customers c ON c.id = v.customer_id WHERE v.id = ?');
    $st->execute([$vid]);
    $v = $st->fetch();
    if (!$v) { flash('பதிவு கிடைக்கவில்லை.', 'err'); redirect('index.php'); }
    layout_start('வருகை திருத்து'); ?>
    <div class="card">
      <h2>வருகை திருத்து – <?= e($v['name']) ?></h2>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
        <input type="hidden" name="action" value="update_visit">
        <input type="hidden" name="visit_id" value="<?= $vid ?>">
        <div class="grid">
          <div><label>தேதி</label><input type="date" name="visit_date" required value="<?= e($v['visit_date']) ?>"></div>
          <div><label>வேளை</label>
            <select name="session">
              <?php foreach (SESSIONS as $k => $lbl): ?>
                <option value="<?= $k ?>" <?= $v['session'] === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div><label>எடை (kg)</label><input type="number" name="weight" step="0.1" min="1" max="400" value="<?= e((string)$v['weight']) ?>"></div>
          <div><label>உடல்நிலை</label>
            <select name="health">
              <?php foreach (HEALTH as $k => $lbl): ?>
                <option value="<?= $k ?>" <?= $v['health'] === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
              <?php endforeach; ?>
            </select></div>
        </div>
        <?php metric_fields($v); ?>
        <label>குறிப்பு</label>
        <input type="text" name="notes" maxlength="300" value="<?= e($v['notes']) ?>">
        <p class="row"><button>சேமி</button> <a class="btn" style="background:var(--muted)" href="?page=view&id=<?= (int)$v['customer_id'] ?>">ரத்து</a></p>
      </form>
    </div>
    <?php layout_end(); exit;
}

/* ---- மாதாந்திர அறிக்கை ---- */
if ($page === 'report') {
    [$month, $first, $last] = month_range($_GET['month'] ?? null);
    $prev = date('Y-m', strtotime($first . ' -1 month'));
    $next = date('Y-m', strtotime($first . ' +1 month'));
    $names = ['ஜனவரி', 'பிப்ரவரி', 'மார்ச்', 'ஏப்ரல்', 'மே', 'ஜூன்', 'ஜூலை', 'ஆகஸ்ட்', 'செப்டம்பர்', 'அக்டோபர்', 'நவம்பர்', 'டிசம்பர்'];
    $title = $names[(int)substr($month, 5, 2) - 1] . ' ' . substr($month, 0, 4);

    $st = $pdo->prepare("SELECT COUNT(*) total, COUNT(DISTINCT customer_id) uniq, COUNT(DISTINCT visit_date) days,
        COALESCE(SUM(session='morning'),0) m, COALESCE(SUM(session='afternoon'),0) a, COALESCE(SUM(session='evening'),0) e
        FROM visits WHERE visit_date BETWEEN ? AND ?");
    $st->execute([$first, $last]);
    $s = $st->fetch();

    $st = $pdo->prepare('SELECT COUNT(*) FROM customers WHERE joined_date BETWEEN ? AND ?');
    $st->execute([$first, $last]);
    $newCust = (int)$st->fetchColumn();

    $st = $pdo->prepare("SELECT visit_date,
        COALESCE(SUM(session='morning'),0) m, COALESCE(SUM(session='afternoon'),0) a, COALESCE(SUM(session='evening'),0) e,
        COUNT(DISTINCT customer_id) u
        FROM visits WHERE visit_date BETWEEN ? AND ? GROUP BY visit_date ORDER BY visit_date");
    $st->execute([$first, $last]);
    $daily = $st->fetchAll();

    $rows = monthly_rows($pdo, $first, $last);
    $lost = $gained = $recovered = $improving = 0;
    foreach ($rows as $r) {
        if ($r['diff'] !== null && $r['diff'] < 0) $lost++;
        if ($r['diff'] !== null && $r['diff'] > 0) $gained++;
        if ($r['last_health'] === 'recovered') $recovered++;
        if ($r['last_health'] === 'improving') $improving++;
    }
    $avg = (int)$s['days'] > 0 ? round((int)$s['total'] / (int)$s['days'], 1) : 0;

    layout_start('மாதாந்திர அறிக்கை'); ?>
    <div class="card">
      <form method="get" class="row noprint">
        <input type="hidden" name="page" value="report">
        <a class="btn" href="?page=report&month=<?= $prev ?>">◀</a>
        <input type="month" name="month" value="<?= e($month) ?>" style="max-width:180px" onchange="this.form.submit()">
        <a class="btn" href="?page=report&month=<?= $next ?>">▶</a>
        <a class="btn" href="?export=summary&month=<?= $month ?>">⬇ சுருக்கம் (Excel)</a>
        <a class="btn" href="?export=visits&month=<?= $month ?>">⬇ வருகை விவரம் (Excel)</a>
        <a class="btn" href="?export=customers">⬇ எல்லா வாடிக்கையாளர்கள் (Excel)</a>
        <button type="button" onclick="window.print()">🖨 அச்சிடு</button>
      </form>
      <h2 style="margin-top:16px">அறிக்கை – <?= e($title) ?></h2>
      <div class="stats">
        <div class="stat"><b><?= (int)$s['total'] ?></b><span>மொத்த வருகைகள்</span></div>
        <div class="stat"><b><?= (int)$s['uniq'] ?></b><span>தனிப்பட்ட வாடிக்கையாளர்கள்</span></div>
        <div class="stat"><b><?= $newCust ?></b><span>புதிதாக சேர்ந்தவர்கள்</span></div>
        <div class="stat"><b><?= (int)$s['days'] ?></b><span>இயங்கிய நாட்கள்</span></div>
        <div class="stat"><b><?= $avg ?></b><span>நாளொன்றுக்கு சராசரி வருகை</span></div>
      </div>
      <br>
      <div class="stats">
        <div class="stat"><b><?= (int)$s['m'] ?></b><span>காலை</span></div>
        <div class="stat"><b><?= (int)$s['a'] ?></b><span>மதியம்</span></div>
        <div class="stat"><b><?= (int)$s['e'] ?></b><span>மாலை</span></div>
        <div class="stat"><b><?= $lost ?></b><span>எடை குறைந்தவர்கள்</span></div>
        <div class="stat"><b><?= $gained ?></b><span>எடை கூடியவர்கள்</span></div>
        <div class="stat"><b><?= $improving ?></b><span>முன்னேற்றம்</span></div>
        <div class="stat"><b><?= $recovered ?></b><span>குணமானவர்கள்</span></div>
      </div>
    </div>

    <div class="card">
      <h2>வாடிக்கையாளர் வாரியான சுருக்கம்</h2>
      <div class="scroll"><table>
        <tr><th>பெயர்</th><th>வருகைகள்</th><th>வந்த நாட்கள்</th><th>மாத தொடக்க எடை</th><th>மாத இறுதி எடை</th><th>மாற்றம்</th><th>உடல்நிலை</th></tr>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><a href="?page=view&id=<?= (int)$r['id'] ?>"><?= e($r['name']) ?></a></td>
          <td><?= (int)$r['visit_count'] ?></td>
          <td><?= (int)$r['days'] ?></td>
          <td><?= $r['base_w'] !== null ? e((string)$r['base_w']) : '-' ?></td>
          <td><?= $r['last_w'] !== null ? e((string)$r['last_w']) : '-' ?></td>
          <td><?= change_html($r['base_w'], $r['last_w']) ?></td>
          <td><?= health_badge($r['last_health']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="7" class="muted">இந்த மாதம் வருகை பதிவு இல்லை.</td></tr><?php endif; ?>
      </table></div>
    </div>

    <div class="card">
      <h2>நாள் வாரியான வருகை</h2>
      <div class="scroll"><table>
        <tr><th>தேதி</th><th>காலை</th><th>மதியம்</th><th>மாலை</th><th>தனிப்பட்ட நபர்கள்</th></tr>
        <?php foreach ($daily as $d): ?>
        <tr>
          <td><a href="index.php?date=<?= e($d['visit_date']) ?>"><?= e($d['visit_date']) ?></a></td>
          <td><?= (int)$d['m'] ?></td><td><?= (int)$d['a'] ?></td><td><?= (int)$d['e'] ?></td><td><?= (int)$d['u'] ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$daily): ?><tr><td colspan="5" class="muted">தரவு இல்லை.</td></tr><?php endif; ?>
      </table></div>
    </div>
    <?php layout_end(); exit;
}

/* ---- புதிய வாடிக்கையாளர் ---- */
if ($page === 'add') {
    layout_start('புதிய வாடிக்கையாளர்'); ?>
    <div class="card">
      <h2>புதிய வாடிக்கையாளர்</h2>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
        <input type="hidden" name="action" value="add_customer">
        <div class="grid">
          <div><label>பெயர் *</label><input name="name" required maxlength="100"></div>
          <div><label>தொலைபேசி</label><input name="phone" maxlength="20" inputmode="tel"></div>
          <div><label>வயது</label><input type="number" name="age" min="0" max="120"></div>
          <div><label>பாலினம்</label>
            <select name="gender"><option value="">--</option><option>ஆண்</option><option>பெண்</option><option>மற்றவை</option></select></div>
          <div><label>உயரம் (cm)</label><input type="number" name="height_cm" step="0.1"></div>
          <div><label>தொடக்க எடை (kg)</label><input type="number" name="start_weight" step="0.1"></div>
        </div>
        <?php goal_fields(); ?>
        <label>புகைப்படம் (மொபைலில் கேமரா நேரடியாக திறக்கும்)</label>
        <input type="file" name="photo" accept="image/*" capture="environment">
        <p><button>சேமி</button></p>
      </form>
    </div>
    <?php layout_end(); exit;
}

/* ---- வாடிக்கையாளர் பட்டியல் ---- */
if ($page === 'customers') {
    $q = '%' . trim((string)($_GET['q'] ?? '')) . '%';
    $st = $pdo->prepare("SELECT c.*,
        (SELECT weight FROM visits WHERE customer_id=c.id AND weight IS NOT NULL ORDER BY visit_date DESC, id DESC LIMIT 1) AS last_weight,
        (SELECT health FROM visits WHERE customer_id=c.id ORDER BY visit_date DESC, id DESC LIMIT 1) AS last_health,
        (SELECT COUNT(*) FROM visits WHERE customer_id=c.id) AS visit_count
        FROM customers c WHERE c.name LIKE :q OR c.phone LIKE :q ORDER BY c.name");
    $st->execute([':q' => $q]);
    $rows = $st->fetchAll();
    layout_start('வாடிக்கையாளர்கள்'); ?>
    <div class="card">
      <div class="row"><h2 style="margin:0">வாடிக்கையாளர்கள் (<?= count($rows) ?>)</h2>
        <a class="btn" href="?export=customers">⬇ Excel</a></div><br>
      <form method="get" class="row">
        <input type="hidden" name="page" value="customers">
        <input name="q" placeholder="பெயர் / தொலைபேசி தேடு" value="<?= e($_GET['q'] ?? '') ?>" style="max-width:300px">
        <button>தேடு</button>
      </form>
      <div class="scroll"><table>
        <tr><th></th><th>பெயர்</th><th>தொடக்க எடை</th><th>தற்போதைய எடை</th><th>மாற்றம்</th><th>உடல்நிலை</th><th>வருகைகள்</th></tr>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><?php if ($r['photo']): ?><img class="thumb" src="uploads/<?= e($r['photo']) ?>" alt=""><?php else: ?><div class="thumb"></div><?php endif; ?></td>
          <td><a href="?page=view&id=<?= $r['id'] ?>"><?= e($r['name']) ?></a><br><span class="muted"><?= e($r['phone']) ?></span></td>
          <td><?= $r['start_weight'] !== null ? e((string)$r['start_weight']) . ' kg' : '-' ?></td>
          <td><?= $r['last_weight'] !== null ? e((string)$r['last_weight']) . ' kg' : '-' ?></td>
          <td><?= change_html($r['start_weight'] !== null ? (float)$r['start_weight'] : null, $r['last_weight'] !== null ? (float)$r['last_weight'] : null) ?></td>
          <td><?= health_badge($r['last_health']) ?></td>
          <td><?= (int)$r['visit_count'] ?></td>
        </tr>
        <?php endforeach; ?>
      </table></div>
    </div>
    <?php layout_end(); exit;
}

/* ---- வாடிக்கையாளர் விவரம் ---- */
if ($page === 'view') {
    $id = (int)($_GET['id'] ?? 0);
    $st = $pdo->prepare('SELECT * FROM customers WHERE id = ?');
    $st->execute([$id]);
    $c = $st->fetch();
    if (!$c) { flash('வாடிக்கையாளர் கிடைக்கவில்லை.', 'err'); redirect('index.php?page=customers'); }

    $st = $pdo->prepare('SELECT * FROM visits WHERE customer_id = ? ORDER BY visit_date DESC, id DESC');
    $st->execute([$id]);
    $visits = $st->fetchAll();

    $weights = array_values(array_filter(array_reverse($visits), fn($v) => $v['weight'] !== null));
    $latestW = $weights ? (float)end($weights)['weight'] : null;
    $startW  = $c['start_weight'] !== null ? (float)$c['start_weight'] : ($weights ? (float)$weights[0]['weight'] : null);
    $bmi = ($latestW && $c['height_cm']) ? round($latestW / pow($c['height_cm'] / 100, 2), 1) : null;
    $lastHealth = $visits[0]['health'] ?? null;

    layout_start($c['name']); ?>
    <div class="card row">
      <?php if ($c['photo']): ?><img class="big" src="uploads/<?= e($c['photo']) ?>" alt=""><?php else: ?><div class="big"></div><?php endif; ?>
      <div>
        <h2 style="font-size:22px"><?= e($c['name']) ?></h2>
        <div class="muted">
          <?= $c['age'] ? e((string)(int)$c['age']) . ' வயது · ' : '' ?><?= e($c['gender']) ?>
          <?= $c['phone'] ? ' · 📞 ' . e($c['phone']) : '' ?><br>
          சேர்ந்த தேதி: <?= e($c['joined_date']) ?><br>
          நோய்/நோக்கம்: <?= e($c['condition_name'] ?: '-') ?>
        </div>
        <p><?= health_badge($lastHealth) ?></p>
        <a class="btn" href="?page=edit&id=<?= $id ?>">✎ விவரம் திருத்து</a>
      </div>
    </div>

    <div class="card">
      <div class="stats">
        <div class="stat"><b><?= $startW !== null ? e((string)$startW) : '-' ?></b><span>தொடக்க எடை (kg)</span></div>
        <div class="stat"><b><?= $latestW !== null ? e((string)$latestW) : '-' ?></b><span>தற்போதைய எடை (kg)</span></div>
        <div class="stat"><b style="font-size:18px"><?= change_html($startW, $latestW) ?></b><span>மொத்த மாற்றம்</span></div>
        <div class="stat"><b><?= $bmi ?? '-' ?></b><span>BMI</span></div>
        <div class="stat"><b><?= count($visits) ?></b><span>மொத்த வருகைகள்</span></div>
      </div>
    </div>

    <?php goals_display($c); ?>

    <div class="card">
      <h2>சமீபத்திய உடல் அமைப்பு அளவீடுகள்</h2>
      <div class="stats">
        <?php foreach (METRICS as $k => $label):
            $vals = array_values(array_filter(array_column($visits, $k), fn($x) => $x !== null));
            $cur = $vals ? (float)$vals[0] : null;           // $visits புதியது முதல் வரிசையில்
            $firstVal = $vals ? (float)end($vals) : null;
            $dm = ($cur !== null && count($vals) > 1) ? round($cur - $firstVal, 1) : null; ?>
          <div class="stat">
            <b><?= $cur !== null ? e((string)$cur) : '-' ?></b>
            <span><?= e($label) ?></span>
            <?php if ($dm !== null && $dm != 0): ?>
              <br><span class="<?= $dm > 0 ? 'up' : 'down' ?>"><?= $dm > 0 ? '▲ +' : '▼ ' ?><?= $dm ?></span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="muted" style="margin:10px 0 0;font-size:13px">▲▼ = முதல் பதிவிலிருந்து மாற்றம்</p>
    </div>

    <?php if (count($weights) >= 2):
        $W = 600; $H = 180; $pad = 30;
        $vals = array_map(fn($v) => (float)$v['weight'], $weights);
        $min = min($vals); $max = max($vals); if ($max == $min) { $max += 1; $min -= 1; }
        $n = count($vals); $pts = [];
        foreach ($vals as $i => $v) {
            $x = $pad + ($W - 2 * $pad) * $i / ($n - 1);
            $y = $H - $pad - ($H - 2 * $pad) * ($v - $min) / ($max - $min);
            $pts[] = [round($x, 1), round($y, 1), $v];
        }
        $poly = implode(' ', array_map(fn($p) => $p[0] . ',' . $p[1], $pts)); ?>
    <div class="card">
      <h2>எடை மாற்ற வரைபடம்</h2>
      <div class="scroll">
      <svg viewBox="0 0 <?= $W ?> <?= $H ?>" width="100%" style="max-width:<?= $W ?>px">
        <polyline points="<?= $poly ?>" fill="none" stroke="#1f7a4d" stroke-width="2.5"/>
        <?php foreach ($pts as $i => $p): ?>
          <circle cx="<?= $p[0] ?>" cy="<?= $p[1] ?>" r="4" fill="#1f7a4d"/>
          <?php if ($n <= 12 || $i === 0 || $i === $n - 1): ?>
          <text x="<?= $p[0] ?>" y="<?= $p[1] - 9 ?>" font-size="11" text-anchor="middle" fill="currentColor"><?= $p[2] ?></text>
          <?php endif; ?>
        <?php endforeach; ?>
      </svg></div>
    </div>
    <?php endif; ?>

    <div class="card">
      <h2>வருகை பதிவு</h2>
      <?php checkin_form($pdo, $id, 'index.php?page=view&id=' . $id); ?>
    </div>

    <div class="card">
      <h2>வருகை வரலாறு</h2>
      <div class="scroll"><table>
        <tr><th>தேதி</th><th>வேளை</th><th>எடை</th><th>உடல்நிலை</th><th>BMI</th><th>FAT %</th><th>BMR</th><th>Visc. Fat</th><th>Sub. Fat</th><th>Meta Age</th><th>குறிப்பு</th><th></th></tr>
        <?php foreach ($visits as $v): ?>
        <tr>
          <td><?= e($v['visit_date']) ?></td>
          <td><?= e(SESSIONS[$v['session']]) ?></td>
          <td><?= $v['weight'] !== null ? e((string)$v['weight']) . ' kg' : '-' ?></td>
          <td><?= health_badge($v['health']) ?></td>
          <?php foreach (array_keys(METRICS) as $k): ?>
          <td><?= $v[$k] !== null ? e((string)$v[$k]) : '-' ?></td>
          <?php endforeach; ?>
          <td><?= e($v['notes']) ?></td>
          <td>
            <a href="?page=edit_visit&id=<?= (int)$v['id'] ?>" title="திருத்து">✎</a>
            <form method="post" style="display:inline" onsubmit="return confirm('நீக்கலாமா?')">
              <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
              <input type="hidden" name="action" value="delete_visit">
              <input type="hidden" name="visit_id" value="<?= $v['id'] ?>">
              <input type="hidden" name="back" value="index.php?page=view&id=<?= $id ?>">
              <button style="background:var(--err);padding:4px 10px">✕</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </table></div>
    </div>
    <?php layout_end(); exit;
}

/* ---- முகப்பு (இன்றைய வருகை) ---- */
$date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['date'] ?? '')) ? $_GET['date'] : date('Y-m-d');

$st = $pdo->prepare('SELECT session, COUNT(*) n FROM visits WHERE visit_date = ? GROUP BY session');
$st->execute([$date]);
$counts = array_column($st->fetchAll(), 'n', 'session');

$st = $pdo->prepare('SELECT COUNT(DISTINCT customer_id) FROM visits WHERE visit_date = ?');
$st->execute([$date]);
$unique = (int)$st->fetchColumn();

$st = $pdo->prepare('SELECT v.*, c.name, c.photo, c.start_weight,
    (SELECT weight FROM visits p WHERE p.customer_id=v.customer_id AND p.weight IS NOT NULL AND (p.visit_date < v.visit_date OR (p.visit_date = v.visit_date AND p.id < v.id)) ORDER BY p.visit_date DESC, p.id DESC LIMIT 1) AS prev_weight
    FROM visits v JOIN customers c ON c.id = v.customer_id
    WHERE v.visit_date = ?
    ORDER BY CASE v.session WHEN "morning" THEN 1 WHEN "afternoon" THEN 2 ELSE 3 END, v.id');
$st->execute([$date]);
$today = $st->fetchAll();

layout_start('முகப்பு'); ?>
<div class="card">
  <form method="get" class="row">
    <h2 style="margin:0">வருகை – <?= e($date) ?></h2>
    <input type="date" name="date" value="<?= e($date) ?>" style="max-width:170px" onchange="this.form.submit()">
  </form>
  <br>
  <div class="stats">
    <div class="stat"><b><?= $unique ?></b><span>மொத்த வாடிக்கையாளர்கள்</span></div>
    <?php foreach (SESSIONS as $k => $v): ?>
      <div class="stat"><b><?= (int)($counts[$k] ?? 0) ?></b><span><?= e($v) ?></span></div>
    <?php endforeach; ?>
  </div>
</div>

<div class="card">
  <h2>விரைவு வருகை பதிவு</h2>
  <?php checkin_form($pdo, null, 'index.php?date=' . $date); ?>
</div>

<div class="card">
  <h2>இன்றைய பட்டியல்</h2>
  <div class="scroll"><table>
    <tr><th></th><th>பெயர்</th><th>வேளை</th><th>எடை</th><th>முந்தைய எடையிலிருந்து</th><th>உடல்நிலை</th></tr>
    <?php foreach ($today as $v): ?>
    <tr>
      <td><?php if ($v['photo']): ?><img class="thumb" src="uploads/<?= e($v['photo']) ?>" alt=""><?php else: ?><div class="thumb"></div><?php endif; ?></td>
      <td><a href="?page=view&id=<?= $v['customer_id'] ?>"><?= e($v['name']) ?></a></td>
      <td><?= e(SESSIONS[$v['session']]) ?></td>
      <td><?= $v['weight'] !== null ? e((string)$v['weight']) . ' kg' : '-' ?></td>
      <td><?php
          $base = $v['prev_weight'] !== null ? (float)$v['prev_weight'] : ($v['start_weight'] !== null ? (float)$v['start_weight'] : null);
          echo change_html($base, $v['weight'] !== null ? (float)$v['weight'] : null); ?></td>
      <td><?= health_badge($v['health']) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$today): ?><tr><td colspan="6" class="muted">இன்னும் வருகை பதிவு இல்லை.</td></tr><?php endif; ?>
  </table></div>
</div>
<?php layout_end();

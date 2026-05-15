<?php
// =====================================================
// دروست کراوە لەلایەن: بەهمەن ئایتی
// https://github.com/bahman-it
// پەڕەی سەرەکی - لۆگین و تۆمارکردن
// =====================================================

require_once __DIR__ . '/includes/auth.php';
requireGuest(); // ئەگەر چووە ژوورەوە بیبەستەرەوە

$error   = '';
$success = '';
$mode    = $_GET['mode'] ?? 'login'; // login | register

// کارپێکردنی فۆرمەکە
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // پشکنینی CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'داواکارییەکە پەسەندکراو نییە. دووبارە هەوڵ بدەوە.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'login') {
            // --- لۆگین ---
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = 'تکایە هەموو خانەکان پڕ بکەوە';
            } else {
                $result = loginUser($username, $password);
                if ($result['success']) {
                    header('Location: dashboard.php');
                    exit;
                }
                $error = $result['message'];
            }

        } elseif ($action === 'register') {
            // --- تۆمارکردن ---
            $fullName  = trim($_POST['full_name'] ?? '');
            $username  = trim($_POST['username'] ?? '');
            $email     = trim($_POST['email'] ?? '');
            $password  = $_POST['password'] ?? '';
            $password2 = $_POST['password2'] ?? '';

            if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
                $error = 'تکایە هەموو خانەکان پڕ بکەوە';
            } elseif ($password !== $password2) {
                $error = 'پاسۆردەکان یەکسان نین';
            } elseif (strlen($password) < 6) {
                $error = 'پاسۆرد دەبێت لانیکەم ٦ پیت بێت';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'ئیمەیڵەکە دروست نییە';
            } else {
                $result = registerUser($username, $email, $password, $fullName);
                if ($result['success']) {
                    $success = $result['message'] . ' — ئێستا دەتوانی بچیتە ژوورەوە';
                    $mode    = 'login';
                } else {
                    $error = $result['message'];
                }
            }
        }
    }
}

$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BAHMAN_IT // ACCESS TERMINAL</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Orbitron:wght@400;700;900&family=Noto+Kufi+Arabic:wght@300;400;700&display=swap" rel="stylesheet">
<style>
/* =====================================================
   دروست کراوە لەلایەن: بەهمەن ئایتی
   https://github.com/bahman-it
   ===================================================== */

:root {
    --green:      #00ff41;
    --green-dim:  #00aa2a;
    --green-glow: rgba(0,255,65,0.15);
    --red:        #ff2244;
    --amber:      #ffaa00;
    --bg:         #000a00;
    --bg2:        #010d01;
    --panel:      rgba(0,20,0,0.92);
    --border:     rgba(0,255,65,0.25);
    --font-mono:  'Share Tech Mono', monospace;
    --font-kurd:  'Noto Kufi Arabic', sans-serif;
    --font-title: 'Orbitron', monospace;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html, body {
    height: 100%;
    background: var(--bg);
    color: var(--green);
    font-family: var(--font-kurd);
    overflow: hidden;
}

/* --- Canvas باکگڕاوند --- */
#matrix-canvas {
    position: fixed;
    inset: 0;
    z-index: 0;
    opacity: 0.18;
}

/* --- شاشەی سکانکردن --- */
.scanlines {
    position: fixed;
    inset: 0;
    z-index: 1;
    background: repeating-linear-gradient(
        to bottom,
        transparent 0px,
        transparent 3px,
        rgba(0,0,0,0.12) 3px,
        rgba(0,0,0,0.12) 4px
    );
    pointer-events: none;
}

/* --- ناوچەی ناوەڕاست --- */
.auth-wrapper {
    position: relative;
    z-index: 10;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

/* --- پانێلی ناساندن --- */
.auth-panel {
    width: 100%;
    max-width: 440px;
    background: var(--panel);
    border: 1px solid var(--border);
    box-shadow:
        0 0 40px rgba(0,255,65,0.08),
        inset 0 0 60px rgba(0,255,65,0.02);
    animation: panel-in 0.6s cubic-bezier(0.23,1,0.32,1) both;
}

@keyframes panel-in {
    from { opacity: 0; transform: translateY(30px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* --- هێڵی سەرەوەی پانێل --- */
.panel-header {
    border-bottom: 1px solid var(--border);
    padding: 1.2rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.panel-dots span {
    display: inline-block;
    width: 10px; height: 10px;
    border-radius: 50%;
}
.panel-dots span:nth-child(1) { background: #ff5f57; }
.panel-dots span:nth-child(2) { background: #ffbd2e; }
.panel-dots span:nth-child(3) { background: #28c940; }

.panel-title {
    font-family: var(--font-mono);
    font-size: 0.7rem;
    color: var(--green-dim);
    letter-spacing: 0.1em;
    margin-right: auto;
}

/* --- ناوی سایت --- */
.site-brand {
    text-align: center;
    padding: 2rem 1.5rem 1rem;
}

.brand-logo {
    font-family: var(--font-title);
    font-size: 1.6rem;
    font-weight: 900;
    letter-spacing: 0.2em;
    color: var(--green);
    text-shadow: 0 0 20px rgba(0,255,65,0.6);
    display: block;
}

.brand-sub {
    font-family: var(--font-mono);
    font-size: 0.65rem;
    color: var(--green-dim);
    letter-spacing: 0.15em;
    margin-top: 0.3rem;
    display: block;
}

/* --- تابەکان --- */
.tab-bar {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border-bottom: 1px solid var(--border);
    margin: 1rem 1.5rem 0;
}

.tab-btn {
    background: none;
    border: none;
    padding: 0.75rem;
    font-family: var(--font-kurd);
    font-size: 0.85rem;
    color: var(--green-dim);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.25s;
    letter-spacing: 0.05em;
}

.tab-btn.active {
    color: var(--green);
    border-bottom-color: var(--green);
    text-shadow: 0 0 10px rgba(0,255,65,0.4);
}

.tab-btn:hover:not(.active) { color: var(--green); }

/* --- ناوچەی فۆرم --- */
.form-area {
    padding: 1.5rem;
}

/* --- کۆگای پەیامەکان --- */
.alert {
    padding: 0.7rem 1rem;
    border-right: 3px solid;
    font-family: var(--font-mono);
    font-size: 0.75rem;
    margin-bottom: 1.2rem;
    letter-spacing: 0.04em;
    animation: alert-in 0.3s ease;
}

@keyframes alert-in {
    from { opacity: 0; transform: translateX(-8px); }
    to   { opacity: 1; transform: translateX(0); }
}

.alert-error {
    color: var(--red);
    border-color: var(--red);
    background: rgba(255,34,68,0.07);
}

.alert-success {
    color: var(--green);
    border-color: var(--green);
    background: rgba(0,255,65,0.05);
}

/* --- گروپی خانەکان --- */
.field-group {
    margin-bottom: 1rem;
}

.field-label {
    display: block;
    font-size: 0.72rem;
    color: var(--green-dim);
    margin-bottom: 0.4rem;
    font-family: var(--font-mono);
    letter-spacing: 0.08em;
}

.field-wrap {
    position: relative;
}

.field-wrap .field-icon {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.9rem;
    color: var(--green-dim);
    pointer-events: none;
}

.field-input {
    width: 100%;
    background: rgba(0,20,0,0.8);
    border: 1px solid var(--border);
    color: var(--green);
    padding: 0.65rem 0.9rem 0.65rem 2.4rem;
    font-family: var(--font-mono);
    font-size: 0.85rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    letter-spacing: 0.04em;
    direction: ltr;
}

.field-input::placeholder { color: rgba(0,255,65,0.25); }

.field-input:focus {
    border-color: var(--green);
    box-shadow: 0 0 12px rgba(0,255,65,0.12);
}

/* --- دوگمەی ناردن --- */
.btn-submit {
    width: 100%;
    background: transparent;
    border: 1px solid var(--green);
    color: var(--green);
    padding: 0.8rem;
    font-family: var(--font-title);
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: all 0.3s;
    margin-top: 0.5rem;
    text-transform: uppercase;
}

.btn-submit::before {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--green);
    transform: translateX(101%);
    transition: transform 0.3s cubic-bezier(0.23,1,0.32,1);
    z-index: 0;
}

.btn-submit:hover::before { transform: translateX(0); }
.btn-submit:hover { color: #000; }
.btn-submit span { position: relative; z-index: 1; }

/* --- بەندی لینکەکان --- */
.form-footer {
    text-align: center;
    font-family: var(--font-mono);
    font-size: 0.68rem;
    color: var(--green-dim);
    margin-top: 1.2rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
}

.form-footer a {
    color: var(--green);
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 0.2s;
}

.form-footer a:hover { border-bottom-color: var(--green); }

/* --- کرێدیتەکان --- */
.credit-bar {
    text-align: center;
    padding: 0.7rem 1.5rem 1rem;
    font-family: var(--font-mono);
    font-size: 0.6rem;
    color: rgba(0,255,65,0.3);
    border-top: 1px solid rgba(0,255,65,0.08);
}

.credit-bar a {
    color: rgba(0,255,65,0.5);
    text-decoration: none;
}

.credit-bar a:hover { color: var(--green); }

/* --- کێرسەر --- */
.cursor-blink {
    display: inline-block;
    width: 8px; height: 1em;
    background: var(--green);
    margin-right: 4px;
    animation: blink 1s step-end infinite;
    vertical-align: text-bottom;
}

@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0} }

/* --- مۆبایل --- */
@media (max-width: 480px) {
    .auth-wrapper { align-items: flex-start; padding-top: 2rem; overflow-y: auto; }
    body { overflow: auto; }
    html { overflow: auto; }
}
</style>
</head>
<body>

<!-- باکگڕاوندی ماتریکس -->
<canvas id="matrix-canvas"></canvas>
<div class="scanlines"></div>

<div class="auth-wrapper">
    <div class="auth-panel">

        <!-- سەردێڕی پانێل -->
        <div class="panel-header">
            <div class="panel-dots">
                <span></span><span></span><span></span>
            </div>
            <span class="panel-title">SECURE_TERMINAL v2.4.1 // ENCRYPTED</span>
        </div>

        <!-- ناوی سایت -->
        <div class="site-brand">
            <span class="brand-logo"><span class="cursor-blink"></span>BAHMAN_IT</span>
            <span class="brand-sub">// AUTHORIZED ACCESS ONLY //</span>
        </div>

        <!-- تابەکان -->
        <div class="tab-bar">
            <button class="tab-btn <?= $mode === 'login' ? 'active' : '' ?>"
                    onclick="switchMode('login')">
                ⌥ چوونەژوورەوە
            </button>
            <button class="tab-btn <?= $mode === 'register' ? 'active' : '' ?>"
                    onclick="switchMode('register')">
                ⊕ تۆمارکردن
            </button>
        </div>

        <!-- فۆرمەکان -->
        <div class="form-area">

            <?php if ($error): ?>
            <div class="alert alert-error">⚠ ERROR: <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- فۆرمی لۆگین -->
            <form id="form-login" method="POST" style="<?= $mode !== 'login' ? 'display:none' : '' ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="action" value="login">

                <div class="field-group">
                    <label class="field-label">► بەکارهێنەر / ئیمەیڵ</label>
                    <div class="field-wrap">
                        <span class="field-icon">⊛</span>
                        <input type="text" name="username" class="field-input"
                               placeholder="username or email"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               autocomplete="username" required>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">► پاسۆرد</label>
                    <div class="field-wrap">
                        <span class="field-icon">⚿</span>
                        <input type="password" name="password" class="field-input"
                               placeholder="••••••••"
                               autocomplete="current-password" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <span>[ AUTHENTICATE ]</span>
                </button>

                <div class="form-footer">
                    هەژمارت نییە؟
                    <a href="#" onclick="switchMode('register'); return false;">تۆمار بکە</a>
                </div>
            </form>

            <!-- فۆرمی تۆمارکردن -->
            <form id="form-register" method="POST" style="<?= $mode !== 'register' ? 'display:none' : '' ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="action" value="register">

                <div class="field-group">
                    <label class="field-label">► ناوی تەواو</label>
                    <div class="field-wrap">
                        <span class="field-icon">◈</span>
                        <input type="text" name="full_name" class="field-input"
                               placeholder="Bahman IT"
                               value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">► ناوی بەکارهێنەر</label>
                    <div class="field-wrap">
                        <span class="field-icon">⊛</span>
                        <input type="text" name="username" class="field-input"
                               placeholder="bahman_it"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               autocomplete="username" required>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">► ئیمەیڵ</label>
                    <div class="field-wrap">
                        <span class="field-icon">@</span>
                        <input type="email" name="email" class="field-input"
                               placeholder="you@domain.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               autocomplete="email" required>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">► پاسۆرد</label>
                    <div class="field-wrap">
                        <span class="field-icon">⚿</span>
                        <input type="password" name="password" class="field-input"
                               placeholder="Min 6 chars" autocomplete="new-password" required>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label">► دووبارەی پاسۆرد</label>
                    <div class="field-wrap">
                        <span class="field-icon">⚿</span>
                        <input type="password" name="password2" class="field-input"
                               placeholder="Confirm password" autocomplete="new-password" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <span>[ CREATE ACCOUNT ]</span>
                </button>

                <div class="form-footer">
                    هەژمارەت هەیە؟
                    <a href="#" onclick="switchMode('login'); return false;">بچۆ ژوورەوە</a>
                </div>
            </form>
        </div>

        <!-- کرێدیت -->
        <div class="credit-bar">
            // دروست کراوە لەلایەن:
            <a href="https://github.com/bahman-it" target="_blank">بەهمەن ئایتی</a>
            // github.com/bahman-it //
        </div>
    </div>
</div>

<script>
// =====================================================
// دروست کراوە لەلایەن: بەهمەن ئایتی
// https://github.com/bahman-it
// JavaScript — ئانیمەیشنی ماتریکس و گۆڕینی تاب
// =====================================================

// --- ئانیمەیشنی ماتریکس ---
(function() {
    const canvas = document.getElementById('matrix-canvas');
    const ctx    = canvas.getContext('2d');

    // کاریکتەرەکانی ماتریکس (کۆدی باینەری و لاتین)
    const chars  = '01アイウエオカキクケコ01アabc01xyz';
    const fontSize = 13;
    let cols, drops;

    function resize() {
        canvas.width  = window.innerWidth;
        canvas.height = window.innerHeight;
        cols  = Math.floor(canvas.width / fontSize);
        drops = Array(cols).fill(1);
    }

    function draw() {
        ctx.fillStyle = 'rgba(0,10,0,0.05)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.fillStyle = '#00ff41';
        ctx.font      = fontSize + 'px Share Tech Mono';

        for (let i = 0; i < drops.length; i++) {
            const text = chars[Math.floor(Math.random() * chars.length)];
            ctx.fillStyle = Math.random() > 0.95
                ? '#ffffff'           // کاریکتەری ئەسپی
                : (Math.random() > 0.7 ? '#00ff41' : '#006620');
            ctx.fillText(text, i * fontSize, drops[i] * fontSize);

            if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                drops[i] = 0;
            }
            drops[i]++;
        }
    }

    resize();
    window.addEventListener('resize', resize);
    setInterval(draw, 45);
})();

// --- گۆڕینی تابەکان ---
function switchMode(mode) {
    const loginForm    = document.getElementById('form-login');
    const registerForm = document.getElementById('form-register');
    const tabs         = document.querySelectorAll('.tab-btn');

    loginForm.style.display    = mode === 'login'    ? '' : 'none';
    registerForm.style.display = mode === 'register' ? '' : 'none';

    tabs.forEach((t, i) => {
        t.classList.toggle('active', (mode === 'login') ? i === 0 : i === 1);
    });
}
</script>
</body>
</html>

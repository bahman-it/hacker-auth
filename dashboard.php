<?php
// =====================================================
// دروست کراوە لەلایەن: بەهمەن ئایتی
// https://github.com/bahman-it
// پەڕەی داشبۆرد — دەستپێک بەسەر لۆگین کردن
// =====================================================

require_once __DIR__ . '/includes/auth.php';
requireLogin(); // تەنها کەسی چووە ژوورەوە دەبینێتەوە

$db       = getDB();
$userId   = $_SESSION['user_id'];

// زانیاری بەکارهێنەر لە داتابەیس
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// ئامارەکان
$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalLogs  = $db->query("SELECT COUNT(*) FROM login_logs")->fetchColumn();
$todayLogins = $db->query("SELECT COUNT(*) FROM login_logs WHERE DATE(logged_at) = CURDATE() AND status='success'")->fetchColumn();
$failedLogins = $db->query("SELECT COUNT(*) FROM login_logs WHERE status='failed'")->fetchColumn();

// دوایین چوونەژوورەوەکان
$lastLogins = $db->prepare("
    SELECT l.*, u.username, u.full_name
    FROM login_logs l
    JOIN users u ON l.user_id = u.id
    ORDER BY l.logged_at DESC
    LIMIT 8
");
$lastLogins->execute();
$logsData = $lastLogins->fetchAll();

// دەرچوون
if (isset($_GET['logout'])) {
    logoutUser();
}
?>
<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BAHMAN_IT // DASHBOARD</title>
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
    --green-dark: #003a0f;
    --amber:      #ffaa00;
    --red:        #ff2244;
    --blue:       #00cfff;
    --bg:         #000a00;
    --sidebar-w:  260px;
    --panel:      rgba(0,18,0,0.95);
    --border:     rgba(0,255,65,0.18);
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

/* --- باکگڕاوند --- */
#matrix-bg {
    position: fixed;
    inset: 0;
    z-index: 0;
    opacity: 0.10;
}

.scanlines {
    position: fixed;
    inset: 0;
    z-index: 1;
    background: repeating-linear-gradient(
        to bottom,
        transparent 0,
        transparent 3px,
        rgba(0,0,0,0.1) 3px,
        rgba(0,0,0,0.1) 4px
    );
    pointer-events: none;
}

/* --- ئامێری گشتی --- */
.app-shell {
    position: relative;
    z-index: 10;
    display: grid;
    grid-template-columns: var(--sidebar-w) 1fr;
    grid-template-rows: auto 1fr;
    height: 100vh;
    overflow: hidden;
}

/* ===================== TOPBAR ===================== */
.topbar {
    grid-column: 1 / -1;
    background: rgba(0,12,0,0.98);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    padding: 0 1.5rem;
    height: 52px;
    gap: 1rem;
}

.topbar-brand {
    font-family: var(--font-title);
    font-size: 1rem;
    font-weight: 900;
    letter-spacing: 0.2em;
    text-shadow: 0 0 16px rgba(0,255,65,0.5);
    white-space: nowrap;
}

.topbar-status {
    font-family: var(--font-mono);
    font-size: 0.6rem;
    color: var(--green-dim);
    border: 1px solid rgba(0,255,65,0.2);
    padding: 0.2rem 0.6rem;
    animation: status-pulse 2s ease-in-out infinite;
}

@keyframes status-pulse {
    0%,100% { border-color: rgba(0,255,65,0.2); }
    50%      { border-color: rgba(0,255,65,0.5); }
}

.topbar-time {
    font-family: var(--font-mono);
    font-size: 0.72rem;
    color: var(--amber);
    margin-right: auto;
}

.topbar-user {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-family: var(--font-mono);
    font-size: 0.72rem;
}

.user-avatar {
    width: 30px; height: 30px;
    border: 1px solid var(--green-dim);
    background: var(--green-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-family: var(--font-title);
    font-weight: 700;
}

.btn-logout {
    background: transparent;
    border: 1px solid rgba(255,34,68,0.4);
    color: var(--red);
    padding: 0.3rem 0.8rem;
    font-family: var(--font-mono);
    font-size: 0.65rem;
    cursor: pointer;
    letter-spacing: 0.08em;
    transition: all 0.2s;
}

.btn-logout:hover {
    background: var(--red);
    color: #000;
}

/* ===================== سایدبار ===================== */
.sidebar {
    background: rgba(0,10,0,0.97);
    border-left: 1px solid var(--border);
    padding: 1.5rem 0;
    overflow-y: auto;
    animation: slide-in 0.4s ease both;
}

@keyframes slide-in {
    from { transform: translateX(-20px); opacity: 0; }
    to   { transform: translateX(0);     opacity: 1; }
}

.nav-section-title {
    font-family: var(--font-mono);
    font-size: 0.55rem;
    color: rgba(0,255,65,0.3);
    letter-spacing: 0.18em;
    padding: 0 1.2rem;
    margin-bottom: 0.4rem;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.65rem 1.2rem;
    font-size: 0.82rem;
    color: var(--green-dim);
    cursor: pointer;
    transition: all 0.2s;
    border-right: 2px solid transparent;
    text-decoration: none;
}

.nav-item:hover,
.nav-item.active {
    color: var(--green);
    border-right-color: var(--green);
    background: rgba(0,255,65,0.04);
}

.nav-item .nav-icon {
    font-family: var(--font-mono);
    font-size: 0.85rem;
    width: 20px;
    text-align: center;
}

.nav-divider {
    border: none;
    border-top: 1px solid var(--border);
    margin: 0.8rem 1.2rem;
}

/* كرێدیتی سایدبار */
.sidebar-credit {
    font-family: var(--font-mono);
    font-size: 0.55rem;
    color: rgba(0,255,65,0.25);
    padding: 1rem 1.2rem 0;
    border-top: 1px solid var(--border);
    margin-top: 1rem;
    line-height: 1.6;
}

.sidebar-credit a {
    color: rgba(0,255,65,0.45);
    text-decoration: none;
}

/* ===================== ناوەڕۆکی سەرەکی ===================== */
.main-content {
    overflow-y: auto;
    padding: 1.5rem;
    animation: fade-in 0.5s ease both 0.1s;
}

@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* --- سەردێڕ --- */
.page-header {
    margin-bottom: 1.5rem;
}

.page-title {
    font-family: var(--font-mono);
    font-size: 0.7rem;
    color: var(--green-dim);
    letter-spacing: 0.12em;
}

.page-heading {
    font-family: var(--font-kurd);
    font-size: 1.4rem;
    font-weight: 700;
    margin-top: 0.2rem;
    color: var(--green);
}

.page-heading span {
    color: var(--amber);
}

/* --- گریدی ئامارەکان --- */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: var(--panel);
    border: 1px solid var(--border);
    padding: 1.2rem;
    position: relative;
    overflow: hidden;
    transition: border-color 0.3s;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 40px; height: 2px;
    background: var(--accent, var(--green));
    box-shadow: 0 0 8px var(--accent, var(--green));
}

.stat-card:hover { border-color: rgba(0,255,65,0.4); }

.stat-icon {
    font-size: 1.4rem;
    margin-bottom: 0.5rem;
    display: block;
}

.stat-value {
    font-family: var(--font-title);
    font-size: 1.8rem;
    font-weight: 900;
    color: var(--accent, var(--green));
    text-shadow: 0 0 16px var(--accent, var(--green));
    line-height: 1;
}

.stat-label {
    font-family: var(--font-mono);
    font-size: 0.62rem;
    color: var(--green-dim);
    letter-spacing: 0.1em;
    margin-top: 0.3rem;
    display: block;
}

/* --- پانێلی ناوەڕاست --- */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1rem;
}

.panel-box {
    background: var(--panel);
    border: 1px solid var(--border);
}

.panel-box-head {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border);
    font-family: var(--font-mono);
    font-size: 0.68rem;
    color: var(--green-dim);
    letter-spacing: 0.12em;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* --- تەیبڵی لۆگەکان --- */
.log-table {
    width: 100%;
    border-collapse: collapse;
    font-family: var(--font-mono);
    font-size: 0.7rem;
}

.log-table th {
    padding: 0.6rem 0.8rem;
    text-align: right;
    color: rgba(0,255,65,0.4);
    font-weight: normal;
    letter-spacing: 0.08em;
    border-bottom: 1px solid var(--border);
}

.log-table td {
    padding: 0.55rem 0.8rem;
    border-bottom: 1px solid rgba(0,255,65,0.06);
    color: var(--green-dim);
    vertical-align: middle;
}

.log-table tr:hover td { background: rgba(0,255,65,0.03); }

.badge {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    font-size: 0.6rem;
    letter-spacing: 0.08em;
    font-family: var(--font-mono);
}

.badge-ok  { color: var(--green); border: 1px solid rgba(0,255,65,0.3); }
.badge-err { color: var(--red);   border: 1px solid rgba(255,34,68,0.3); }

/* --- ئامارەکانی بەکارهێنەر --- */
.user-info-list {
    padding: 1rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.55rem 0;
    border-bottom: 1px solid rgba(0,255,65,0.07);
    font-family: var(--font-mono);
    font-size: 0.7rem;
}

.info-key  { color: rgba(0,255,65,0.4); }
.info-val  { color: var(--green); }

/* --- تەرمینال --- */
.terminal-box {
    padding: 1rem;
    font-family: var(--font-mono);
    font-size: 0.68rem;
    color: var(--green-dim);
    line-height: 1.7;
    min-height: 150px;
}

.terminal-line { display: flex; gap: 0.5rem; }
.term-prompt   { color: rgba(0,255,65,0.4); white-space: nowrap; }
.term-cmd      { color: var(--green); }
.term-out      { color: var(--amber); margin-right: 1.4rem; }

/* --- مۆبایل --- */
@media (max-width: 900px) {
    .app-shell { grid-template-columns: 1fr; grid-template-rows: auto auto 1fr; }
    .sidebar   { display: none; }
    .stats-grid { grid-template-columns: repeat(2,1fr); }
    .content-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- باکگڕاوندی ماتریکس -->
<canvas id="matrix-bg"></canvas>
<div class="scanlines"></div>

<div class="app-shell">

    <!-- سەردێڕی سەرەوە -->
    <header class="topbar">
        <span class="topbar-brand">⬡ BAHMAN_IT</span>
        <span class="topbar-status">● SYSTEM ONLINE</span>
        <span class="topbar-time" id="clock">--:--:--</span>

        <div class="topbar-user">
            <div class="user-avatar">
                <?= strtoupper(substr($user['full_name'] ?: $user['username'], 0, 1)) ?>
            </div>
            <span><?= htmlspecialchars($user['username']) ?></span>
            <a href="?logout=1" class="btn-logout">[ EXIT ]</a>
        </div>
    </header>

    <!-- سایدبار -->
    <aside class="sidebar">
        <div class="nav-section-title">// MAIN MENU</div>

        <a href="#" class="nav-item active">
            <span class="nav-icon">⬡</span> داشبۆرد
        </a>
        <a href="#" class="nav-item">
            <span class="nav-icon">◈</span> بەکارهێنەران
        </a>
        <a href="#" class="nav-item">
            <span class="nav-icon">⊛</span> لۆگەکان
        </a>

        <hr class="nav-divider">

        <div class="nav-section-title">// SETTINGS</div>
        <a href="#" class="nav-item">
            <span class="nav-icon">⚙</span> ڕێکخستنەکان
        </a>
        <a href="#" class="nav-item">
            <span class="nav-icon">⚿</span> گۆڕینی پاسۆرد
        </a>

        <hr class="nav-divider">

        <a href="?logout=1" class="nav-item" style="--green-dim:var(--red);">
            <span class="nav-icon">⏻</span> دەرچوون
        </a>

        <!-- كرێدیت سایدبار -->
        <div class="sidebar-credit">
            // دروست کراوە لەلایەن:<br>
            <a href="https://github.com/bahman-it" target="_blank">بەهمەن ئایتی</a><br>
            github.com/bahman-it
        </div>
    </aside>

    <!-- ناوەڕۆکی سەرەکی -->
    <main class="main-content">

        <div class="page-header">
            <div class="page-title">// DASHBOARD > OVERVIEW</div>
            <h1 class="page-heading">
               بەخێربێی ، <span><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></span>
            </h1>
        </div>

        <!-- ئامارەکان -->
        <div class="stats-grid">
            <div class="stat-card" style="--accent: var(--green);">
                <span class="stat-icon">◈</span>
                <div class="stat-value"><?= number_format($totalUsers) ?></div>
                <span class="stat-label">TOTAL USERS</span>
            </div>
            <div class="stat-card" style="--accent: var(--blue);">
                <span class="stat-icon">⊛</span>
                <div class="stat-value" style="color:var(--blue);text-shadow:0 0 16px var(--blue);"><?= number_format($todayLogins) ?></div>
                <span class="stat-label">TODAY LOGINS</span>
            </div>
            <div class="stat-card" style="--accent: var(--amber);">
                <span class="stat-icon">⬡</span>
                <div class="stat-value" style="color:var(--amber);text-shadow:0 0 16px var(--amber);"><?= number_format($totalLogs) ?></div>
                <span class="stat-label">TOTAL EVENTS</span>
            </div>
            <div class="stat-card" style="--accent: var(--red);">
                <span class="stat-icon">⚠</span>
                <div class="stat-value" style="color:var(--red);text-shadow:0 0 16px var(--red);"><?= number_format($failedLogins) ?></div>
                <span class="stat-label">FAILED ATTEMPTS</span>
            </div>
        </div>

        <!-- گریدی ناوەڕاست -->
        <div class="content-grid">

            <!-- تەیبڵی لۆگەکان -->
            <div class="panel-box">
                <div class="panel-box-head">
                    <span>// RECENT_LOGIN_EVENTS</span>
                    <span style="color:var(--green);">● LIVE</span>
                </div>
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>بەکارهێنەر</th>
                            <th>IP</th>
                            <th>کات</th>
                            <th>بارودۆخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logsData as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['username']) ?></td>
                            <td><?= htmlspecialchars($log['ip_address'] ?? '—') ?></td>
                            <td><?= date('H:i d/m', strtotime($log['logged_at'])) ?></td>
                            <td>
                                <?php if ($log['status'] === 'success'): ?>
                                    <span class="badge badge-ok">OK</span>
                                <?php else: ?>
                                    <span class="badge badge-err">FAIL</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (empty($logsData)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center;color:rgba(0,255,65,0.3);padding:2rem;">
                                هیچ تۆمارێک نییە
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ئامارەکانی بەکارهێنەر + تەرمینال -->
            <div style="display:flex;flex-direction:column;gap:1rem;">

                <!-- زانیاری بەکارهێنەر -->
                <div class="panel-box">
                    <div class="panel-box-head">// USER_PROFILE</div>
                    <div class="user-info-list">
                        <div class="info-row">
                            <span class="info-key">ID</span>
                            <span class="info-val">#<?= $user['id'] ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">USERNAME</span>
                            <span class="info-val"><?= htmlspecialchars($user['username']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">EMAIL</span>
                            <span class="info-val"><?= htmlspecialchars($user['email']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">ROLE</span>
                            <span class="info-val" style="color:var(--amber);"><?= strtoupper($user['role']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">JOINED</span>
                            <span class="info-val"><?= date('Y/m/d', strtotime($user['created_at'])) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">LAST_LOGIN</span>
                            <span class="info-val"><?= $user['last_login'] ? date('H:i d/m/Y', strtotime($user['last_login'])) : 'N/A' ?></span>
                        </div>
                    </div>
                </div>

                <!-- تەرمینالی دەرکەوت -->
                <div class="panel-box">
                    <div class="panel-box-head">// SYSTEM_TERMINAL</div>
                    <div class="terminal-box" id="terminal">
                        <div class="terminal-line">
                            <span class="term-prompt">root@bahman-it:~$</span>
                            <span class="term-cmd">whoami</span>
                        </div>
                        <div class="term-out"><?= htmlspecialchars($user['username']) ?></div>
                        <div class="terminal-line">
                            <span class="term-prompt">root@bahman-it:~$</span>
                            <span class="term-cmd">uptime</span>
                        </div>
                        <div class="term-out" id="uptime-line">calculating...</div>
                        <div class="terminal-line">
                            <span class="term-prompt">root@bahman-it:~$</span>
                            <span class="term-cmd" id="blink-cmd">_</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
// =====================================================
// دروست کراوە لەلایەن: بەهمەن ئایتی
// https://github.com/bahman-it
// JavaScript — داشبۆرد
// =====================================================

// --- ئانیمەیشنی ماتریکس ---
(function() {
    const canvas = document.getElementById('matrix-bg');
    const ctx    = canvas.getContext('2d');
    const chars  = '01アイウエ01カキクケ01abcxyz░▒▓';
    const fs     = 12;
    let cols, drops;

    function resize() {
        canvas.width  = innerWidth;
        canvas.height = innerHeight;
        cols  = Math.floor(canvas.width / fs);
        drops = Array(cols).fill(1);
    }

    function draw() {
        ctx.fillStyle = 'rgba(0,10,0,0.055)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.font = fs + 'px Share Tech Mono';

        for (let i = 0; i < drops.length; i++) {
            ctx.fillStyle = Math.random() > 0.96 ? '#fff'
                : (Math.random() > 0.6 ? '#00ff41' : '#004410');
            ctx.fillText(chars[Math.floor(Math.random() * chars.length)],
                         i * fs, drops[i] * fs);
            if (drops[i] * fs > canvas.height && Math.random() > 0.975) drops[i] = 0;
            drops[i]++;
        }
    }

    resize();
    addEventListener('resize', resize);
    setInterval(draw, 50);
})();

// --- کاتژمێر ---
function updateClock() {
    const now  = new Date();
    const h    = String(now.getHours()).padStart(2,'0');
    const m    = String(now.getMinutes()).padStart(2,'0');
    const s    = String(now.getSeconds()).padStart(2,'0');
    document.getElementById('clock').textContent = h + ':' + m + ':' + s;
    document.getElementById('uptime-line').textContent =
        'system load: 0.0' + s.slice(-1) + ' // session: active';
}

setInterval(updateClock, 1000);
updateClock();

// --- کرسەری تەرمینال ---
const blinkEl = document.getElementById('blink-cmd');
let   blinkOn = true;
setInterval(() => {
    blinkOn = !blinkOn;
    blinkEl.style.opacity = blinkOn ? '1' : '0';
}, 500);
</script>
</body>
</html>

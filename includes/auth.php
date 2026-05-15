<?php
// =====================================================
// دروست کراوە لەلایەن: بەهمەن ئایتی
// https://github.com/bahman-it
// فایلی یارمەتیدەرەکانی ناساندن
// =====================================================

require_once __DIR__ . '/config.php';

session_start();

// پشکنینی چوونەژوورەوە
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// ئەگەر چووە ژوورەوە بیبەستەوە بۆ داشبۆرد
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

// ئەگەر چووە ژوورەوە بیبەستەوە بۆ داشبۆرد (لەکاتی چوونە پەڕەی لۆگین)
function requireGuest(): void {
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit;
    }
}

// تۆمارکردنی بەکارهێنەری نوێ
function registerUser(string $username, string $email, string $password, string $fullName): array {
    $db = getDB();

    // پشکنینی بەکارهێنەر یان ئیمەیڵ هەبوون
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'بەکارهێنەر یان ئیمەیڵ پێشتر تۆمارکراوە'];
    }

    // هاش کردنی پاسۆرد
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $email, $hashedPassword, $fullName]);

    return ['success' => true, 'message' => 'تۆمارکردن سەرکەوتوو بوو'];
}

// چوونەژوورەوە
function loginUser(string $username, string $password): array {
    $db = getDB();

    $stmt = $db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    if (!$user || !password_verify($password, $user['password'])) {
        // هەوڵی تۆمارکردن سەرکەوتوو نەبوو
        if ($user) {
            $log = $db->prepare("INSERT INTO login_logs (user_id, ip_address, user_agent, status) VALUES (?, ?, ?, 'failed')");
            $log->execute([$user['id'], $ip, $ua]);
        }
        return ['success' => false, 'message' => 'بەکارهێنەر یان پاسۆرد هەڵەیە'];
    }

    // دامەزراندنی سێشن
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['email']     = $user['email'];

    // نوێکردنەوەی کاتی دوایین چوونەژوورەوە
    $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

    // تۆمارکردنی چوونەژوورەوەی سەرکەوتوو
    $log = $db->prepare("INSERT INTO login_logs (user_id, ip_address, user_agent, status) VALUES (?, ?, ?, 'success')");
    $log->execute([$user['id'], $ip, $ua]);

    return ['success' => true, 'message' => 'چێژ لەو دیزاینە ببینە!'];
}

// دەرچوون
function logoutUser(): void {
    session_destroy();
    header('Location: index.php');
    exit;
}

// بەرپاکردنی CSRF token
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// پشکنینی CSRF token
function verifyCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

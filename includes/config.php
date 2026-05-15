<?php
// =====================================================
// دروست کراوە لەلایەن: بەهمەن ئایتی
// https://github.com/bahman-it
// فایلی پەیوەندیکردن بە داتابەیس
// =====================================================

define('DB_HOST',     'localhost');
define('DB_USER',     'root');        // بەکارهێنەری داتابەیسەکەت لێرە بنووسە
define('DB_PASSWORD', '');            // پاسۆردی داتابەیسەکەت لێرە بنووسە
define('DB_NAME',     'hacker_auth');
define('DB_CHARSET',  'utf8mb4');

// پەیوەندی PDO
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // لە کاری ڕاستەقینە هەڵە نیشان مەدە
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
    return $pdo;
}

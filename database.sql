-- =====================================================
-- دروست کراوە لەلایەن: بەهمەن ئایتی
-- https://github.com/bahman-it
-- داتابەیسی سیستەمی ناساندن و تۆمارکردن
-- =====================================================

CREATE DATABASE IF NOT EXISTS hacker_auth CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE hacker_auth;

-- تەیبڵی بەکارهێنەران
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    full_name   VARCHAR(100) DEFAULT NULL,
    role        ENUM('admin','user') DEFAULT 'user',
    avatar      VARCHAR(255) DEFAULT NULL,
    is_active   TINYINT(1)   DEFAULT 1,
    last_login  DATETIME     DEFAULT NULL,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- تەیبڵی نشانەکانی چوونەژوورەوە
CREATE TABLE IF NOT EXISTS login_logs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    ip_address VARCHAR(45)  DEFAULT NULL,
    user_agent TEXT         DEFAULT NULL,
    status     ENUM('success','failed') DEFAULT 'success',
    logged_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- دروست کردنی بەکارهێنەری پێشوەخت (admin)
-- پاسۆرد: Admin@1234
INSERT INTO users (username, email, password, full_name, role)
VALUES (
    'admin',
    'admin@hacker.local',
    '$2y$12$XK0VaYZL7e5tZwB3d8XqcOvD5E1mN9rP2kQ4sT6uW8jH1bF3gI7mK',
    'Bahman IT',
    'admin'
) ON DUPLICATE KEY UPDATE id = id;

# BAHMAN_IT — Auth System
> دروست کراوە لەلایەن: بەهمەن ئایتی  
> https://github.com/bahman-it

---

## 📁 پڕۆجەکت ستراکچەر

```
hacker-auth/
├── index.php              ← پەڕەی لۆگین و تۆمارکردن
├── dashboard.php          ← پەڕەی داشبۆرد
├── database.sql           ← داتابەیس و تەیبڵەکان
└── includes/
    ├── config.php         ← ڕێکخستنی پەیوەندی داتابەیس
    └── auth.php           ← فانکشنەکانی ناساندن
```

---
(https://github.com/bahman-it/hacker-auth/blob/main/Images/Dashboard.jpg)





## ⚡ چۆن کاری پێ بکەین

### ١. داتابەیس دروست بکە
```sql
mysql -u root -p < database.sql
```

### ٢. config.php ڕێکبخە
`includes/config.php` بکەوە و زانیاریەکانت لێرە بنووسە:
```php
define('DB_USER',     'root');     // بەکارهێنەری MySQL
define('DB_PASSWORD', '');         // پاسوۆردی MySQL
```

### ٣. فایلەکان بکە سەر Apache/Nginx
پڕۆجەکت بکە ناو `htdocs` یان `www` فۆڵدەرەکەت.

### ٤. بۆ ژوورەوە بچۆ
- **URL**: `http://localhost/hacker-auth/`
- **Admin Username**: `admin`
- **Admin Password**: `Admin@1234`

> ⚠️ پاسۆردی ئەدمینەکە دووبارە دروست بکە بۆ بەکارهێنانی راستەقینە!

---

## 🛡️ تایبەتمەندییەکان

| تایبەتمەندی | بارودۆخ |
|------------|--------|
| لۆگین / تۆمارکردن | ✅ |
| پاسۆردی هاش‌کراو BCrypt | ✅ |
| پاراستنی CSRF | ✅ |
| تۆمارکردنی لۆگین لۆگز | ✅ |
| داشبۆردی ئامارەکان | ✅ |
| دیزاینی هاکەر | ✅ |
| مۆبایل ریسپانسیڤ | ✅ |

---

## 🗄️ تەیبڵەکانی داتابەیس

### `users`
| ستون | جۆر | وەسف |
|------|-----|------|
| id | INT AI PK | ناسنامە |
| username | VARCHAR(50) UNIQUE | بەکارهێنەر |
| email | VARCHAR(100) UNIQUE | ئیمەیڵ |
| password | VARCHAR(255) | پاسۆرد هاش‌کراو |
| full_name | VARCHAR(100) | ناوی تەواو |
| role | ENUM(admin,user) | ئەرکی بەکارهێنەر |
| is_active | TINYINT | چالاک/ناچالاک |
| last_login | DATETIME | کاتی دوایین چوونەژوورەوە |
| created_at | DATETIME | کاتی دروست بوون |

### `login_logs`
| ستون | جۆر | وەسف |
|------|-----|------|
| id | INT AI PK | ناسنامە |
| user_id | INT FK | بەکارهێنەر |
| ip_address | VARCHAR(45) | ئایپی ئادرەس |
| user_agent | TEXT | براوزەر |
| status | ENUM(success,failed) | سەرکەوتوو/سەرکەوتوو نەبوو |
| logged_at | DATETIME | کاتی تۆمارکردن |

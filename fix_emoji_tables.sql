-- حذف الجداول القديمة وإعادة إنشائها بالبنية الصحيحة
DROP TABLE IF EXISTS emojis;
DROP TABLE IF EXISTS emoji_categories;

-- إنشاء جدول أقسام الإيموجي
CREATE TABLE emoji_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إنشاء جدول الإيموجي
CREATE TABLE emojis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES emoji_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إضافة أقسام افتراضية
INSERT INTO emoji_categories (name, display_order, is_active) VALUES
('وجوه مبتسمة', 1, 1),
('قلوب', 2, 1),
('احتفالات', 3, 1),
('زهور', 4, 1),
('إسلامي', 5, 1),
('أخرى', 6, 1);

-- Migration script for Home Page Redesign & Share Feature
-- Run this script to add the necessary database tables and columns

-- 1. Add preview_image_url and aspect_ratio to templates table
ALTER TABLE templates 
ADD COLUMN preview_image_url VARCHAR(255) AFTER image_path,
ADD COLUMN aspect_ratio VARCHAR(10) DEFAULT '4:5' AFTER preview_image_url;

-- 2. Create share_backgrounds table for themed backgrounds
CREATE TABLE IF NOT EXISTS share_backgrounds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ar VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    category VARCHAR(50) NOT NULL COMMENT 'wedding, condolence, birthday, eid, general',
    background_type VARCHAR(20) NOT NULL COMMENT 'pattern, gradient, image',
    background_value TEXT NOT NULL COMMENT 'CSS value or image URL',
    preview_image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create shared_cards table for viral sharing
CREATE TABLE IF NOT EXISTS shared_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unique_id VARCHAR(50) UNIQUE NOT NULL,
    template_id INT NOT NULL,
    user_id INT NULL,
    card_image_url VARCHAR(255) NOT NULL,
    dedication_text TEXT,
    sender_name VARCHAR(100),
    background_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    views INT DEFAULT 0,
    downloads INT DEFAULT 0,
    FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (background_id) REFERENCES share_backgrounds(id) ON DELETE SET NULL,
    INDEX idx_unique_id (unique_id),
    INDEX idx_created_at (created_at),
    INDEX idx_template_id (template_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Insert default share backgrounds
INSERT INTO share_backgrounds (name_ar, name_en, slug, category, background_type, background_value, display_order) VALUES
('قلوب رومانسية', 'Romantic Hearts', 'hearts', 'wedding', 'pattern', 'repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255,105,180,0.3) 35px, rgba(255,105,180,0.3) 70px), repeating-linear-gradient(-45deg, transparent, transparent 35px, rgba(255,182,193,0.3) 35px, rgba(255,182,193,0.3) 70px)', 1),
('بلالين احتفالية', 'Celebration Balloons', 'balloons', 'birthday', 'pattern', 'radial-gradient(circle at 20% 30%, rgba(255,99,71,0.2) 0%, transparent 50%), radial-gradient(circle at 80% 60%, rgba(30,144,255,0.2) 0%, transparent 50%), radial-gradient(circle at 50% 80%, rgba(255,215,0,0.2) 0%, transparent 50%)', 2),
('ورود هادئة', 'Peaceful Floral', 'floral', 'condolence', 'pattern', 'repeating-linear-gradient(90deg, transparent, transparent 50px, rgba(147,112,219,0.15) 50px, rgba(147,112,219,0.15) 100px)', 3),
('نجوم وهلال', 'Stars and Crescent', 'stars-crescent', 'eid', 'pattern', 'radial-gradient(circle at 20% 50%, rgba(255,215,0,0.2) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(255,215,0,0.15) 0%, transparent 50%)', 4),
('كونفيتي النجاح', 'Success Confetti', 'confetti', 'success', 'pattern', 'radial-gradient(circle at 10% 20%, rgba(255,215,0,0.2) 0%, transparent 30%), radial-gradient(circle at 90% 40%, rgba(255,99,71,0.2) 0%, transparent 30%), radial-gradient(circle at 50% 70%, rgba(30,144,255,0.2) 0%, transparent 30%)', 5),
('تدرج أنيق بنفسجي', 'Elegant Purple Gradient', 'gradient-purple', 'general', 'gradient', 'linear-gradient(135deg, rgba(147,51,234,0.1) 0%, rgba(79,70,229,0.1) 100%)', 6),
('تدرج وردي ناعم', 'Soft Pink Gradient', 'gradient-pink', 'general', 'gradient', 'linear-gradient(135deg, rgba(255,182,193,0.15) 0%, rgba(255,105,180,0.1) 100%)', 7),
('تدرج أزرق سماوي', 'Sky Blue Gradient', 'gradient-blue', 'general', 'gradient', 'linear-gradient(135deg, rgba(135,206,250,0.15) 0%, rgba(30,144,255,0.1) 100%)', 8);

-- Migration complete
-- You can now use the share feature with themed backgrounds!

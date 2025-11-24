-- Add dedication text and background fields to templates table
-- Note: preview_image_url and aspect_ratio are already added in migration_share_feature.sql
ALTER TABLE `templates` 
ADD COLUMN `default_dedication_text` TEXT NULL AFTER `aspect_ratio`,
ADD COLUMN `default_background_id` INT NULL AFTER `default_dedication_text`,
ADD FOREIGN KEY (`default_background_id`) REFERENCES `share_backgrounds`(`id`) ON DELETE SET NULL;

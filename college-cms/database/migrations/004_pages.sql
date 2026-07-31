-- College CMS — Dynamic Page Builder
-- Extends the pages table introduced in 003_menus.sql

USE `college_cms`;

-- Helper: add column if missing
-- content (HTML body)
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pages' AND COLUMN_NAME = 'content'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE `pages` ADD COLUMN `content` LONGTEXT NULL AFTER `slug`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- menu_id (optional attached menu item)
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pages' AND COLUMN_NAME = 'menu_id'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE `pages` ADD COLUMN `menu_id` INT UNSIGNED NULL DEFAULT NULL AFTER `content`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- SEO + media fields
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pages' AND COLUMN_NAME = 'meta_title'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE `pages`
     ADD COLUMN `meta_title` VARCHAR(200) NULL DEFAULT NULL AFTER `menu_id`,
     ADD COLUMN `meta_description` VARCHAR(500) NULL DEFAULT NULL AFTER `meta_title`,
     ADD COLUMN `meta_keywords` VARCHAR(500) NULL DEFAULT NULL AFTER `meta_description`,
     ADD COLUMN `featured_image` VARCHAR(255) NULL DEFAULT NULL AFTER `meta_keywords`,
     ADD COLUMN `pdf_attachment` VARCHAR(255) NULL DEFAULT NULL AFTER `featured_image`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index on menu_id
SET @exists := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pages' AND INDEX_NAME = 'pages_menu_id_index'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE `pages` ADD KEY `pages_menu_id_index` (`menu_id`)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- FK pages.menu_id → menus.id (nullable, ON DELETE SET NULL)
SET @fk := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pages' AND CONSTRAINT_NAME = 'pages_menu_id_fk'
);
SET @sql := IF(@fk = 0,
  'ALTER TABLE `pages`
     ADD CONSTRAINT `pages_menu_id_fk`
     FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE SET NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Ensure sample pages have empty content placeholders
UPDATE `pages` SET `content` = COALESCE(`content`, CONCAT('<p>', `title`, '</p>'))
WHERE `content` IS NULL;

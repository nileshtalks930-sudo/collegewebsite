-- College CMS — Menu link targets
-- Run after 001–008
--
-- Menus can link to:
--   page        → Internal Pages
--   department  → Department
--   naac        → NAAC (landing or criterion)
--   iqac        → IQAC (landing or section)
--   external    → External URL
--   downloads   → Downloads (landing or item)
--   gallery     → Gallery (landing or album)
--   custom      → Custom Link (internal path)

USE `college_cms`;

-- ---------------------------------------------------------------------------
-- Stub module tables so menus can reference Downloads / Gallery items
-- (full CRUD modules can extend these later)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `downloads` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `file_path` VARCHAR(255) NULL DEFAULT NULL,
  `description` TEXT NULL,
  `category` VARCHAR(100) NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=published,0=draft',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `downloads_slug_unique` (`slug`),
  KEY `downloads_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `galleries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  `cover_image` VARCHAR(255) NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=published,0=draft',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `galleries_slug_unique` (`slug`),
  KEY `galleries_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gallery_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `gallery_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NULL DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `gallery_images_gallery_id_index` (`gallery_id`),
  CONSTRAINT `gallery_images_gallery_id_fk`
    FOREIGN KEY (`gallery_id`) REFERENCES `galleries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Expand menus.link_type and add target reference columns
-- ---------------------------------------------------------------------------

-- Step 1: widen ENUM to include new values + legacy `url`
ALTER TABLE `menus`
  MODIFY COLUMN `link_type` ENUM(
    'page',
    'department',
    'naac',
    'iqac',
    'external',
    'downloads',
    'gallery',
    'custom',
    'url'
  ) NOT NULL DEFAULT 'custom';

-- Step 2: migrate legacy `url` → external (http/https) or custom (path)
UPDATE `menus`
SET `link_type` = CASE
  WHEN `link_type` = 'url' AND `url` REGEXP '^https?://' THEN 'external'
  WHEN `link_type` = 'url' THEN 'custom'
  ELSE `link_type`
END;

-- Step 3: drop legacy `url` from ENUM
ALTER TABLE `menus`
  MODIFY COLUMN `link_type` ENUM(
    'page',
    'department',
    'naac',
    'iqac',
    'external',
    'downloads',
    'gallery',
    'custom'
  ) NOT NULL DEFAULT 'custom';

-- Step 4: add target columns (idempotent-ish via procedure-free checks)
-- department_id
SET @col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menus' AND COLUMN_NAME = 'department_id'
);
SET @sql := IF(
  @col_exists = 0,
  'ALTER TABLE `menus` ADD COLUMN `department_id` INT UNSIGNED NULL DEFAULT NULL AFTER `page_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- naac_criterion_id
SET @col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menus' AND COLUMN_NAME = 'naac_criterion_id'
);
SET @sql := IF(
  @col_exists = 0,
  'ALTER TABLE `menus` ADD COLUMN `naac_criterion_id` INT UNSIGNED NULL DEFAULT NULL AFTER `department_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- iqac_section (NULL/empty = IQAC home; else members|minutes|aqar|…)
SET @col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menus' AND COLUMN_NAME = 'iqac_section'
);
SET @sql := IF(
  @col_exists = 0,
  'ALTER TABLE `menus` ADD COLUMN `iqac_section` VARCHAR(50) NULL DEFAULT NULL AFTER `naac_criterion_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- download_id (NULL = /downloads landing)
SET @col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menus' AND COLUMN_NAME = 'download_id'
);
SET @sql := IF(
  @col_exists = 0,
  'ALTER TABLE `menus` ADD COLUMN `download_id` INT UNSIGNED NULL DEFAULT NULL AFTER `iqac_section`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- gallery_id (NULL = /gallery landing)
SET @col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menus' AND COLUMN_NAME = 'gallery_id'
);
SET @sql := IF(
  @col_exists = 0,
  'ALTER TABLE `menus` ADD COLUMN `gallery_id` INT UNSIGNED NULL DEFAULT NULL AFTER `download_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Indexes
SET @idx_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menus' AND INDEX_NAME = 'menus_department_id_index'
);
SET @sql := IF(@idx_exists = 0, 'ALTER TABLE `menus` ADD KEY `menus_department_id_index` (`department_id`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menus' AND INDEX_NAME = 'menus_naac_criterion_id_index'
);
SET @sql := IF(@idx_exists = 0, 'ALTER TABLE `menus` ADD KEY `menus_naac_criterion_id_index` (`naac_criterion_id`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menus' AND INDEX_NAME = 'menus_download_id_index'
);
SET @sql := IF(@idx_exists = 0, 'ALTER TABLE `menus` ADD KEY `menus_download_id_index` (`download_id`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menus' AND INDEX_NAME = 'menus_gallery_id_index'
);
SET @sql := IF(@idx_exists = 0, 'ALTER TABLE `menus` ADD KEY `menus_gallery_id_index` (`gallery_id`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Foreign keys (skip if already present)
SET @fk_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menus' AND CONSTRAINT_NAME = 'menus_department_id_fk'
);
SET @sql := IF(
  @fk_exists = 0,
  'ALTER TABLE `menus` ADD CONSTRAINT `menus_department_id_fk`
     FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menus' AND CONSTRAINT_NAME = 'menus_naac_criterion_id_fk'
);
SET @sql := IF(
  @fk_exists = 0,
  'ALTER TABLE `menus` ADD CONSTRAINT `menus_naac_criterion_id_fk`
     FOREIGN KEY (`naac_criterion_id`) REFERENCES `naac_criteria` (`id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menus' AND CONSTRAINT_NAME = 'menus_download_id_fk'
);
SET @sql := IF(
  @fk_exists = 0,
  'ALTER TABLE `menus` ADD CONSTRAINT `menus_download_id_fk`
     FOREIGN KEY (`download_id`) REFERENCES `downloads` (`id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'menus' AND CONSTRAINT_NAME = 'menus_gallery_id_fk'
);
SET @sql := IF(
  @fk_exists = 0,
  'ALTER TABLE `menus` ADD CONSTRAINT `menus_gallery_id_fk`
     FOREIGN KEY (`gallery_id`) REFERENCES `galleries` (`id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------------
-- Final menus shape (for reference / documentation)
-- ---------------------------------------------------------------------------
-- menus (
--   id, name, position, sort_order, parent_id, status, open_in_new_tab,
--   link_type ENUM(page|department|naac|iqac|external|downloads|gallery|custom),
--   url,                  -- used by: external, custom
--   page_id,              -- used by: page
--   department_id,        -- used by: department
--   naac_criterion_id,    -- used by: naac (NULL = /naac landing)
--   iqac_section,         -- used by: iqac (NULL = /iqac landing)
--   download_id,          -- used by: downloads (NULL = /downloads landing)
--   gallery_id,           -- used by: gallery (NULL = /gallery landing)
--   created_at, updated_at
-- )

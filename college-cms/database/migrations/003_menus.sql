-- College CMS — Dynamic Menu Management
-- Run after 001 + 002

USE `college_cms`;

-- Minimal pages table so menus can link to internal pages
-- (full Pages module can extend this later)
CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=published,0=draft',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `menus` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `position` VARCHAR(50) NOT NULL DEFAULT 'header' COMMENT 'header|footer|sidebar',
  `sort_order` INT NOT NULL DEFAULT 0,
  `parent_id` INT UNSIGNED NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active,0=inactive',
  `open_in_new_tab` TINYINT(1) NOT NULL DEFAULT 0,
  `link_type` ENUM('url', 'page') NOT NULL DEFAULT 'url'
    COMMENT 'Expanded in 009_menu_link_targets.sql',
  `url` VARCHAR(500) NULL DEFAULT NULL,
  `page_id` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `menus_position_index` (`position`),
  KEY `menus_parent_id_index` (`parent_id`),
  KEY `menus_sort_order_index` (`sort_order`),
  KEY `menus_page_id_index` (`page_id`),
  CONSTRAINT `menus_parent_id_fk`
    FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE SET NULL,
  CONSTRAINT `menus_page_id_fk`
    FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample internal pages for menu linking
INSERT INTO `pages` (`title`, `slug`, `status`, `created_at`) VALUES
  ('About Us', 'about-us', 1, NOW()),
  ('Contact', 'contact', 1, NOW()),
  ('Admissions', 'admissions', 1, NOW())
ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`),
  `status` = VALUES(`status`);

-- =============================================================================
-- College CMS — Complete MySQL Schema
-- Engine: InnoDB · Charset: utf8mb4
-- MySQL 8+ / MariaDB 10.4+
--
-- Includes (requested):
--   users, roles, permissions, menus, pages, quick_links, departments,
--   naac_pages, iqac_pages, settings, gallery, downloads, media, notices,
--   events, sliders, contacts, page_files, page_links, activity_logs, audit_logs
--
-- Plus supporting tables required by the CMS modules (role_permissions,
-- auth tokens, department/NAAC/IQAC children, media folders, gallery images).
--
-- Fresh install:
--   mysql -u root -p < database/schema.sql
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `college_cms`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `college_cms`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- Drop in reverse dependency order (safe re-run for fresh installs only)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `page_links`;
DROP TABLE IF EXISTS `page_files`;
DROP TABLE IF EXISTS `contacts`;
DROP TABLE IF EXISTS `sliders`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `notices`;
DROP TABLE IF EXISTS `quick_links`;
DROP TABLE IF EXISTS `gallery_images`;
DROP TABLE IF EXISTS `gallery`;
DROP TABLE IF EXISTS `downloads`;
DROP TABLE IF EXISTS `media`;
DROP TABLE IF EXISTS `media_folders`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `iqac_pages`;
DROP TABLE IF EXISTS `iqac_documents`;
DROP TABLE IF EXISTS `iqac_circulars`;
DROP TABLE IF EXISTS `iqac_gallery`;
DROP TABLE IF EXISTS `iqac_notices`;
DROP TABLE IF EXISTS `iqac_downloads`;
DROP TABLE IF EXISTS `iqac_ssr`;
DROP TABLE IF EXISTS `iqac_aqar`;
DROP TABLE IF EXISTS `iqac_minutes`;
DROP TABLE IF EXISTS `iqac_members`;
DROP TABLE IF EXISTS `iqac_committee`;
DROP TABLE IF EXISTS `naac_pages`;
DROP TABLE IF EXISTS `naac_images`;
DROP TABLE IF EXISTS `naac_tables`;
DROP TABLE IF EXISTS `naac_links`;
DROP TABLE IF EXISTS `naac_files`;
DROP TABLE IF EXISTS `naac_criteria`;
DROP TABLE IF EXISTS `department_gallery`;
DROP TABLE IF EXISTS `department_downloads`;
DROP TABLE IF EXISTS `department_achievements`;
DROP TABLE IF EXISTS `department_faculty`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `menus`;
DROP TABLE IF EXISTS `pages`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `remember_tokens`;
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- 1. Roles & Permissions
-- =============================================================================

CREATE TABLE `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(80) NOT NULL,
  `module` VARCHAR(60) NOT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_slug_unique` (`slug`),
  KEY `permissions_module_index` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  KEY `role_permissions_permission_id_index` (`permission_id`),
  CONSTRAINT `role_permissions_role_id_fk`
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_permission_id_fk`
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 2. Users & Auth
-- =============================================================================

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active,0=disabled',
  `last_login_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_index` (`role_id`),
  KEY `users_status_index` (`status`),
  CONSTRAINT `users_role_id_fk`
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `remember_tokens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `selector` VARCHAR(64) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `remember_tokens_selector_unique` (`selector`),
  KEY `remember_tokens_user_id_index` (`user_id`),
  CONSTRAINT `remember_tokens_user_id_fk`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(190) NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 3. Pages (base) — menu_id FK added after menus
-- =============================================================================

CREATE TABLE `pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `content` LONGTEXT NULL,
  `menu_id` INT UNSIGNED NULL DEFAULT NULL,
  `meta_title` VARCHAR(200) NULL DEFAULT NULL,
  `meta_description` VARCHAR(500) NULL DEFAULT NULL,
  `meta_keywords` VARCHAR(500) NULL DEFAULT NULL,
  `featured_image` VARCHAR(255) NULL DEFAULT NULL,
  `pdf_attachment` VARCHAR(255) NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=published,0=draft',
  `published_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`),
  KEY `pages_status_index` (`status`),
  KEY `pages_menu_id_index` (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 4. Menus
-- =============================================================================

CREATE TABLE `menus` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `position` VARCHAR(50) NOT NULL DEFAULT 'header' COMMENT 'header|footer|sidebar',
  `sort_order` INT NOT NULL DEFAULT 0,
  `parent_id` INT UNSIGNED NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active,0=inactive',
  `open_in_new_tab` TINYINT(1) NOT NULL DEFAULT 0,
  `link_type` ENUM(
    'page',
    'department',
    'naac',
    'iqac',
    'external',
    'downloads',
    'gallery',
    'custom'
  ) NOT NULL DEFAULT 'custom',
  `url` VARCHAR(500) NULL DEFAULT NULL COMMENT 'external / custom',
  `page_id` INT UNSIGNED NULL DEFAULT NULL,
  `department_id` INT UNSIGNED NULL DEFAULT NULL,
  `naac_criterion_id` INT UNSIGNED NULL DEFAULT NULL,
  `iqac_section` VARCHAR(50) NULL DEFAULT NULL,
  `download_id` INT UNSIGNED NULL DEFAULT NULL,
  `gallery_id` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `menus_position_index` (`position`),
  KEY `menus_parent_id_index` (`parent_id`),
  KEY `menus_sort_order_index` (`sort_order`),
  KEY `menus_page_id_index` (`page_id`),
  KEY `menus_department_id_index` (`department_id`),
  KEY `menus_naac_criterion_id_index` (`naac_criterion_id`),
  KEY `menus_download_id_index` (`download_id`),
  KEY `menus_gallery_id_index` (`gallery_id`),
  CONSTRAINT `menus_parent_id_fk`
    FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE SET NULL,
  CONSTRAINT `menus_page_id_fk`
    FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Circular FK: pages.menu_id → menus.id
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_menu_id_fk`
    FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE SET NULL;

-- =============================================================================
-- 5. Page files & links
-- =============================================================================

CREATE TABLE `page_files` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(50) NULL DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `page_files_page_id_index` (`page_id`),
  CONSTRAINT `page_files_page_id_fk`
    FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `page_links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `open_in_new_tab` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `page_links_page_id_index` (`page_id`),
  CONSTRAINT `page_links_page_id_fk`
    FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 6. Quick Links
-- =============================================================================

CREATE TABLE `quick_links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `icon` VARCHAR(100) NULL DEFAULT NULL,
  `open_in_new_tab` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `quick_links_status_index` (`status`),
  KEY `quick_links_sort_order_index` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 7. Departments
-- =============================================================================

CREATE TABLE `departments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `head_name` VARCHAR(150) NULL DEFAULT NULL,
  `head_designation` VARCHAR(150) NULL DEFAULT NULL,
  `head_photo` VARCHAR(255) NULL DEFAULT NULL,
  `description` LONGTEXT NULL,
  `contact_email` VARCHAR(190) NULL DEFAULT NULL,
  `contact_phone` VARCHAR(50) NULL DEFAULT NULL,
  `contact_address` TEXT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_slug_unique` (`slug`),
  KEY `departments_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `department_faculty` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `designation` VARCHAR(150) NULL DEFAULT NULL,
  `email` VARCHAR(190) NULL DEFAULT NULL,
  `phone` VARCHAR(50) NULL DEFAULT NULL,
  `photo` VARCHAR(255) NULL DEFAULT NULL,
  `bio` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `department_faculty_department_id_index` (`department_id`),
  CONSTRAINT `department_faculty_department_id_fk`
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `department_achievements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `achieved_on` DATE NULL DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `department_achievements_department_id_index` (`department_id`),
  CONSTRAINT `department_achievements_department_id_fk`
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `department_downloads` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `department_downloads_department_id_index` (`department_id`),
  CONSTRAINT `department_downloads_department_id_fk`
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `department_gallery` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NULL DEFAULT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `department_gallery_department_id_index` (`department_id`),
  CONSTRAINT `department_gallery_department_id_fk`
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `menus`
  ADD CONSTRAINT `menus_department_id_fk`
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

-- =============================================================================
-- 8. NAAC
-- =============================================================================

CREATE TABLE `naac_criteria` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `number` TINYINT UNSIGNED NOT NULL COMMENT '1-7',
  `heading` VARCHAR(255) NOT NULL,
  `description` LONGTEXT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `naac_criteria_number_unique` (`number`),
  UNIQUE KEY `naac_criteria_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `naac_files` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `criterion_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `naac_files_criterion_id_index` (`criterion_id`),
  CONSTRAINT `naac_files_criterion_id_fk`
    FOREIGN KEY (`criterion_id`) REFERENCES `naac_criteria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `naac_links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `criterion_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `open_in_new_tab` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `naac_links_criterion_id_index` (`criterion_id`),
  CONSTRAINT `naac_links_criterion_id_fk`
    FOREIGN KEY (`criterion_id`) REFERENCES `naac_criteria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `naac_tables` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `criterion_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NULL DEFAULT NULL,
  `table_html` LONGTEXT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `naac_tables_criterion_id_index` (`criterion_id`),
  CONSTRAINT `naac_tables_criterion_id_fk`
    FOREIGN KEY (`criterion_id`) REFERENCES `naac_criteria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `naac_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `criterion_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NULL DEFAULT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NULL DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `naac_images_criterion_id_index` (`criterion_id`),
  CONSTRAINT `naac_images_criterion_id_fk`
    FOREIGN KEY (`criterion_id`) REFERENCES `naac_criteria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `naac_pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `criterion_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `content` LONGTEXT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `naac_pages_criterion_slug_unique` (`criterion_id`, `slug`),
  KEY `naac_pages_criterion_id_index` (`criterion_id`),
  CONSTRAINT `naac_pages_criterion_id_fk`
    FOREIGN KEY (`criterion_id`) REFERENCES `naac_criteria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `menus`
  ADD CONSTRAINT `menus_naac_criterion_id_fk`
    FOREIGN KEY (`naac_criterion_id`) REFERENCES `naac_criteria` (`id`) ON DELETE SET NULL;

-- =============================================================================
-- 9. IQAC
-- =============================================================================

CREATE TABLE `iqac_committee` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NULL DEFAULT NULL,
  `description` LONGTEXT NULL,
  `vision` TEXT NULL,
  `mission` TEXT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `iqac_pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `content` LONGTEXT NULL,
  `meta_title` VARCHAR(200) NULL DEFAULT NULL,
  `meta_description` VARCHAR(500) NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `iqac_pages_slug_unique` (`slug`),
  KEY `iqac_pages_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `iqac_members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `designation` VARCHAR(150) NULL DEFAULT NULL,
  `role` VARCHAR(150) NULL DEFAULT NULL,
  `email` VARCHAR(190) NULL DEFAULT NULL,
  `phone` VARCHAR(50) NULL DEFAULT NULL,
  `photo` VARCHAR(255) NULL DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `iqac_minutes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `meeting_date` DATE NULL DEFAULT NULL,
  `description` TEXT NULL,
  `file_path` VARCHAR(255) NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `iqac_aqar` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `academic_year` VARCHAR(20) NULL DEFAULT NULL,
  `file_path` VARCHAR(255) NULL DEFAULT NULL,
  `description` TEXT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `iqac_ssr` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `academic_year` VARCHAR(20) NULL DEFAULT NULL,
  `file_path` VARCHAR(255) NULL DEFAULT NULL,
  `description` TEXT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `iqac_downloads` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `iqac_notices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `notice_date` DATE NULL DEFAULT NULL,
  `content` LONGTEXT NULL,
  `file_path` VARCHAR(255) NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `iqac_gallery` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NULL DEFAULT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NULL DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `iqac_circulars` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `reference_no` VARCHAR(100) NULL DEFAULT NULL,
  `circular_date` DATE NULL DEFAULT NULL,
  `file_path` VARCHAR(255) NULL DEFAULT NULL,
  `description` TEXT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `iqac_documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NULL DEFAULT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 10. Settings
-- =============================================================================

CREATE TABLE `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` LONGTEXT NULL,
  `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general'
    COMMENT 'general|contact|social|smtp|analytics|footer',
  `label` VARCHAR(150) NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`setting_key`),
  KEY `settings_group_index` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 11. Gallery (site-wide)
-- =============================================================================

CREATE TABLE `gallery` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  `cover_image` VARCHAR(255) NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gallery_slug_unique` (`slug`),
  KEY `gallery_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `gallery_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `gallery_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NULL DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `gallery_images_gallery_id_index` (`gallery_id`),
  CONSTRAINT `gallery_images_gallery_id_fk`
    FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `menus`
  ADD CONSTRAINT `menus_gallery_id_fk`
    FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE SET NULL;

-- =============================================================================
-- 12. Downloads (site-wide)
-- =============================================================================

CREATE TABLE `downloads` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `file_path` VARCHAR(255) NULL DEFAULT NULL,
  `description` TEXT NULL,
  `category` VARCHAR(100) NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `download_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `downloads_slug_unique` (`slug`),
  KEY `downloads_status_index` (`status`),
  KEY `downloads_category_index` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `menus`
  ADD CONSTRAINT `menus_download_id_fk`
    FOREIGN KEY (`download_id`) REFERENCES `downloads` (`id`) ON DELETE SET NULL;

-- =============================================================================
-- 13. Media (file manager)
-- =============================================================================

CREATE TABLE `media_folders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `parent_id` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `media_folders_parent_id_index` (`parent_id`),
  CONSTRAINT `media_folders_parent_id_fk`
    FOREIGN KEY (`parent_id`) REFERENCES `media_folders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `media` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `folder_id` INT UNSIGNED NULL DEFAULT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `stored_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `extension` VARCHAR(20) NOT NULL,
  `mime_type` VARCHAR(120) NULL DEFAULT NULL,
  `size_bytes` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `uploaded_by` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `media_folder_id_index` (`folder_id`),
  KEY `media_extension_index` (`extension`),
  KEY `media_uploaded_by_index` (`uploaded_by`),
  CONSTRAINT `media_folder_id_fk`
    FOREIGN KEY (`folder_id`) REFERENCES `media_folders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `media_uploaded_by_fk`
    FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 14. Notices
-- =============================================================================

CREATE TABLE `notices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `content` LONGTEXT NULL,
  `notice_date` DATE NULL DEFAULT NULL,
  `file_path` VARCHAR(255) NULL DEFAULT NULL,
  `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notices_slug_unique` (`slug`),
  KEY `notices_status_index` (`status`),
  KEY `notices_notice_date_index` (`notice_date`),
  KEY `notices_created_by_index` (`created_by`),
  CONSTRAINT `notices_created_by_fk`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 15. Events
-- =============================================================================

CREATE TABLE `events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(200) NOT NULL,
  `description` LONGTEXT NULL,
  `location` VARCHAR(255) NULL DEFAULT NULL,
  `start_at` DATETIME NOT NULL,
  `end_at` DATETIME NULL DEFAULT NULL,
  `image` VARCHAR(255) NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `events_slug_unique` (`slug`),
  KEY `events_status_index` (`status`),
  KEY `events_start_at_index` (`start_at`),
  KEY `events_created_by_index` (`created_by`),
  CONSTRAINT `events_created_by_fk`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 16. Sliders
-- =============================================================================

CREATE TABLE `sliders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NULL DEFAULT NULL,
  `subtitle` VARCHAR(255) NULL DEFAULT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `link_url` VARCHAR(500) NULL DEFAULT NULL,
  `link_text` VARCHAR(100) NULL DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sliders_status_index` (`status`),
  KEY `sliders_sort_order_index` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 17. Contacts (inquiry form submissions)
-- =============================================================================

CREATE TABLE `contacts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(50) NULL DEFAULT NULL,
  `subject` VARCHAR(255) NULL DEFAULT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new', 'read', 'replied', 'archived') NOT NULL DEFAULT 'new',
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `replied_by` INT UNSIGNED NULL DEFAULT NULL,
  `replied_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `contacts_status_index` (`status`),
  KEY `contacts_email_index` (`email`),
  KEY `contacts_replied_by_index` (`replied_by`),
  CONSTRAINT `contacts_replied_by_fk`
    FOREIGN KEY (`replied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 18. Activity Logs
-- =============================================================================

CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL COMMENT 'login|logout|create|update|delete|upload|…',
  `module` VARCHAR(60) NULL DEFAULT NULL,
  `description` VARCHAR(500) NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` VARCHAR(500) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_index` (`user_id`),
  KEY `activity_logs_action_index` (`action`),
  KEY `activity_logs_module_index` (`module`),
  KEY `activity_logs_created_at_index` (`created_at`),
  CONSTRAINT `activity_logs_user_id_fk`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 19. Audit Logs (row-level change history)
-- =============================================================================

CREATE TABLE `audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL DEFAULT NULL,
  `table_name` VARCHAR(100) NOT NULL,
  `record_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `action` ENUM('insert', 'update', 'delete') NOT NULL,
  `old_values` JSON NULL,
  `new_values` JSON NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_index` (`user_id`),
  KEY `audit_logs_table_name_index` (`table_name`),
  KEY `audit_logs_record_id_index` (`record_id`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `audit_logs_user_id_fk`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- End of schema
-- =============================================================================

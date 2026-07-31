-- College CMS — Roles, Permissions, User Management
-- Run after 001_auth_users.sql

USE `college_cms`;

-- ---------------------------------------------------------------------------
-- Roles
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Permissions
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `permissions` (
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

-- ---------------------------------------------------------------------------
-- Role ↔ Permission
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  CONSTRAINT `role_permissions_role_id_fk`
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_permission_id_fk`
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Seed roles
-- ---------------------------------------------------------------------------
INSERT INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
  (1, 'Super Admin', 'super_admin', 'Full system access'),
  (2, 'Admin', 'admin', 'Manage most CMS modules'),
  (3, 'Department Editor', 'department_editor', 'Manage department content'),
  (4, 'IQAC Editor', 'iqac_editor', 'Manage IQAC content'),
  (5, 'NAAC Editor', 'naac_editor', 'Manage NAAC content'),
  (6, 'Faculty', 'faculty', 'Limited content access')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`);

-- ---------------------------------------------------------------------------
-- Seed permissions
-- ---------------------------------------------------------------------------
INSERT INTO `permissions` (`name`, `slug`, `module`, `description`) VALUES
  ('View Dashboard', 'dashboard.view', 'dashboard', 'Access admin dashboard'),

  ('View Users', 'users.view', 'users', 'List users'),
  ('Create Users', 'users.create', 'users', 'Create users'),
  ('Update Users', 'users.update', 'users', 'Edit users'),
  ('Delete Users', 'users.delete', 'users', 'Delete users'),

  ('View Menus', 'menus.view', 'menus', 'List menus'),
  ('Manage Menus', 'menus.manage', 'menus', 'Create/update/delete menus'),

  ('View Pages', 'pages.view', 'pages', 'List pages'),
  ('Manage Pages', 'pages.manage', 'pages', 'Create/update/delete pages'),

  ('View Media', 'media.view', 'media', 'Browse uploads'),
  ('Manage Media', 'media.manage', 'media', 'Upload/delete files'),

  ('View Departments', 'departments.view', 'departments', 'View departments'),
  ('Manage Departments', 'departments.manage', 'departments', 'Edit departments'),

  ('View IQAC', 'iqac.view', 'iqac', 'View IQAC'),
  ('Manage IQAC', 'iqac.manage', 'iqac', 'Edit IQAC'),

  ('View NAAC', 'naac.view', 'naac', 'View NAAC'),
  ('Manage NAAC', 'naac.manage', 'naac', 'Edit NAAC'),

  ('View Notices', 'notices.view', 'notices', 'View notices'),
  ('Manage Notices', 'notices.manage', 'notices', 'Edit notices'),

  ('View Gallery', 'gallery.view', 'gallery', 'View gallery'),
  ('Manage Gallery', 'gallery.manage', 'gallery', 'Edit gallery'),

  ('View Downloads', 'downloads.view', 'downloads', 'View downloads'),
  ('Manage Downloads', 'downloads.manage', 'downloads', 'Edit downloads'),

  ('View Slider', 'slider.view', 'slider', 'View homepage slider'),
  ('Manage Slider', 'slider.manage', 'slider', 'Edit homepage slider'),

  ('View Settings', 'settings.view', 'settings', 'View site settings'),
  ('Manage Settings', 'settings.manage', 'settings', 'Edit site settings'),

  ('View SEO', 'seo.view', 'seo', 'View SEO settings'),
  ('Manage SEO', 'seo.manage', 'seo', 'Edit SEO settings'),

  ('Backup Database', 'backup.manage', 'backup', 'Create/download DB backups')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `module` = VALUES(`module`),
  `description` = VALUES(`description`);

-- ---------------------------------------------------------------------------
-- Assign permissions to roles
-- ---------------------------------------------------------------------------
-- Super Admin: all permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, p.id FROM `permissions` p;

-- Admin: all except backup.manage
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, p.id FROM `permissions` p WHERE p.slug <> 'backup.manage';

-- Department Editor
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, p.id FROM `permissions` p
WHERE p.slug IN (
  'dashboard.view', 'pages.view', 'media.view', 'media.manage',
  'departments.view', 'departments.manage', 'notices.view', 'gallery.view'
);

-- IQAC Editor
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, p.id FROM `permissions` p
WHERE p.slug IN (
  'dashboard.view', 'pages.view', 'media.view', 'media.manage',
  'iqac.view', 'iqac.manage', 'notices.view', 'downloads.view'
);

-- NAAC Editor
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 5, p.id FROM `permissions` p
WHERE p.slug IN (
  'dashboard.view', 'pages.view', 'media.view', 'media.manage',
  'naac.view', 'naac.manage', 'notices.view', 'downloads.view'
);

-- Faculty
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 6, p.id FROM `permissions` p
WHERE p.slug IN (
  'dashboard.view', 'notices.view', 'gallery.view', 'downloads.view', 'pages.view'
);

-- ---------------------------------------------------------------------------
-- Migrate users.role (ENUM) → users.role_id
-- ---------------------------------------------------------------------------
SET @role_col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'role'
);

SET @role_id_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'role_id'
);

-- Add role_id if missing
SET @sql := IF(
  @role_id_exists = 0,
  'ALTER TABLE `users` ADD COLUMN `role_id` INT UNSIGNED NULL AFTER `password`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Map legacy ENUM role → role_id when old column exists
SET @sql := IF(
  @role_col_exists > 0,
  'UPDATE `users` u
     LEFT JOIN `roles` r ON r.slug = u.role
     SET u.role_id = COALESCE(r.id, 2)
     WHERE u.role_id IS NULL',
  'UPDATE `users` SET `role_id` = 1 WHERE `role_id` IS NULL'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Ensure every user has a role
UPDATE `users` SET `role_id` = 1 WHERE `role_id` IS NULL;

-- Drop legacy ENUM column if present
SET @sql := IF(
  @role_col_exists > 0,
  'ALTER TABLE `users` DROP COLUMN `role`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Enforce NOT NULL + FK (safe to re-run: drop FK if exists first)
SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND CONSTRAINT_NAME = 'users_role_id_fk'
);

SET @sql := IF(
  @fk_exists > 0,
  'ALTER TABLE `users` DROP FOREIGN KEY `users_role_id_fk`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

ALTER TABLE `users`
  MODIFY COLUMN `role_id` INT UNSIGNED NOT NULL,
  ADD CONSTRAINT `users_role_id_fk`
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

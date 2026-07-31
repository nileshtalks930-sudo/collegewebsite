-- College CMS — Site Settings Module
-- Run after 001–008

USE `college_cms`;

CREATE TABLE IF NOT EXISTS `settings` (
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

-- Seed defaults (idempotent)
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`, `label`, `created_at`) VALUES
  -- General
  ('website_name', 'College Website', 'general', 'Website Name', NOW()),
  ('logo', NULL, 'general', 'Logo', NOW()),
  ('favicon', NULL, 'general', 'Favicon', NOW()),

  -- Contact
  ('address', NULL, 'contact', 'Address', NOW()),
  ('email', NULL, 'contact', 'Email', NOW()),
  ('phone', NULL, 'contact', 'Phone', NOW()),
  ('google_map', NULL, 'contact', 'Google Map Embed / URL', NOW()),

  -- Social Links
  ('social_facebook', NULL, 'social', 'Facebook', NOW()),
  ('social_twitter', NULL, 'social', 'Twitter / X', NOW()),
  ('social_instagram', NULL, 'social', 'Instagram', NOW()),
  ('social_youtube', NULL, 'social', 'YouTube', NOW()),
  ('social_linkedin', NULL, 'social', 'LinkedIn', NOW()),

  -- SMTP
  ('smtp_host', NULL, 'smtp', 'SMTP Host', NOW()),
  ('smtp_port', '587', 'smtp', 'SMTP Port', NOW()),
  ('smtp_username', NULL, 'smtp', 'SMTP Username', NOW()),
  ('smtp_password', NULL, 'smtp', 'SMTP Password', NOW()),
  ('smtp_encryption', 'tls', 'smtp', 'SMTP Encryption', NOW()),
  ('smtp_from_email', NULL, 'smtp', 'From Email', NOW()),
  ('smtp_from_name', NULL, 'smtp', 'From Name', NOW()),

  -- Analytics
  ('analytics_code', NULL, 'analytics', 'Analytics Code', NOW()),

  -- Footer
  ('footer_text', NULL, 'footer', 'Footer Text', NOW()),
  ('copyright', '© College. All rights reserved.', 'footer', 'Copyright', NOW())
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `setting_group` = VALUES(`setting_group`);

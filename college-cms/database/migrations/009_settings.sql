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
  ('website_name', 'Nilesh College', 'general', 'Website Name', NOW()),
  ('logo', 'uploads/settings/default-logo.svg', 'general', 'Logo', NOW()),
  ('favicon', 'uploads/settings/default-favicon.svg', 'general', 'Favicon', NOW()),

  -- Contact
  ('address', 'College Road, City, State — 400001\nIndia', 'contact', 'Address', NOW()),
  ('email', 'info@nileshcollege.edu', 'contact', 'Email', NOW()),
  ('phone', '+91 98765 43210', 'contact', 'Phone', NOW()),
  ('google_map', 'https://maps.google.com/?q=College+Road', 'contact', 'Google Map Embed / URL', NOW()),

  -- Social Links
  ('social_facebook', 'https://facebook.com/', 'social', 'Facebook', NOW()),
  ('social_twitter', 'https://x.com/', 'social', 'Twitter / X', NOW()),
  ('social_instagram', 'https://instagram.com/', 'social', 'Instagram', NOW()),
  ('social_youtube', 'https://youtube.com/', 'social', 'YouTube', NOW()),
  ('social_linkedin', 'https://linkedin.com/', 'social', 'LinkedIn', NOW()),

  -- SMTP
  ('smtp_host', 'smtp.gmail.com', 'smtp', 'SMTP Host', NOW()),
  ('smtp_port', '587', 'smtp', 'SMTP Port', NOW()),
  ('smtp_username', 'info@nileshcollege.edu', 'smtp', 'SMTP Username', NOW()),
  ('smtp_password', NULL, 'smtp', 'SMTP Password', NOW()),
  ('smtp_encryption', 'tls', 'smtp', 'SMTP Encryption', NOW()),
  ('smtp_from_email', 'info@nileshcollege.edu', 'smtp', 'From Email', NOW()),
  ('smtp_from_name', 'Nilesh College', 'smtp', 'From Name', NOW()),

  -- Analytics
  ('analytics_code', NULL, 'analytics', 'Analytics Code', NOW()),

  -- Footer
  ('footer_text', 'Nilesh College is committed to academic excellence, research, and community service.', 'footer', 'Footer Text', NOW()),
  ('copyright', '© 2026 Nilesh College. All rights reserved.', 'footer', 'Copyright', NOW())
ON DUPLICATE KEY UPDATE
  `setting_value` = IF(`setting_value` IS NULL OR `setting_value` = '', VALUES(`setting_value`), `setting_value`),
  `label` = VALUES(`label`),
  `setting_group` = VALUES(`setting_group`);

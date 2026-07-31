-- College CMS — NAAC Module (Criteria 1–7)
-- Run after prior migrations

USE `college_cms`;

CREATE TABLE IF NOT EXISTS `naac_criteria` (
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

CREATE TABLE IF NOT EXISTS `naac_files` (
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

CREATE TABLE IF NOT EXISTS `naac_links` (
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

CREATE TABLE IF NOT EXISTS `naac_tables` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `criterion_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `table_html` LONGTEXT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `naac_tables_criterion_id_index` (`criterion_id`),
  CONSTRAINT `naac_tables_criterion_id_fk`
    FOREIGN KEY (`criterion_id`) REFERENCES `naac_criteria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `naac_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `criterion_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NULL DEFAULT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NULL DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `naac_images_criterion_id_index` (`criterion_id`),
  CONSTRAINT `naac_images_criterion_id_fk`
    FOREIGN KEY (`criterion_id`) REFERENCES `naac_criteria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `naac_pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `criterion_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
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

INSERT INTO `naac_criteria` (`number`, `heading`, `description`, `slug`, `status`, `created_at`) VALUES
  (1, 'Curricular Aspects', '<p>Criterion 1 focuses on curriculum design, development, and enrichment.</p>', 'criteria-1', 1, NOW()),
  (2, 'Teaching-Learning and Evaluation', '<p>Criterion 2 covers teaching-learning processes and evaluation methods.</p>', 'criteria-2', 1, NOW()),
  (3, 'Research, Innovations and Extension', '<p>Criterion 3 highlights research output, innovation, and extension activities.</p>', 'criteria-3', 1, NOW()),
  (4, 'Infrastructure and Learning Resources', '<p>Criterion 4 documents physical facilities and learning resources.</p>', 'criteria-4', 1, NOW()),
  (5, 'Student Support and Progression', '<p>Criterion 5 presents student support systems and progression outcomes.</p>', 'criteria-5', 1, NOW()),
  (6, 'Governance, Leadership and Management', '<p>Criterion 6 describes institutional governance and leadership practices.</p>', 'criteria-6', 1, NOW()),
  (7, 'Institutional Values and Best Practices', '<p>Criterion 7 captures institutional values and distinct best practices.</p>', 'criteria-7', 1, NOW())
ON DUPLICATE KEY UPDATE
  `heading` = VALUES(`heading`),
  `slug` = VALUES(`slug`);

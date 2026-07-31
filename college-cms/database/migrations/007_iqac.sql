-- College CMS — IQAC Module
-- Run after prior migrations

USE `college_cms`;

-- Committee overview (single editable profile)
CREATE TABLE IF NOT EXISTS `iqac_committee` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL DEFAULT 'Internal Quality Assurance Cell',
  `description` LONGTEXT NULL,
  `vision` TEXT NULL,
  `mission` TEXT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `iqac_members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `designation` VARCHAR(150) NULL DEFAULT NULL,
  `role` VARCHAR(150) NULL DEFAULT NULL COMMENT 'Coordinator, Member, etc.',
  `email` VARCHAR(190) NULL DEFAULT NULL,
  `phone` VARCHAR(50) NULL DEFAULT NULL,
  `photo` VARCHAR(255) NULL DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `iqac_minutes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `meeting_date` DATE NULL DEFAULT NULL,
  `description` TEXT NULL,
  `file_path` VARCHAR(255) NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `iqac_minutes_meeting_date_index` (`meeting_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `iqac_aqar` (
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

CREATE TABLE IF NOT EXISTS `iqac_ssr` (
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

CREATE TABLE IF NOT EXISTS `iqac_downloads` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `iqac_notices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `notice_date` DATE NULL DEFAULT NULL,
  `content` LONGTEXT NULL,
  `file_path` VARCHAR(255) NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `iqac_notices_notice_date_index` (`notice_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `iqac_gallery` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NULL DEFAULT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) NULL DEFAULT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `iqac_circulars` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `reference_no` VARCHAR(100) NULL DEFAULT NULL,
  `circular_date` DATE NULL DEFAULT NULL,
  `file_path` VARCHAR(255) NULL DEFAULT NULL,
  `description` TEXT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `iqac_circulars_circular_date_index` (`circular_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `iqac_documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NULL DEFAULT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `iqac_committee` (`id`, `title`, `description`, `vision`, `mission`, `status`, `created_at`)
VALUES (
  1,
  'Internal Quality Assurance Cell (IQAC)',
  '<p>The IQAC is constituted to promote quality enhancement and sustenance in academic and administrative performance.</p>',
  'To develop a quality culture across the institution.',
  'To institutionalize best practices for continuous improvement.',
  1,
  NOW()
)
ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`);

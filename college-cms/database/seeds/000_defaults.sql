-- =============================================================================
-- College CMS — Default seed data
-- Run AFTER schema.sql (or after migrations 001–010)
--
-- Default admin login:
--   Email:    admin@college.local
--   Password: Admin@123
-- =============================================================================

USE `college_cms`;

-- ---------------------------------------------------------------------------
-- Roles & permissions (safe if already seeded by 002)
-- ---------------------------------------------------------------------------
INSERT INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
  (1, 'Super Admin', 'super_admin', 'Full system access'),
  (2, 'Admin', 'admin', 'Manage most modules'),
  (3, 'Department Editor', 'department_editor', 'Manage departments'),
  (4, 'IQAC Editor', 'iqac_editor', 'Manage IQAC'),
  (5, 'NAAC Editor', 'naac_editor', 'Manage NAAC'),
  (6, 'Faculty', 'faculty', 'Limited access')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`);

INSERT INTO `permissions` (`name`, `slug`, `module`, `description`) VALUES
  ('View Dashboard', 'dashboard.view', 'dashboard', 'Access admin dashboard'),
  ('View Users', 'users.view', 'users', 'View users'),
  ('Manage Users', 'users.manage', 'users', 'Create/update/delete users'),
  ('View Menus', 'menus.view', 'menus', 'View menus'),
  ('Manage Menus', 'menus.manage', 'menus', 'Edit menus'),
  ('View Pages', 'pages.view', 'pages', 'View pages'),
  ('Manage Pages', 'pages.manage', 'pages', 'Edit pages'),
  ('View Media', 'media.view', 'media', 'Browse uploads'),
  ('Manage Media', 'media.manage', 'media', 'Upload/delete files'),
  ('View Departments', 'departments.view', 'departments', 'View departments'),
  ('Manage Departments', 'departments.manage', 'departments', 'Edit departments'),
  ('View NAAC', 'naac.view', 'naac', 'View NAAC'),
  ('Manage NAAC', 'naac.manage', 'naac', 'Edit NAAC'),
  ('View IQAC', 'iqac.view', 'iqac', 'View IQAC'),
  ('Manage IQAC', 'iqac.manage', 'iqac', 'Edit IQAC'),
  ('View Notices', 'notices.view', 'notices', 'View notices'),
  ('Manage Notices', 'notices.manage', 'notices', 'Edit notices'),
  ('View Gallery', 'gallery.view', 'gallery', 'View gallery'),
  ('Manage Gallery', 'gallery.manage', 'gallery', 'Edit gallery'),
  ('View Downloads', 'downloads.view', 'downloads', 'View downloads'),
  ('Manage Downloads', 'downloads.manage', 'downloads', 'Edit downloads'),
  ('View Settings', 'settings.view', 'settings', 'View site settings'),
  ('Manage Settings', 'settings.manage', 'settings', 'Edit site settings'),
  ('View SEO', 'seo.view', 'seo', 'View SEO settings'),
  ('Manage SEO', 'seo.manage', 'seo', 'Edit SEO settings'),
  ('Backup Database', 'backup.manage', 'backup', 'Create/download DB backups')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `module` = VALUES(`module`),
  `description` = VALUES(`description`);

-- Super Admin: all permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, p.id FROM `permissions` p;

-- Admin: all except backup
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, p.id FROM `permissions` p WHERE p.slug <> 'backup.manage';

-- ---------------------------------------------------------------------------
-- Default Super Admin user
-- Email: admin@college.local  |  Password: Admin@123
-- ---------------------------------------------------------------------------
INSERT INTO `users` (`name`, `email`, `password`, `role_id`, `status`, `created_at`)
VALUES (
  'Super Admin',
  'admin@college.local',
  '$2y$10$R3gUqVbK8ZDJHp1bkJ8Y7OsVcbB8y9.k5TGJzljvhc8NKFFH7X5iC',
  1,
  1,
  NOW()
)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `password` = VALUES(`password`),
  `role_id` = VALUES(`role_id`),
  `status` = VALUES(`status`);

-- ---------------------------------------------------------------------------
-- Site settings (college identity, contact, footer, SMTP defaults)
-- ---------------------------------------------------------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`, `label`, `created_at`) VALUES
  ('website_name', 'Nilesh College', 'general', 'Website Name', NOW()),
  ('logo', 'uploads/settings/default-logo.svg', 'general', 'Logo', NOW()),
  ('favicon', 'uploads/settings/default-favicon.svg', 'general', 'Favicon', NOW()),

  ('address', 'College Road, City, State — 400001\nIndia', 'contact', 'Address', NOW()),
  ('email', 'info@nileshcollege.edu', 'contact', 'Email', NOW()),
  ('phone', '+91 98765 43210', 'contact', 'Phone', NOW()),
  ('google_map', 'https://maps.google.com/?q=College+Road', 'contact', 'Google Map Embed / URL', NOW()),

  ('social_facebook', 'https://facebook.com/', 'social', 'Facebook', NOW()),
  ('social_twitter', 'https://x.com/', 'social', 'Twitter / X', NOW()),
  ('social_instagram', 'https://instagram.com/', 'social', 'Instagram', NOW()),
  ('social_youtube', 'https://youtube.com/', 'social', 'YouTube', NOW()),
  ('social_linkedin', 'https://linkedin.com/', 'social', 'LinkedIn', NOW()),

  ('smtp_host', 'smtp.gmail.com', 'smtp', 'SMTP Host', NOW()),
  ('smtp_port', '587', 'smtp', 'SMTP Port', NOW()),
  ('smtp_username', 'info@nileshcollege.edu', 'smtp', 'SMTP Username', NOW()),
  ('smtp_password', '', 'smtp', 'SMTP Password', NOW()),
  ('smtp_encryption', 'tls', 'smtp', 'SMTP Encryption', NOW()),
  ('smtp_from_email', 'info@nileshcollege.edu', 'smtp', 'From Email', NOW()),
  ('smtp_from_name', 'Nilesh College', 'smtp', 'From Name', NOW()),

  ('analytics_code', '', 'analytics', 'Analytics Code', NOW()),

  ('footer_text', 'Nilesh College is committed to academic excellence, research, and community service.', 'footer', 'Footer Text', NOW()),
  ('copyright', '© 2026 Nilesh College. All rights reserved.', 'footer', 'Copyright', NOW())
ON DUPLICATE KEY UPDATE
  `setting_value` = VALUES(`setting_value`),
  `label` = VALUES(`label`),
  `setting_group` = VALUES(`setting_group`);

-- ---------------------------------------------------------------------------
-- Starter pages
-- ---------------------------------------------------------------------------
INSERT INTO `pages` (`title`, `slug`, `content`, `status`, `meta_title`, `meta_description`, `created_at`) VALUES
  (
    'About Us',
    'about-us',
    '<h2>About Nilesh College</h2><p>Nilesh College provides quality higher education with a focus on academics, values, and student development.</p>',
    1,
    'About Us | Nilesh College',
    'Learn about Nilesh College, our mission, and campus life.',
    NOW()
  ),
  (
    'Admissions',
    'admissions',
    '<h2>Admissions</h2><p>Applications are open for the new academic year. Contact the admission office for eligibility, fees, and deadlines.</p>',
    1,
    'Admissions | Nilesh College',
    'Admission information for Nilesh College programmes.',
    NOW()
  ),
  (
    'Contact',
    'contact',
    '<h2>Contact Us</h2><p>Email: info@nileshcollege.edu<br>Phone: +91 98765 43210<br>Address: College Road, City, State — 400001</p>',
    1,
    'Contact | Nilesh College',
    'Contact Nilesh College.',
    NOW()
  )
ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`),
  `content` = VALUES(`content`),
  `status` = VALUES(`status`),
  `meta_title` = VALUES(`meta_title`),
  `meta_description` = VALUES(`meta_description`);

-- ---------------------------------------------------------------------------
-- Default header menus
-- ---------------------------------------------------------------------------
INSERT INTO `menus` (`id`, `name`, `position`, `sort_order`, `parent_id`, `status`, `open_in_new_tab`, `link_type`, `url`, `page_id`, `created_at`)
SELECT 1, 'Home', 'header', 0, NULL, 1, 0, 'custom', '/', NULL, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `menus` WHERE `id` = 1);

INSERT INTO `menus` (`id`, `name`, `position`, `sort_order`, `parent_id`, `status`, `open_in_new_tab`, `link_type`, `url`, `page_id`, `created_at`)
SELECT 2, 'About', 'header', 1, NULL, 1, 0, 'page', NULL, p.id, NOW()
FROM `pages` p WHERE p.slug = 'about-us'
AND NOT EXISTS (SELECT 1 FROM `menus` WHERE `id` = 2);

INSERT INTO `menus` (`id`, `name`, `position`, `sort_order`, `parent_id`, `status`, `open_in_new_tab`, `link_type`, `url`, `page_id`, `created_at`)
SELECT 3, 'Admissions', 'header', 2, NULL, 1, 0, 'page', NULL, p.id, NOW()
FROM `pages` p WHERE p.slug = 'admissions'
AND NOT EXISTS (SELECT 1 FROM `menus` WHERE `id` = 3);

INSERT INTO `menus` (`id`, `name`, `position`, `sort_order`, `parent_id`, `status`, `open_in_new_tab`, `link_type`, `url`, `page_id`, `created_at`)
SELECT 4, 'Contact', 'header', 3, NULL, 1, 0, 'page', NULL, p.id, NOW()
FROM `pages` p WHERE p.slug = 'contact'
AND NOT EXISTS (SELECT 1 FROM `menus` WHERE `id` = 4);

-- Sync pages.menu_id
UPDATE `pages` p
INNER JOIN `menus` m ON m.page_id = p.id
SET p.menu_id = m.id;

-- ---------------------------------------------------------------------------
-- Sample departments
-- ---------------------------------------------------------------------------
INSERT INTO `departments` (`name`, `slug`, `head_name`, `head_designation`, `description`, `contact_email`, `contact_phone`, `status`, `created_at`) VALUES
  (
    'Computer Science',
    'computer-science',
    'Dr. A. Sharma',
    'Head of Department',
    '<p>Undergraduate and postgraduate programmes in Computer Science, AI, and Software Engineering.</p>',
    'cs@nileshcollege.edu',
    '+91 98765 43211',
    1,
    NOW()
  ),
  (
    'Commerce',
    'commerce',
    'Prof. R. Patel',
    'Head of Department',
    '<p>Accounting, finance, taxation, and business studies programmes.</p>',
    'commerce@nileshcollege.edu',
    '+91 98765 43212',
    1,
    NOW()
  ),
  (
    'Arts & Humanities',
    'arts-humanities',
    'Dr. S. Khan',
    'Head of Department',
    '<p>Languages, history, and social sciences with a strong liberal-arts foundation.</p>',
    'arts@nileshcollege.edu',
    '+91 98765 43213',
    1,
    NOW()
  )
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `head_name` = VALUES(`head_name`),
  `description` = VALUES(`description`),
  `status` = VALUES(`status`);

-- ---------------------------------------------------------------------------
-- Quick links
-- ---------------------------------------------------------------------------
INSERT INTO `quick_links` (`title`, `url`, `icon`, `open_in_new_tab`, `sort_order`, `status`, `created_at`)
SELECT 'Admissions', '/page/admissions', 'bi-mortarboard', 0, 0, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `quick_links` WHERE `title` = 'Admissions');

INSERT INTO `quick_links` (`title`, `url`, `icon`, `open_in_new_tab`, `sort_order`, `status`, `created_at`)
SELECT 'Downloads', '/downloads', 'bi-download', 0, 1, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `quick_links` WHERE `title` = 'Downloads');

INSERT INTO `quick_links` (`title`, `url`, `icon`, `open_in_new_tab`, `sort_order`, `status`, `created_at`)
SELECT 'Gallery', '/gallery', 'bi-images', 0, 2, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `quick_links` WHERE `title` = 'Gallery');

INSERT INTO `quick_links` (`title`, `url`, `icon`, `open_in_new_tab`, `sort_order`, `status`, `created_at`)
SELECT 'Contact', '/page/contact', 'bi-envelope', 0, 3, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `quick_links` WHERE `title` = 'Contact');

-- ---------------------------------------------------------------------------
-- Sample notice
-- ---------------------------------------------------------------------------
INSERT INTO `notices` (`title`, `slug`, `content`, `notice_date`, `is_pinned`, `status`, `created_by`, `created_at`)
SELECT
  'Welcome to the new college website',
  'welcome-new-website',
  '<p>Our new website is live. Explore departments, admissions, and campus updates.</p>',
  CURDATE(),
  1,
  1,
  1,
  NOW()
WHERE NOT EXISTS (SELECT 1 FROM `notices` WHERE `slug` = 'welcome-new-website');

-- ---------------------------------------------------------------------------
-- Sample slider
-- ---------------------------------------------------------------------------
INSERT INTO `sliders` (`title`, `subtitle`, `image_path`, `link_url`, `link_text`, `sort_order`, `status`, `created_at`)
SELECT
  'Welcome to Nilesh College',
  'Excellence in education, research, and community service.',
  'uploads/settings/default-logo.svg',
  '/page/admissions',
  'Apply Now',
  0,
  1,
  NOW()
WHERE NOT EXISTS (SELECT 1 FROM `sliders` WHERE `title` = 'Welcome to Nilesh College');


-- ---------------------------------------------------------------------------
-- IQAC committee defaults
-- ---------------------------------------------------------------------------
INSERT INTO `iqac_committee` (`title`, `description`, `vision`, `mission`, `status`, `created_at`)
SELECT
  'Internal Quality Assurance Cell',
  '<p>The IQAC plans, guides, and monitors quality assurance and enhancement activities of the college.</p>',
  'To develop a quality culture through continuous improvement.',
  'To ensure stakeholder participation in quality initiatives.',
  1,
  NOW()
WHERE NOT EXISTS (SELECT 1 FROM `iqac_committee` LIMIT 1);

-- ---------------------------------------------------------------------------
-- NAAC criteria (1–7) if empty
-- ---------------------------------------------------------------------------
INSERT INTO `naac_criteria` (`number`, `heading`, `description`, `slug`, `status`, `created_at`)
SELECT * FROM (
  SELECT 1 AS number, 'Curricular Aspects' AS heading, NULL AS description, 'curricular-aspects' AS slug, 1 AS status, NOW() AS created_at
  UNION ALL SELECT 2, 'Teaching-Learning and Evaluation', NULL, 'teaching-learning-evaluation', 1, NOW()
  UNION ALL SELECT 3, 'Research, Innovations and Extension', NULL, 'research-innovations-extension', 1, NOW()
  UNION ALL SELECT 4, 'Infrastructure and Learning Resources', NULL, 'infrastructure-learning-resources', 1, NOW()
  UNION ALL SELECT 5, 'Student Support and Progression', NULL, 'student-support-progression', 1, NOW()
  UNION ALL SELECT 6, 'Governance, Leadership and Management', NULL, 'governance-leadership-management', 1, NOW()
  UNION ALL SELECT 7, 'Institutional Values and Best Practices', NULL, 'institutional-values-best-practices', 1, NOW()
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `naac_criteria` LIMIT 1);

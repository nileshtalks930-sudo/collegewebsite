-- Seed default super admin (run after 002_roles_permissions.sql)
-- Password: Admin@123  (bcrypt)

USE `college_cms`;

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

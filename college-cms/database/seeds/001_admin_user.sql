-- Seed default super admin
-- Password: Admin@123  (bcrypt)

USE `college_cms`;

INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`, `created_at`)
VALUES (
  'Super Admin',
  'admin@college.local',
  '$2y$10$R3gUqVbK8ZDJHp1bkJ8Y7OsVcbB8y9.k5TGJzljvhc8NKFFH7X5iC',
  'super_admin',
  1,
  NOW()
)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `password` = VALUES(`password`),
  `role` = VALUES(`role`),
  `status` = VALUES(`status`);

<?php

declare(strict_types=1);

/**
 * Seed default admin user via PDO.
 * Usage: php database/seeds/seed_admin.php
 * Requires migrations 001 + 002.
 */

require dirname(__DIR__, 2) . '/app/Core/Autoload.php';
require dirname(__DIR__, 2) . '/app/Helpers/functions.php';

use App\Core\Database;
use App\Models\Role;

$email = 'admin@college.local';
$name = 'Super Admin';
$password = 'Admin@123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$role = Role::findBySlug('super_admin');
if ($role === null) {
    fwrite(STDERR, "Run migration 002_roles_permissions.sql first.\n");
    exit(1);
}

$pdo = Database::connection();

$stmt = $pdo->prepare(
    'INSERT INTO users (name, email, password, role_id, status, created_at)
     VALUES (:name, :email, :password, :role_id, 1, NOW())
     ON DUPLICATE KEY UPDATE
       name = VALUES(name),
       password = VALUES(password),
       role_id = VALUES(role_id),
       status = 1'
);

$stmt->execute([
    'name' => $name,
    'email' => $email,
    'password' => $hash,
    'role_id' => (int) $role['id'],
]);

echo "Seeded admin user:\n";
echo "  Email   : {$email}\n";
echo "  Password: {$password}\n";
echo "  Role    : Super Admin\n";

<?php

declare(strict_types=1);

/**
 * Seed default admin user via PDO.
 * Usage: php database/seeds/seed_admin.php
 */

require dirname(__DIR__, 2) . '/app/Core/Autoload.php';
require dirname(__DIR__, 2) . '/app/Helpers/functions.php';

use App\Core\Database;

$email = 'admin@college.local';
$name = 'Super Admin';
$password = 'Admin@123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$pdo = Database::connection();

$stmt = $pdo->prepare(
    'INSERT INTO users (name, email, password, role, status, created_at)
     VALUES (:name, :email, :password, :role, 1, NOW())
     ON DUPLICATE KEY UPDATE
       name = VALUES(name),
       password = VALUES(password),
       role = VALUES(role),
       status = 1'
);

$stmt->execute([
    'name' => $name,
    'email' => $email,
    'password' => $hash,
    'role' => 'super_admin',
]);

echo "Seeded admin user:\n";
echo "  Email   : {$email}\n";
echo "  Password: {$password}\n";

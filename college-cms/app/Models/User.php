<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class User extends Model
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => strtolower(trim($email))]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function updatePassword(int $id, string $hash): void
    {
        $stmt = self::db()->prepare('UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['password' => $hash, 'id' => $id]);
    }

    public static function touchLastLogin(int $id): void
    {
        $stmt = self::db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function countActive(): int
    {
        return (int) self::db()->query('SELECT COUNT(*) FROM users WHERE status = 1')->fetchColumn();
    }
}

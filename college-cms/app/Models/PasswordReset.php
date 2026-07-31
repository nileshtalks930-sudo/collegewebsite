<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class PasswordReset extends Model
{
    public static function create(string $email, string $tokenHash, string $expiresAt): void
    {
        // Invalidate previous tokens for this email
        self::deleteByEmail($email);

        $stmt = self::db()->prepare(
            'INSERT INTO password_resets (email, token_hash, expires_at, created_at)
             VALUES (:email, :token_hash, :expires_at, NOW())'
        );
        $stmt->execute([
            'email' => strtolower(trim($email)),
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    public static function findValid(string $email, string $tokenHash): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM password_resets
             WHERE email = :email AND token_hash = :token_hash AND expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute([
            'email' => strtolower(trim($email)),
            'token_hash' => $tokenHash,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function deleteByEmail(string $email): void
    {
        $stmt = self::db()->prepare('DELETE FROM password_resets WHERE email = :email');
        $stmt->execute(['email' => strtolower(trim($email))]);
    }
}

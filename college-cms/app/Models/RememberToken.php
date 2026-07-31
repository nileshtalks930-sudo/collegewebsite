<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class RememberToken extends Model
{
    public static function create(int $userId, string $selector, string $tokenHash, string $expiresAt): void
    {
        $stmt = self::db()->prepare(
            'INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at, created_at)
             VALUES (:user_id, :selector, :token_hash, :expires_at, NOW())'
        );
        $stmt->execute([
            'user_id' => $userId,
            'selector' => $selector,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    public static function findValidBySelector(string $selector): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM remember_tokens
             WHERE selector = :selector AND expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute(['selector' => $selector]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function deleteBySelector(string $selector): void
    {
        $stmt = self::db()->prepare('DELETE FROM remember_tokens WHERE selector = :selector');
        $stmt->execute(['selector' => $selector]);
    }

    public static function deleteByUserId(int $userId): void
    {
        $stmt = self::db()->prepare('DELETE FROM remember_tokens WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
    }
}

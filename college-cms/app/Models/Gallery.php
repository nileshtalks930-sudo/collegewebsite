<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Gallery extends Model
{
    public static function allPublished(): array
    {
        $stmt = self::db()->query(
            'SELECT id, title, slug FROM galleries WHERE status = 1 ORDER BY sort_order ASC, title ASC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM galleries WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function publicUrl(?string $slug = null): string
    {
        if ($slug === null || $slug === '') {
            return '/gallery';
        }
        return '/gallery/' . ltrim($slug, '/');
    }
}

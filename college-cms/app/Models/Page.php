<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Page extends Model
{
    public static function allPublished(): array
    {
        $stmt = self::db()->query(
            'SELECT id, title, slug FROM pages WHERE status = 1 ORDER BY title ASC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM pages WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}

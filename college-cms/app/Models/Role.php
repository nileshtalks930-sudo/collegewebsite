<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Role extends Model
{
    public static function all(): array
    {
        $stmt = self::db()->query('SELECT * FROM roles ORDER BY id ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM roles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM roles WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** @return list<string> permission slugs */
    public static function permissionSlugs(int $roleId): array
    {
        $stmt = self::db()->prepare(
            'SELECT p.slug
             FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = :role_id
             ORDER BY p.slug'
        );
        $stmt->execute(['role_id' => $roleId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [], 'slug');
    }
}

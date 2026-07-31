<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Permission extends Model
{
    public static function all(): array
    {
        $stmt = self::db()->query('SELECT * FROM permissions ORDER BY module ASC, slug ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function groupedByModule(): array
    {
        $grouped = [];
        foreach (self::all() as $permission) {
            $grouped[$permission['module']][] = $permission;
        }
        return $grouped;
    }
}

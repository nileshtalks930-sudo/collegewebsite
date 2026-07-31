<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Menu extends Model
{
    public const POSITIONS = [
        'header' => 'Header',
        'footer' => 'Footer',
        'sidebar' => 'Sidebar',
    ];

    private const SELECT = 'SELECT m.*,
            p.title AS page_title,
            p.slug AS page_slug,
            parent.name AS parent_name
        FROM menus m
        LEFT JOIN pages p ON p.id = m.page_id
        LEFT JOIN menus parent ON parent.id = m.parent_id';

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare(self::SELECT . ' WHERE m.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function allByPosition(?string $position = null): array
    {
        $sql = self::SELECT;
        $params = [];

        if ($position !== null && $position !== '') {
            $sql .= ' WHERE m.position = :position';
            $params['position'] = $position;
        }

        $sql .= ' ORDER BY m.position ASC, m.sort_order ASC, m.id ASC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Flat list suitable for parent dropdown (exclude a node and its descendants). */
    public static function optionsForParent(?string $position = null, ?int $excludeId = null): array
    {
        $items = self::allByPosition($position);
        if ($excludeId === null) {
            return $items;
        }

        $exclude = self::collectDescendantIds($items, $excludeId);
        $exclude[$excludeId] = true;

        return array_values(array_filter(
            $items,
            static fn (array $row): bool => !isset($exclude[(int) $row['id']])
        ));
    }

    /** Nested tree grouped by position. */
    public static function treeByPosition(?string $position = null): array
    {
        $items = self::allByPosition($position);
        $grouped = [];

        foreach ($items as $item) {
            $grouped[$item['position']][] = $item;
        }

        $trees = [];
        foreach ($grouped as $pos => $rows) {
            $trees[$pos] = self::buildTree($rows);
        }

        return $trees;
    }

    public static function create(array $data): int
    {
        $order = $data['sort_order'] ?? self::nextOrder(
            (string) $data['position'],
            $data['parent_id'] !== null ? (int) $data['parent_id'] : null
        );

        $stmt = self::db()->prepare(
            'INSERT INTO menus
                (name, position, sort_order, parent_id, status, open_in_new_tab, link_type, url, page_id, created_at)
             VALUES
                (:name, :position, :sort_order, :parent_id, :status, :open_in_new_tab, :link_type, :url, :page_id, NOW())'
        );
        $stmt->execute([
            'name' => $data['name'],
            'position' => $data['position'],
            'sort_order' => (int) $order,
            'parent_id' => $data['parent_id'],
            'status' => (int) $data['status'],
            'open_in_new_tab' => (int) $data['open_in_new_tab'],
            'link_type' => $data['link_type'],
            'url' => $data['url'],
            'page_id' => $data['page_id'],
        ]);

        return (int) self::db()->lastInsertId();
    }

    public static function updateMenu(int $id, array $data): void
    {
        $stmt = self::db()->prepare(
            'UPDATE menus SET
                name = :name,
                position = :position,
                sort_order = :sort_order,
                parent_id = :parent_id,
                status = :status,
                open_in_new_tab = :open_in_new_tab,
                link_type = :link_type,
                url = :url,
                page_id = :page_id,
                updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'position' => $data['position'],
            'sort_order' => (int) $data['sort_order'],
            'parent_id' => $data['parent_id'],
            'status' => (int) $data['status'],
            'open_in_new_tab' => (int) $data['open_in_new_tab'],
            'link_type' => $data['link_type'],
            'url' => $data['url'],
            'page_id' => $data['page_id'],
        ]);
    }

    public static function deleteById(int $id): void
    {
        // Children become top-level (FK ON DELETE SET NULL); also re-parent explicitly
        $stmt = self::db()->prepare('UPDATE menus SET parent_id = NULL WHERE parent_id = :id');
        $stmt->execute(['id' => $id]);

        $stmt = self::db()->prepare('DELETE FROM menus WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * Persist drag-and-drop order.
     * @param list<array{id:int,parent_id:?int,sort_order:int}> $items
     */
    public static function reorder(array $items): void
    {
        $pdo = self::db();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'UPDATE menus SET parent_id = :parent_id, sort_order = :sort_order, updated_at = NOW() WHERE id = :id'
            );

            foreach ($items as $item) {
                $stmt->execute([
                    'id' => (int) $item['id'],
                    'parent_id' => $item['parent_id'],
                    'sort_order' => (int) $item['sort_order'],
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function nextOrder(string $position, ?int $parentId): int
    {
        if ($parentId === null) {
            $stmt = self::db()->prepare(
                'SELECT COALESCE(MAX(sort_order), -1) + 1
                 FROM menus WHERE position = :position AND parent_id IS NULL'
            );
            $stmt->execute(['position' => $position]);
        } else {
            $stmt = self::db()->prepare(
                'SELECT COALESCE(MAX(sort_order), -1) + 1
                 FROM menus WHERE position = :position AND parent_id = :parent_id'
            );
            $stmt->execute(['position' => $position, 'parent_id' => $parentId]);
        }

        return (int) $stmt->fetchColumn();
    }

    public static function resolveHref(array $menu): string
    {
        if (($menu['link_type'] ?? '') === 'page' && !empty($menu['page_slug'])) {
            return '/page/' . ltrim((string) $menu['page_slug'], '/');
        }

        return (string) ($menu['url'] ?? '#');
    }

    /** @param list<array<string,mixed>> $items */
    private static function buildTree(array $items, ?int $parentId = null): array
    {
        $branch = [];
        foreach ($items as $item) {
            $itemParent = $item['parent_id'] !== null ? (int) $item['parent_id'] : null;
            if ($itemParent === $parentId) {
                $children = self::buildTree($items, (int) $item['id']);
                $item['children'] = $children;
                $branch[] = $item;
            }
        }
        return $branch;
    }

    /** @param list<array<string,mixed>> $items @return array<int,bool> */
    private static function collectDescendantIds(array $items, int $rootId): array
    {
        $byParent = [];
        foreach ($items as $item) {
            $pid = $item['parent_id'] !== null ? (int) $item['parent_id'] : 0;
            $byParent[$pid][] = (int) $item['id'];
        }

        $found = [];
        $stack = $byParent[$rootId] ?? [];
        while ($stack !== []) {
            $id = array_pop($stack);
            if (isset($found[$id])) {
                continue;
            }
            $found[$id] = true;
            foreach ($byParent[$id] ?? [] as $child) {
                $stack[] = $child;
            }
        }

        return $found;
    }
}

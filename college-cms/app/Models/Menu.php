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

    /** Menu destination types shown in the admin form. */
    public const LINK_TYPES = [
        'page' => 'Internal Page',
        'department' => 'Department',
        'naac' => 'NAAC',
        'iqac' => 'IQAC',
        'external' => 'External URL',
        'downloads' => 'Downloads',
        'gallery' => 'Gallery',
        'custom' => 'Custom Link',
    ];

    private const SELECT = 'SELECT m.*,
            p.title AS page_title,
            p.slug AS page_slug,
            d.name AS department_name,
            d.slug AS department_slug,
            nc.heading AS naac_heading,
            nc.slug AS naac_slug,
            dl.title AS download_title,
            dl.slug AS download_slug,
            g.title AS gallery_title,
            g.slug AS gallery_slug,
            parent.name AS parent_name
        FROM menus m
        LEFT JOIN pages p ON p.id = m.page_id
        LEFT JOIN departments d ON d.id = m.department_id
        LEFT JOIN naac_criteria nc ON nc.id = m.naac_criterion_id
        LEFT JOIN downloads dl ON dl.id = m.download_id
        LEFT JOIN galleries g ON g.id = m.gallery_id
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

        $targets = self::normalizedTargets($data);

        $stmt = self::db()->prepare(
            'INSERT INTO menus
                (name, position, sort_order, parent_id, status, open_in_new_tab, link_type,
                 url, page_id, department_id, naac_criterion_id, iqac_section, download_id, gallery_id, created_at)
             VALUES
                (:name, :position, :sort_order, :parent_id, :status, :open_in_new_tab, :link_type,
                 :url, :page_id, :department_id, :naac_criterion_id, :iqac_section, :download_id, :gallery_id, NOW())'
        );
        $stmt->execute([
            'name' => $data['name'],
            'position' => $data['position'],
            'sort_order' => (int) $order,
            'parent_id' => $data['parent_id'],
            'status' => (int) $data['status'],
            'open_in_new_tab' => (int) $data['open_in_new_tab'],
            'link_type' => $targets['link_type'],
            'url' => $targets['url'],
            'page_id' => $targets['page_id'],
            'department_id' => $targets['department_id'],
            'naac_criterion_id' => $targets['naac_criterion_id'],
            'iqac_section' => $targets['iqac_section'],
            'download_id' => $targets['download_id'],
            'gallery_id' => $targets['gallery_id'],
        ]);

        $id = (int) self::db()->lastInsertId();
        self::syncPageMenuId($id, $targets['link_type'], $targets['page_id']);

        return $id;
    }

    public static function updateMenu(int $id, array $data): void
    {
        $targets = self::normalizedTargets($data);

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
                department_id = :department_id,
                naac_criterion_id = :naac_criterion_id,
                iqac_section = :iqac_section,
                download_id = :download_id,
                gallery_id = :gallery_id,
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
            'link_type' => $targets['link_type'],
            'url' => $targets['url'],
            'page_id' => $targets['page_id'],
            'department_id' => $targets['department_id'],
            'naac_criterion_id' => $targets['naac_criterion_id'],
            'iqac_section' => $targets['iqac_section'],
            'download_id' => $targets['download_id'],
            'gallery_id' => $targets['gallery_id'],
        ]);

        self::syncPageMenuId($id, $targets['link_type'], $targets['page_id']);
    }

    public static function deleteById(int $id): void
    {
        $stmt = self::db()->prepare('UPDATE pages SET menu_id = NULL WHERE menu_id = :id');
        $stmt->execute(['id' => $id]);

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
        $type = (string) ($menu['link_type'] ?? 'custom');

        return match ($type) {
            'page' => !empty($menu['page_slug'])
                ? '/page/' . ltrim((string) $menu['page_slug'], '/')
                : '#',
            'department' => !empty($menu['department_slug'])
                ? Department::publicUrl((string) $menu['department_slug'])
                : '#',
            'naac' => !empty($menu['naac_slug'])
                ? NaacCriterion::publicUrl(['slug' => $menu['naac_slug']])
                : '/naac',
            'iqac' => !empty($menu['iqac_section'])
                ? '/iqac/' . ltrim((string) $menu['iqac_section'], '/')
                : '/iqac',
            'downloads' => Download::publicUrl(
                !empty($menu['download_slug']) ? (string) $menu['download_slug'] : null
            ),
            'gallery' => Gallery::publicUrl(
                !empty($menu['gallery_slug']) ? (string) $menu['gallery_slug'] : null
            ),
            'external', 'custom', 'url' => (string) ($menu['url'] ?? '#'),
            default => (string) ($menu['url'] ?? '#'),
        };
    }

    public static function linkBadge(array $menu): string
    {
        $type = (string) ($menu['link_type'] ?? 'custom');
        $label = self::LINK_TYPES[$type] ?? ucfirst($type);

        $detail = match ($type) {
            'page' => (string) ($menu['page_title'] ?? ''),
            'department' => (string) ($menu['department_name'] ?? ''),
            'naac' => (string) ($menu['naac_heading'] ?? 'All criteria'),
            'iqac' => !empty($menu['iqac_section'])
                ? (Iqac::SECTIONS[$menu['iqac_section']]['label'] ?? (string) $menu['iqac_section'])
                : 'Home',
            'downloads' => (string) ($menu['download_title'] ?? 'All downloads'),
            'gallery' => (string) ($menu['gallery_title'] ?? 'All galleries'),
            'external', 'custom', 'url' => (string) ($menu['url'] ?? ''),
            default => (string) ($menu['url'] ?? ''),
        };

        return $detail !== '' ? $label . ': ' . $detail : $label;
    }

    /**
     * Keep only the fields that apply to the chosen link_type.
     *
     * @param array<string,mixed> $data
     * @return array{
     *   link_type:string,url:?string,page_id:?int,department_id:?int,
     *   naac_criterion_id:?int,iqac_section:?string,download_id:?int,gallery_id:?int
     * }
     */
    public static function normalizedTargets(array $data): array
    {
        $type = (string) ($data['link_type'] ?? 'custom');
        if ($type === 'url') {
            $type = preg_match('#^https?://#i', (string) ($data['url'] ?? '')) ? 'external' : 'custom';
        }
        if (!isset(self::LINK_TYPES[$type])) {
            $type = 'custom';
        }

        $blank = [
            'link_type' => $type,
            'url' => null,
            'page_id' => null,
            'department_id' => null,
            'naac_criterion_id' => null,
            'iqac_section' => null,
            'download_id' => null,
            'gallery_id' => null,
        ];

        return match ($type) {
            'page' => array_merge($blank, [
                'page_id' => $data['page_id'] !== null ? (int) $data['page_id'] : null,
            ]),
            'department' => array_merge($blank, [
                'department_id' => $data['department_id'] !== null ? (int) $data['department_id'] : null,
            ]),
            'naac' => array_merge($blank, [
                'naac_criterion_id' => $data['naac_criterion_id'] !== null ? (int) $data['naac_criterion_id'] : null,
            ]),
            'iqac' => array_merge($blank, [
                'iqac_section' => ($data['iqac_section'] ?? '') !== '' ? (string) $data['iqac_section'] : null,
            ]),
            'downloads' => array_merge($blank, [
                'download_id' => $data['download_id'] !== null ? (int) $data['download_id'] : null,
            ]),
            'gallery' => array_merge($blank, [
                'gallery_id' => $data['gallery_id'] !== null ? (int) $data['gallery_id'] : null,
            ]),
            'external', 'custom' => array_merge($blank, [
                'url' => ($data['url'] ?? '') !== '' ? (string) $data['url'] : null,
            ]),
            default => array_merge($blank, [
                'url' => ($data['url'] ?? '') !== '' ? (string) $data['url'] : null,
            ]),
        };
    }

    private static function syncPageMenuId(int $menuId, string $linkType, ?int $pageId): void
    {
        $pdo = self::db();

        $stmt = $pdo->prepare('UPDATE pages SET menu_id = NULL WHERE menu_id = :menu_id');
        $stmt->execute(['menu_id' => $menuId]);

        if ($linkType === 'page' && $pageId !== null) {
            $stmt = $pdo->prepare('UPDATE pages SET menu_id = :menu_id WHERE id = :page_id');
            $stmt->execute(['menu_id' => $menuId, 'page_id' => $pageId]);
        }
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

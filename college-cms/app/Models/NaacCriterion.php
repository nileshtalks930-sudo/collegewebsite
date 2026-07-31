<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Helpers\Uploader;
use PDO;

final class NaacCriterion extends Model
{
    public static function all(): array
    {
        $sql = 'SELECT c.*,
                    (SELECT COUNT(*) FROM naac_files f WHERE f.criterion_id = c.id) AS files_count,
                    (SELECT COUNT(*) FROM naac_links l WHERE l.criterion_id = c.id) AS links_count,
                    (SELECT COUNT(*) FROM naac_tables t WHERE t.criterion_id = c.id) AS tables_count,
                    (SELECT COUNT(*) FROM naac_images i WHERE i.criterion_id = c.id) AS images_count,
                    (SELECT COUNT(*) FROM naac_pages p WHERE p.criterion_id = c.id) AS pages_count
                FROM naac_criteria c
                ORDER BY c.number ASC';
        return self::db()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM naac_criteria WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findByNumber(int $number): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM naac_criteria WHERE number = :number LIMIT 1');
        $stmt->execute(['number' => $number]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function updateCriterion(int $id, array $data): void
    {
        $stmt = self::db()->prepare(
            'UPDATE naac_criteria SET
                heading = :heading,
                description = :description,
                slug = :slug,
                status = :status,
                updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'heading' => $data['heading'],
            'description' => $data['description'],
            'slug' => $data['slug'],
            'status' => (int) $data['status'],
        ]);
    }

    public static function withRelations(int $id): ?array
    {
        $criterion = self::findById($id);
        if ($criterion === null) {
            return null;
        }

        $criterion['files'] = self::files($id);
        $criterion['links'] = self::links($id);
        $criterion['tables'] = self::tables($id);
        $criterion['images'] = self::images($id);
        $criterion['pages'] = self::pages($id);

        return $criterion;
    }

    public static function files(int $criterionId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM naac_files WHERE criterion_id = :id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['id' => $criterionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function links(int $criterionId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM naac_links WHERE criterion_id = :id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['id' => $criterionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function tables(int $criterionId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM naac_tables WHERE criterion_id = :id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['id' => $criterionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function images(int $criterionId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM naac_images WHERE criterion_id = :id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['id' => $criterionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function pages(int $criterionId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM naac_pages WHERE criterion_id = :id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['id' => $criterionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findPage(int $criterionId, int $pageId): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM naac_pages WHERE id = :id AND criterion_id = :criterion_id LIMIT 1'
        );
        $stmt->execute(['id' => $pageId, 'criterion_id' => $criterionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function slugify(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        return $slug !== '' ? $slug : 'item';
    }

    public static function uniqueCriterionSlug(string $base, ?int $exceptId = null): string
    {
        $slug = self::slugify($base);
        $candidate = $slug;
        $i = 2;
        while (self::criterionSlugExists($candidate, $exceptId)) {
            $candidate = $slug . '-' . $i;
            $i++;
        }
        return $candidate;
    }

    public static function criterionSlugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM naac_criteria WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function uniquePageSlug(int $criterionId, string $base, ?int $exceptId = null): string
    {
        $slug = self::slugify($base);
        $candidate = $slug;
        $i = 2;
        while (self::pageSlugExists($criterionId, $candidate, $exceptId)) {
            $candidate = $slug . '-' . $i;
            $i++;
        }
        return $candidate;
    }

    public static function pageSlugExists(int $criterionId, string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM naac_pages WHERE criterion_id = :criterion_id AND slug = :slug';
        $params = ['criterion_id' => $criterionId, 'slug' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function addFile(int $criterionId, string $title, string $path, int $sortOrder = 0): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO naac_files (criterion_id, title, file_path, sort_order, created_at)
             VALUES (:criterion_id, :title, :file_path, :sort_order, NOW())'
        );
        $stmt->execute([
            'criterion_id' => $criterionId,
            'title' => $title,
            'file_path' => $path,
            'sort_order' => $sortOrder,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function deleteFile(int $criterionId, int $fileId): void
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM naac_files WHERE id = :id AND criterion_id = :criterion_id LIMIT 1'
        );
        $stmt->execute(['id' => $fileId, 'criterion_id' => $criterionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }
        Uploader::deletePublic($row['file_path'] ?? null);
        $del = self::db()->prepare(
            'DELETE FROM naac_files WHERE id = :id AND criterion_id = :criterion_id'
        );
        $del->execute(['id' => $fileId, 'criterion_id' => $criterionId]);
    }

    public static function syncLinks(int $criterionId, array $rows): void
    {
        $existing = self::links($criterionId);
        $keep = [];

        foreach ($rows as $index => $row) {
            $id = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : 0;
            $payload = [
                'title' => $row['title'],
                'url' => $row['url'],
                'open_in_new_tab' => (int) ($row['open_in_new_tab'] ?? 1),
                'sort_order' => $index,
            ];

            if ($id > 0) {
                $stmt = self::db()->prepare(
                    'UPDATE naac_links SET title = :title, url = :url, open_in_new_tab = :open_in_new_tab, sort_order = :sort_order
                     WHERE id = :id AND criterion_id = :criterion_id'
                );
                $stmt->execute($payload + ['id' => $id, 'criterion_id' => $criterionId]);
                $keep[] = $id;
            } else {
                $stmt = self::db()->prepare(
                    'INSERT INTO naac_links (criterion_id, title, url, open_in_new_tab, sort_order, created_at)
                     VALUES (:criterion_id, :title, :url, :open_in_new_tab, :sort_order, NOW())'
                );
                $stmt->execute($payload + ['criterion_id' => $criterionId]);
                $keep[] = (int) self::db()->lastInsertId();
            }
        }

        foreach ($existing as $old) {
            if (!in_array((int) $old['id'], $keep, true)) {
                $stmt = self::db()->prepare(
                    'DELETE FROM naac_links WHERE id = :id AND criterion_id = :criterion_id'
                );
                $stmt->execute(['id' => (int) $old['id'], 'criterion_id' => $criterionId]);
            }
        }
    }

    public static function syncTables(int $criterionId, array $rows): void
    {
        $existing = self::tables($criterionId);
        $keep = [];

        foreach ($rows as $index => $row) {
            $id = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : 0;
            $payload = [
                'title' => $row['title'],
                'table_html' => $row['table_html'],
                'sort_order' => $index,
            ];

            if ($id > 0) {
                $stmt = self::db()->prepare(
                    'UPDATE naac_tables SET title = :title, table_html = :table_html, sort_order = :sort_order, updated_at = NOW()
                     WHERE id = :id AND criterion_id = :criterion_id'
                );
                $stmt->execute($payload + ['id' => $id, 'criterion_id' => $criterionId]);
                $keep[] = $id;
            } else {
                $stmt = self::db()->prepare(
                    'INSERT INTO naac_tables (criterion_id, title, table_html, sort_order, created_at)
                     VALUES (:criterion_id, :title, :table_html, :sort_order, NOW())'
                );
                $stmt->execute($payload + ['criterion_id' => $criterionId]);
                $keep[] = (int) self::db()->lastInsertId();
            }
        }

        foreach ($existing as $old) {
            if (!in_array((int) $old['id'], $keep, true)) {
                $stmt = self::db()->prepare(
                    'DELETE FROM naac_tables WHERE id = :id AND criterion_id = :criterion_id'
                );
                $stmt->execute(['id' => (int) $old['id'], 'criterion_id' => $criterionId]);
            }
        }
    }

    public static function addImage(int $criterionId, ?string $title, string $path, ?string $caption, int $sortOrder = 0): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO naac_images (criterion_id, title, image_path, caption, sort_order, created_at)
             VALUES (:criterion_id, :title, :image_path, :caption, :sort_order, NOW())'
        );
        $stmt->execute([
            'criterion_id' => $criterionId,
            'title' => $title,
            'image_path' => $path,
            'caption' => $caption,
            'sort_order' => $sortOrder,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function deleteImage(int $criterionId, int $imageId): void
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM naac_images WHERE id = :id AND criterion_id = :criterion_id LIMIT 1'
        );
        $stmt->execute(['id' => $imageId, 'criterion_id' => $criterionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }
        Uploader::deletePublic($row['image_path'] ?? null);
        $del = self::db()->prepare(
            'DELETE FROM naac_images WHERE id = :id AND criterion_id = :criterion_id'
        );
        $del->execute(['id' => $imageId, 'criterion_id' => $criterionId]);
    }

    public static function createPage(int $criterionId, array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO naac_pages (criterion_id, title, slug, content, status, sort_order, created_at)
             VALUES (:criterion_id, :title, :slug, :content, :status, :sort_order, NOW())'
        );
        $stmt->execute([
            'criterion_id' => $criterionId,
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'],
            'status' => (int) $data['status'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function updatePage(int $criterionId, int $pageId, array $data): void
    {
        $stmt = self::db()->prepare(
            'UPDATE naac_pages SET
                title = :title, slug = :slug, content = :content, status = :status, updated_at = NOW()
             WHERE id = :id AND criterion_id = :criterion_id'
        );
        $stmt->execute([
            'id' => $pageId,
            'criterion_id' => $criterionId,
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'],
            'status' => (int) $data['status'],
        ]);
    }

    public static function deletePage(int $criterionId, int $pageId): void
    {
        $stmt = self::db()->prepare(
            'DELETE FROM naac_pages WHERE id = :id AND criterion_id = :criterion_id'
        );
        $stmt->execute(['id' => $pageId, 'criterion_id' => $criterionId]);
    }

    public static function publicUrl(array $criterion): string
    {
        return '/naac/' . ltrim((string) $criterion['slug'], '/');
    }

    public static function pagePublicUrl(array $criterion, array $page): string
    {
        return self::publicUrl($criterion) . '/' . ltrim((string) $page['slug'], '/');
    }
}

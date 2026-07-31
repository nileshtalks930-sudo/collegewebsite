<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class Page extends Model
{
    private const SELECT = 'SELECT p.*,
            m.name AS menu_name,
            m.position AS menu_position
        FROM pages p
        LEFT JOIN menus m ON m.id = p.menu_id';

    public static function all(?string $search = null): array
    {
        $sql = self::SELECT;
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= ' WHERE (p.title LIKE :q OR p.slug LIKE :q2 OR p.meta_keywords LIKE :q3)';
            $like = '%' . $search . '%';
            $params = ['q' => $like, 'q2' => $like, 'q3' => $like];
        }

        $sql .= ' ORDER BY p.updated_at IS NULL, p.updated_at DESC, p.id DESC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function allPublished(): array
    {
        $stmt = self::db()->query(
            'SELECT id, title, slug FROM pages WHERE status = 1 ORDER BY title ASC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare(self::SELECT . ' WHERE p.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = self::db()->prepare(self::SELECT . ' WHERE p.slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM pages WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO pages
                (title, slug, content, menu_id, meta_title, meta_description, meta_keywords,
                 featured_image, pdf_attachment, status, created_at)
             VALUES
                (:title, :slug, :content, :menu_id, :meta_title, :meta_description, :meta_keywords,
                 :featured_image, :pdf_attachment, :status, NOW())'
        );
        $stmt->execute([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'],
            'menu_id' => $data['menu_id'],
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'meta_keywords' => $data['meta_keywords'],
            'featured_image' => $data['featured_image'],
            'pdf_attachment' => $data['pdf_attachment'],
            'status' => (int) $data['status'],
        ]);

        return (int) self::db()->lastInsertId();
    }

    public static function updatePage(int $id, array $data): void
    {
        $stmt = self::db()->prepare(
            'UPDATE pages SET
                title = :title,
                slug = :slug,
                content = :content,
                menu_id = :menu_id,
                meta_title = :meta_title,
                meta_description = :meta_description,
                meta_keywords = :meta_keywords,
                featured_image = :featured_image,
                pdf_attachment = :pdf_attachment,
                status = :status,
                updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'],
            'menu_id' => $data['menu_id'],
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'meta_keywords' => $data['meta_keywords'],
            'featured_image' => $data['featured_image'],
            'pdf_attachment' => $data['pdf_attachment'],
            'status' => (int) $data['status'],
        ]);
    }

    public static function deleteById(int $id): void
    {
        $stmt = self::db()->prepare('UPDATE menus SET page_id = NULL WHERE page_id = :id');
        $stmt->execute(['id' => $id]);

        $stmt = self::db()->prepare('DELETE FROM pages WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** Keep menus.page_id in sync with pages.menu_id selection. */
    public static function syncMenuLink(int $pageId, ?int $menuId): void
    {
        $pdo = self::db();

        if ($menuId === null) {
            $stmt = $pdo->prepare('UPDATE menus SET page_id = NULL WHERE page_id = :page_id');
            $stmt->execute(['page_id' => $pageId]);
            return;
        }

        $stmt = $pdo->prepare(
            'UPDATE menus SET page_id = NULL WHERE page_id = :page_id AND id <> :menu_id'
        );
        $stmt->execute(['page_id' => $pageId, 'menu_id' => $menuId]);

        $stmt = $pdo->prepare(
            'UPDATE menus
             SET page_id = :page_id,
                 link_type = \'page\',
                 url = NULL,
                 department_id = NULL,
                 naac_criterion_id = NULL,
                 iqac_section = NULL,
                 download_id = NULL,
                 gallery_id = NULL,
                 updated_at = NOW()
             WHERE id = :menu_id'
        );
        $stmt->execute(['page_id' => $pageId, 'menu_id' => $menuId]);
    }

    public static function slugify(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        return $slug !== '' ? $slug : 'page';
    }

    public static function uniqueSlug(string $base, ?int $exceptId = null): string
    {
        $slug = self::slugify($base);
        $candidate = $slug;
        $i = 2;
        while (self::slugExists($candidate, $exceptId)) {
            $candidate = $slug . '-' . $i;
            $i++;
        }
        return $candidate;
    }
}

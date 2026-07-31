<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Helpers\Uploader;
use PDO;

final class Department extends Model
{
    public static function all(?string $search = null): array
    {
        $sql = 'SELECT d.*,
                    (SELECT COUNT(*) FROM department_faculty f WHERE f.department_id = d.id) AS faculty_count,
                    (SELECT COUNT(*) FROM department_gallery g WHERE g.department_id = d.id) AS gallery_count,
                    (SELECT COUNT(*) FROM department_downloads dl WHERE dl.department_id = d.id) AS downloads_count
                FROM departments d';
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= ' WHERE (d.name LIKE :q OR d.slug LIKE :q2 OR d.head_name LIKE :q3)';
            $like = '%' . $search . '%';
            $params = ['q' => $like, 'q2' => $like, 'q3' => $like];
        }

        $sql .= ' ORDER BY d.name ASC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM departments WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM departments WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM departments WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function slugify(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        return $slug !== '' ? $slug : 'department';
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

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO departments
                (name, slug, head_name, head_designation, head_photo, description,
                 contact_email, contact_phone, contact_address, status, created_at)
             VALUES
                (:name, :slug, :head_name, :head_designation, :head_photo, :description,
                 :contact_email, :contact_phone, :contact_address, :status, NOW())'
        );
        $stmt->execute([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'head_name' => $data['head_name'],
            'head_designation' => $data['head_designation'],
            'head_photo' => $data['head_photo'],
            'description' => $data['description'],
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'],
            'contact_address' => $data['contact_address'],
            'status' => (int) $data['status'],
        ]);

        return (int) self::db()->lastInsertId();
    }

    public static function updateDepartment(int $id, array $data): void
    {
        $stmt = self::db()->prepare(
            'UPDATE departments SET
                name = :name,
                slug = :slug,
                head_name = :head_name,
                head_designation = :head_designation,
                head_photo = :head_photo,
                description = :description,
                contact_email = :contact_email,
                contact_phone = :contact_phone,
                contact_address = :contact_address,
                status = :status,
                updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'head_name' => $data['head_name'],
            'head_designation' => $data['head_designation'],
            'head_photo' => $data['head_photo'],
            'description' => $data['description'],
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'],
            'contact_address' => $data['contact_address'],
            'status' => (int) $data['status'],
        ]);
    }

    public static function deleteById(int $id): void
    {
        $dept = self::findById($id);
        if ($dept === null) {
            return;
        }

        foreach (self::faculty($id) as $row) {
            Uploader::deletePublic($row['photo'] ?? null);
        }
        foreach (self::downloads($id) as $row) {
            Uploader::deletePublic($row['file_path'] ?? null);
        }
        foreach (self::gallery($id) as $row) {
            Uploader::deletePublic($row['image_path'] ?? null);
        }
        Uploader::deletePublic($dept['head_photo'] ?? null);

        $stmt = self::db()->prepare('DELETE FROM departments WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function faculty(int $departmentId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM department_faculty WHERE department_id = :id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['id' => $departmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function achievements(int $departmentId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM department_achievements WHERE department_id = :id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['id' => $departmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function downloads(int $departmentId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM department_downloads WHERE department_id = :id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['id' => $departmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function gallery(int $departmentId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM department_gallery WHERE department_id = :id ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['id' => $departmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function withRelations(int $id): ?array
    {
        $dept = self::findById($id);
        if ($dept === null) {
            return null;
        }

        $dept['faculty'] = self::faculty($id);
        $dept['achievements'] = self::achievements($id);
        $dept['downloads'] = self::downloads($id);
        $dept['gallery'] = self::gallery($id);

        return $dept;
    }

    /** Replace faculty list for a department. */
    public static function syncFaculty(int $departmentId, array $rows): void
    {
        $existing = self::faculty($departmentId);
        $keepIds = [];

        foreach ($rows as $index => $row) {
            $id = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : 0;
            $payload = [
                'name' => $row['name'],
                'designation' => $row['designation'] ?? null,
                'email' => $row['email'] ?? null,
                'phone' => $row['phone'] ?? null,
                'photo' => $row['photo'] ?? null,
                'bio' => $row['bio'] ?? null,
                'sort_order' => $index,
            ];

            if ($id > 0) {
                if ($payload['photo'] === null) {
                    unset($payload['photo']);
                    $stmt = self::db()->prepare(
                        'UPDATE department_faculty SET
                            name = :name, designation = :designation, email = :email, phone = :phone,
                            bio = :bio, sort_order = :sort_order
                         WHERE id = :id AND department_id = :department_id'
                    );
                    $stmt->execute([
                        'name' => $payload['name'],
                        'designation' => $payload['designation'],
                        'email' => $payload['email'],
                        'phone' => $payload['phone'],
                        'bio' => $payload['bio'],
                        'sort_order' => $payload['sort_order'],
                        'id' => $id,
                        'department_id' => $departmentId,
                    ]);
                } else {
                    $stmt = self::db()->prepare(
                        'UPDATE department_faculty SET
                            name = :name, designation = :designation, email = :email, phone = :phone,
                            photo = :photo, bio = :bio, sort_order = :sort_order
                         WHERE id = :id AND department_id = :department_id'
                    );
                    $stmt->execute($payload + ['id' => $id, 'department_id' => $departmentId]);
                }
                $keepIds[] = $id;
            } else {
                $stmt = self::db()->prepare(
                    'INSERT INTO department_faculty
                        (department_id, name, designation, email, phone, photo, bio, sort_order, created_at)
                     VALUES
                        (:department_id, :name, :designation, :email, :phone, :photo, :bio, :sort_order, NOW())'
                );
                $stmt->execute($payload + ['department_id' => $departmentId]);
                $keepIds[] = (int) self::db()->lastInsertId();
            }
        }

        foreach ($existing as $old) {
            if (!in_array((int) $old['id'], $keepIds, true)) {
                Uploader::deletePublic($old['photo'] ?? null);
                $stmt = self::db()->prepare(
                    'DELETE FROM department_faculty WHERE id = :id AND department_id = :department_id'
                );
                $stmt->execute(['id' => (int) $old['id'], 'department_id' => $departmentId]);
            }
        }
    }

    public static function syncAchievements(int $departmentId, array $rows): void
    {
        $existing = self::achievements($departmentId);
        $keepIds = [];

        foreach ($rows as $index => $row) {
            $id = isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : 0;
            $payload = [
                'title' => $row['title'],
                'description' => $row['description'] ?? null,
                'achieved_on' => $row['achieved_on'] ?: null,
                'sort_order' => $index,
            ];

            if ($id > 0) {
                $stmt = self::db()->prepare(
                    'UPDATE department_achievements SET
                        title = :title, description = :description, achieved_on = :achieved_on, sort_order = :sort_order
                     WHERE id = :id AND department_id = :department_id'
                );
                $stmt->execute($payload + ['id' => $id, 'department_id' => $departmentId]);
                $keepIds[] = $id;
            } else {
                $stmt = self::db()->prepare(
                    'INSERT INTO department_achievements
                        (department_id, title, description, achieved_on, sort_order, created_at)
                     VALUES
                        (:department_id, :title, :description, :achieved_on, :sort_order, NOW())'
                );
                $stmt->execute($payload + ['department_id' => $departmentId]);
                $keepIds[] = (int) self::db()->lastInsertId();
            }
        }

        foreach ($existing as $old) {
            if (!in_array((int) $old['id'], $keepIds, true)) {
                $stmt = self::db()->prepare(
                    'DELETE FROM department_achievements WHERE id = :id AND department_id = :department_id'
                );
                $stmt->execute(['id' => (int) $old['id'], 'department_id' => $departmentId]);
            }
        }
    }

    public static function addDownload(int $departmentId, string $title, string $filePath, int $sortOrder = 0): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO department_downloads (department_id, title, file_path, sort_order, created_at)
             VALUES (:department_id, :title, :file_path, :sort_order, NOW())'
        );
        $stmt->execute([
            'department_id' => $departmentId,
            'title' => $title,
            'file_path' => $filePath,
            'sort_order' => $sortOrder,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function deleteDownload(int $departmentId, int $downloadId): void
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM department_downloads WHERE id = :id AND department_id = :department_id LIMIT 1'
        );
        $stmt->execute(['id' => $downloadId, 'department_id' => $departmentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }
        Uploader::deletePublic($row['file_path'] ?? null);
        $del = self::db()->prepare(
            'DELETE FROM department_downloads WHERE id = :id AND department_id = :department_id'
        );
        $del->execute(['id' => $downloadId, 'department_id' => $departmentId]);
    }

    public static function addGalleryImage(int $departmentId, ?string $title, string $imagePath, int $sortOrder = 0): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO department_gallery (department_id, title, image_path, sort_order, created_at)
             VALUES (:department_id, :title, :image_path, :sort_order, NOW())'
        );
        $stmt->execute([
            'department_id' => $departmentId,
            'title' => $title,
            'image_path' => $imagePath,
            'sort_order' => $sortOrder,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function deleteGalleryImage(int $departmentId, int $imageId): void
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM department_gallery WHERE id = :id AND department_id = :department_id LIMIT 1'
        );
        $stmt->execute(['id' => $imageId, 'department_id' => $departmentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }
        Uploader::deletePublic($row['image_path'] ?? null);
        $del = self::db()->prepare(
            'DELETE FROM department_gallery WHERE id = :id AND department_id = :department_id'
        );
        $del->execute(['id' => $imageId, 'department_id' => $departmentId]);
    }

    public static function publicUrl(string $slug): string
    {
        return '/department/' . ltrim($slug, '/');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Helpers\Uploader;
use PDO;

final class Media extends Model
{
    public const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip'];

    public const ALLOWED_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
        'application/zip',
        'application/x-zip-compressed',
        'multipart/x-zip',
    ];

    public const MAX_BYTES = 25 * 1024 * 1024;

    public static function folders(?int $parentId = null): array
    {
        if ($parentId === null) {
            $stmt = self::db()->query(
                'SELECT f.*,
                    (SELECT COUNT(*) FROM media_folders c WHERE c.parent_id = f.id) AS child_folders,
                    (SELECT COUNT(*) FROM media_files mf WHERE mf.folder_id = f.id) AS file_count
                 FROM media_folders f
                 WHERE f.parent_id IS NULL
                 ORDER BY f.name ASC'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        $stmt = self::db()->prepare(
            'SELECT f.*,
                (SELECT COUNT(*) FROM media_folders c WHERE c.parent_id = f.id) AS child_folders,
                (SELECT COUNT(*) FROM media_files mf WHERE mf.folder_id = f.id) AS file_count
             FROM media_folders f
             WHERE f.parent_id = :parent_id
             ORDER BY f.name ASC'
        );
        $stmt->execute(['parent_id' => $parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findFolder(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM media_folders WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function createFolder(string $name, ?int $parentId = null): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO media_folders (name, parent_id, created_at) VALUES (:name, :parent_id, NOW())'
        );
        $stmt->execute([
            'name' => $name,
            'parent_id' => $parentId,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function deleteFolder(int $id): void
    {
        // Delete files in this folder (and nested via recursive call)
        $children = self::folders($id);
        foreach ($children as $child) {
            self::deleteFolder((int) $child['id']);
        }

        foreach (self::filesInFolder($id) as $file) {
            self::deleteFile((int) $file['id']);
        }

        $stmt = self::db()->prepare('DELETE FROM media_folders WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function folderExists(string $name, ?int $parentId, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM media_folders WHERE name = :name AND ';
        $params = ['name' => $name];
        if ($parentId === null) {
            $sql .= 'parent_id IS NULL';
        } else {
            $sql .= 'parent_id = :parent_id';
            $params['parent_id'] = $parentId;
        }
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function breadcrumb(?int $folderId): array
    {
        $crumbs = [];
        $current = $folderId;
        while ($current !== null) {
            $folder = self::findFolder($current);
            if ($folder === null) {
                break;
            }
            array_unshift($crumbs, $folder);
            $current = $folder['parent_id'] !== null ? (int) $folder['parent_id'] : null;
        }
        return $crumbs;
    }

    public static function filesInFolder(?int $folderId, ?string $search = null): array
    {
        $sql = 'SELECT * FROM media_files WHERE ';
        $params = [];

        if ($folderId === null) {
            $sql .= 'folder_id IS NULL';
        } else {
            $sql .= 'folder_id = :folder_id';
            $params['folder_id'] = $folderId;
        }

        if ($search !== null && $search !== '') {
            $sql .= ' AND (original_name LIKE :q OR stored_name LIKE :q2 OR extension LIKE :q3)';
            $like = '%' . $search . '%';
            $params['q'] = $like;
            $params['q2'] = $like;
            $params['q3'] = $like;
        }

        $sql .= ' ORDER BY created_at DESC, id DESC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function searchAll(string $search): array
    {
        $stmt = self::db()->prepare(
            'SELECT mf.*, f.name AS folder_name
             FROM media_files mf
             LEFT JOIN media_folders f ON f.id = mf.folder_id
             WHERE mf.original_name LIKE :q OR mf.stored_name LIKE :q2 OR mf.extension LIKE :q3
             ORDER BY mf.created_at DESC
             LIMIT 200'
        );
        $like = '%' . $search . '%';
        $stmt->execute(['q' => $like, 'q2' => $like, 'q3' => $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function findFile(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM media_files WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function createFile(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO media_files
                (folder_id, original_name, stored_name, file_path, extension, mime_type, size_bytes, created_at)
             VALUES
                (:folder_id, :original_name, :stored_name, :file_path, :extension, :mime_type, :size_bytes, NOW())'
        );
        $stmt->execute([
            'folder_id' => $data['folder_id'],
            'original_name' => $data['original_name'],
            'stored_name' => $data['stored_name'],
            'file_path' => $data['file_path'],
            'extension' => $data['extension'],
            'mime_type' => $data['mime_type'],
            'size_bytes' => (int) $data['size_bytes'],
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function replaceFile(int $id, array $data): void
    {
        $existing = self::findFile($id);
        if ($existing === null) {
            return;
        }

        Uploader::deletePublic((string) $existing['file_path']);

        $stmt = self::db()->prepare(
            'UPDATE media_files SET
                original_name = :original_name,
                stored_name = :stored_name,
                file_path = :file_path,
                extension = :extension,
                mime_type = :mime_type,
                size_bytes = :size_bytes,
                updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'original_name' => $data['original_name'],
            'stored_name' => $data['stored_name'],
            'file_path' => $data['file_path'],
            'extension' => $data['extension'],
            'mime_type' => $data['mime_type'],
            'size_bytes' => (int) $data['size_bytes'],
        ]);
    }

    public static function deleteFile(int $id): void
    {
        $file = self::findFile($id);
        if ($file === null) {
            return;
        }
        Uploader::deletePublic((string) $file['file_path']);
        $stmt = self::db()->prepare('DELETE FROM media_files WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function storageSubdir(?int $folderId): string
    {
        return $folderId === null ? 'media/root' : 'media/folder_' . $folderId;
    }

    public static function isImage(string $extension): bool
    {
        return in_array(strtolower($extension), ['jpg', 'jpeg', 'png'], true);
    }

    public static function isPreviewable(string $extension): bool
    {
        $ext = strtolower($extension);
        return self::isImage($ext) || $ext === 'pdf';
    }

    public static function formatSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return round($bytes / 1048576, 1) . ' MB';
    }
}

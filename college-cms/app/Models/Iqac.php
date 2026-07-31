<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Helpers\Uploader;
use PDO;

/**
 * IQAC data access for committee profile and all section tables.
 */
final class Iqac extends Model
{
    public const SECTIONS = [
        'members' => [
            'label' => 'Members',
            'table' => 'iqac_members',
            'icon' => 'bi-people',
            'file_field' => 'photo',
            'file_subdir' => 'iqac/members',
            'file_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'file_mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'required' => ['name'],
            'fields' => ['name', 'designation', 'role', 'email', 'phone', 'sort_order'],
        ],
        'minutes' => [
            'label' => 'Minutes of Meetings',
            'table' => 'iqac_minutes',
            'icon' => 'bi-journal-text',
            'file_field' => 'file_path',
            'file_subdir' => 'iqac/minutes',
            'file_types' => ['pdf', 'doc', 'docx'],
            'file_mimes' => [],
            'required' => ['title'],
            'fields' => ['title', 'meeting_date', 'description'],
        ],
        'aqar' => [
            'label' => 'AQAR',
            'table' => 'iqac_aqar',
            'icon' => 'bi-file-earmark-bar-graph',
            'file_field' => 'file_path',
            'file_subdir' => 'iqac/aqar',
            'file_types' => ['pdf', 'doc', 'docx'],
            'file_mimes' => [],
            'required' => ['title'],
            'fields' => ['title', 'academic_year', 'description'],
        ],
        'ssr' => [
            'label' => 'SSR',
            'table' => 'iqac_ssr',
            'icon' => 'bi-file-earmark-richtext',
            'file_field' => 'file_path',
            'file_subdir' => 'iqac/ssr',
            'file_types' => ['pdf', 'doc', 'docx'],
            'file_mimes' => [],
            'required' => ['title'],
            'fields' => ['title', 'academic_year', 'description'],
        ],
        'downloads' => [
            'label' => 'Downloads',
            'table' => 'iqac_downloads',
            'icon' => 'bi-download',
            'file_field' => 'file_path',
            'file_subdir' => 'iqac/downloads',
            'file_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'],
            'file_mimes' => [],
            'file_required' => true,
            'required' => ['title'],
            'fields' => ['title', 'sort_order'],
        ],
        'notices' => [
            'label' => 'Notices',
            'table' => 'iqac_notices',
            'icon' => 'bi-megaphone',
            'file_field' => 'file_path',
            'file_subdir' => 'iqac/notices',
            'file_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
            'file_mimes' => [],
            'required' => ['title'],
            'fields' => ['title', 'notice_date', 'content'],
            'richtext' => ['content'],
        ],
        'gallery' => [
            'label' => 'Gallery',
            'table' => 'iqac_gallery',
            'icon' => 'bi-images',
            'file_field' => 'image_path',
            'file_subdir' => 'iqac/gallery',
            'file_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'file_mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'file_required' => true,
            'required' => [],
            'fields' => ['title', 'caption', 'sort_order'],
        ],
        'circulars' => [
            'label' => 'Circulars',
            'table' => 'iqac_circulars',
            'icon' => 'bi-envelope-paper',
            'file_field' => 'file_path',
            'file_subdir' => 'iqac/circulars',
            'file_types' => ['pdf', 'doc', 'docx'],
            'file_mimes' => [],
            'required' => ['title'],
            'fields' => ['title', 'reference_no', 'circular_date', 'description'],
        ],
        'documents' => [
            'label' => 'Documents',
            'table' => 'iqac_documents',
            'icon' => 'bi-folder2-open',
            'file_field' => 'file_path',
            'file_subdir' => 'iqac/documents',
            'file_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip'],
            'file_mimes' => [],
            'file_required' => true,
            'required' => ['title'],
            'fields' => ['title', 'category', 'sort_order'],
        ],
    ];

    public static function section(string $key): ?array
    {
        return self::SECTIONS[$key] ?? null;
    }

    public static function counts(): array
    {
        $counts = ['committee' => 1];
        foreach (self::SECTIONS as $key => $meta) {
            $table = $meta['table'];
            $counts[$key] = (int) self::db()->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        }
        return $counts;
    }

    public static function getCommittee(): array
    {
        $row = self::db()->query('SELECT * FROM iqac_committee ORDER BY id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }

        self::db()->exec(
            "INSERT INTO iqac_committee (title, description, status, created_at)
             VALUES ('Internal Quality Assurance Cell (IQAC)', '<p>IQAC</p>', 1, NOW())"
        );

        return self::getCommittee();
    }

    public static function updateCommittee(array $data): void
    {
        $committee = self::getCommittee();
        $stmt = self::db()->prepare(
            'UPDATE iqac_committee SET
                title = :title,
                description = :description,
                vision = :vision,
                mission = :mission,
                status = :status,
                updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => (int) $committee['id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'vision' => $data['vision'],
            'mission' => $data['mission'],
            'status' => (int) $data['status'],
        ]);
    }

    public static function all(string $section): array
    {
        $meta = self::section($section);
        if ($meta === null) {
            return [];
        }

        $table = $meta['table'];
        $order = in_array('sort_order', $meta['fields'], true)
            ? 'sort_order ASC, id DESC'
            : 'id DESC';

        return self::db()->query("SELECT * FROM `{$table}` ORDER BY {$order}")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function find(string $section, int $id): ?array
    {
        $meta = self::section($section);
        if ($meta === null) {
            return null;
        }

        $stmt = self::db()->prepare("SELECT * FROM `{$meta['table']}` WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(string $section, array $data): int
    {
        $meta = self::section($section);
        if ($meta === null) {
            throw new \InvalidArgumentException('Invalid IQAC section');
        }

        $cols = $meta['fields'];
        $fileField = $meta['file_field'] ?? null;
        if ($fileField) {
            $cols[] = $fileField;
        }
        if (self::tableHasStatus($meta['table'])) {
            $cols[] = 'status';
        }

        $cols = array_values(array_unique($cols));
        $placeholders = array_map(static fn (string $c): string => ':' . $c, $cols);

        $sql = sprintf(
            'INSERT INTO `%s` (%s, created_at) VALUES (%s, NOW())',
            $meta['table'],
            implode(', ', array_map(static fn ($c) => "`{$c}`", $cols)),
            implode(', ', $placeholders)
        );

        $params = [];
        foreach ($cols as $col) {
            $params[$col] = $data[$col] ?? null;
        }

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return (int) self::db()->lastInsertId();
    }

    public static function update(string $section, int $id, array $data): void
    {
        $meta = self::section($section);
        if ($meta === null) {
            throw new \InvalidArgumentException('Invalid IQAC section');
        }

        $cols = $meta['fields'];
        $fileField = $meta['file_field'] ?? null;
        if ($fileField && array_key_exists($fileField, $data)) {
            $cols[] = $fileField;
        }
        if (self::tableHasStatus($meta['table'])) {
            $cols[] = 'status';
        }
        $cols = array_values(array_unique($cols));

        $sets = [];
        $params = ['id' => $id];
        foreach ($cols as $col) {
            $sets[] = "`{$col}` = :{$col}";
            $params[$col] = $data[$col] ?? null;
        }

        $updated = self::tableHasUpdatedAt($meta['table']) ? ', updated_at = NOW()' : '';
        $sql = sprintf(
            'UPDATE `%s` SET %s%s WHERE id = :id',
            $meta['table'],
            implode(', ', $sets),
            $updated
        );

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
    }

    public static function delete(string $section, int $id): void
    {
        $meta = self::section($section);
        $row = self::find($section, $id);
        if ($meta === null || $row === null) {
            return;
        }

        $fileField = $meta['file_field'] ?? null;
        if ($fileField && !empty($row[$fileField])) {
            Uploader::deletePublic((string) $row[$fileField]);
        }

        $stmt = self::db()->prepare("DELETE FROM `{$meta['table']}` WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    private static function tableHasStatus(string $table): bool
    {
        return !in_array($table, [], true); // all IQAC section tables include status
    }

    private static function tableHasUpdatedAt(string $table): bool
    {
        return in_array($table, [
            'iqac_members',
            'iqac_minutes',
            'iqac_aqar',
            'iqac_ssr',
            'iqac_notices',
            'iqac_circulars',
        ], true);
    }
}

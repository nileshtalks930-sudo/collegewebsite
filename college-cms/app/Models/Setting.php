<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Helpers\Uploader;
use PDO;

final class Setting extends Model
{
    public const GROUPS = [
        'general' => 'General',
        'contact' => 'Contact',
        'social' => 'Social Links',
        'smtp' => 'SMTP',
        'analytics' => 'Analytics',
        'footer' => 'Footer',
    ];

    /** @var list<string> */
    public const KEYS = [
        'website_name',
        'logo',
        'favicon',
        'address',
        'email',
        'phone',
        'google_map',
        'social_facebook',
        'social_twitter',
        'social_instagram',
        'social_youtube',
        'social_linkedin',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'smtp_from_email',
        'smtp_from_name',
        'analytics_code',
        'footer_text',
        'copyright',
    ];

    /** @var array<string,string>|null */
    private static ?array $cache = null;

    /** @return array<string,string|null> key => value */
    public static function allKeyed(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $stmt = self::db()->query(
            'SELECT setting_key, setting_value FROM settings ORDER BY setting_group ASC, id ASC'
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row['setting_key']] = $row['setting_value'];
        }

        self::$cache = $out;
        return self::$cache;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = self::allKeyed();
        if (!array_key_exists($key, $all)) {
            return $default;
        }
        $value = $all[$key];
        return $value === null || $value === '' ? $default : (string) $value;
    }

    public static function set(string $key, ?string $value): void
    {
        $stmt = self::db()->prepare(
            'INSERT INTO settings (setting_key, setting_value, setting_group, created_at)
             VALUES (:key, :value, :group, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()'
        );
        $stmt->execute([
            'key' => $key,
            'value' => $value,
            'group' => self::groupForKey($key),
        ]);
        self::$cache = null;
    }

    /** @param array<string,?string> $values */
    public static function setMany(array $values): void
    {
        $pdo = self::db();
        $pdo->beginTransaction();
        try {
            foreach ($values as $key => $value) {
                if (!in_array($key, self::KEYS, true)) {
                    continue;
                }
                self::set($key, $value);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        self::$cache = null;
    }

    public static function groupForKey(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'social_') => 'social',
            str_starts_with($key, 'smtp_') => 'smtp',
            in_array($key, ['address', 'email', 'phone', 'google_map'], true) => 'contact',
            $key === 'analytics_code' => 'analytics',
            in_array($key, ['footer_text', 'copyright'], true) => 'footer',
            default => 'general',
        };
    }

    /**
     * Handle logo/favicon upload + optional removal.
     *
     * @return array{logo:?string,favicon:?string,error:?string}
     */
    public static function handleUploads(array $current): array
    {
        $logo = $current['logo'] ?? null;
        $favicon = $current['favicon'] ?? null;
        $error = null;

        if (!empty($_POST['remove_logo'])) {
            Uploader::deletePublic(is_string($logo) ? $logo : null);
            $logo = null;
        }
        if (!empty($_POST['remove_favicon'])) {
            Uploader::deletePublic(is_string($favicon) ? $favicon : null);
            $favicon = null;
        }

        if (isset($_FILES['logo'])) {
            $upload = Uploader::store(
                $_FILES['logo'],
                'settings',
                ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
                ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
                2 * 1024 * 1024
            );
            if (!$upload['skipped']) {
                if ($upload['error'] !== null || $upload['path'] === null) {
                    return ['logo' => is_string($logo) ? $logo : null, 'favicon' => is_string($favicon) ? $favicon : null, 'error' => $upload['error'] ?? 'Logo upload failed.'];
                }
                Uploader::deletePublic(is_string($logo) ? $logo : null);
                $logo = $upload['path'];
            }
        }

        if (isset($_FILES['favicon'])) {
            $upload = Uploader::store(
                $_FILES['favicon'],
                'settings',
                ['ico', 'png', 'gif', 'jpg', 'jpeg', 'webp', 'svg'],
                ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/gif', 'image/jpeg', 'image/webp', 'image/svg+xml'],
                512 * 1024
            );
            if (!$upload['skipped']) {
                if ($upload['error'] !== null || $upload['path'] === null) {
                    return ['logo' => is_string($logo) ? $logo : null, 'favicon' => is_string($favicon) ? $favicon : null, 'error' => $upload['error'] ?? 'Favicon upload failed.'];
                }
                Uploader::deletePublic(is_string($favicon) ? $favicon : null);
                $favicon = $upload['path'];
            }
        }

        return [
            'logo' => is_string($logo) ? $logo : null,
            'favicon' => is_string($favicon) ? $favicon : null,
            'error' => $error,
        ];
    }

    public static function clearCache(): void
    {
        self::$cache = null;
    }
}

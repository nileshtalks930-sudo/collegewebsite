<?php

declare(strict_types=1);

namespace App\Helpers;

final class Uploader
{
    /**
     * @param array{name?:string,type?:string,tmp_name?:string,error?:int,size?:int} $file
     * @param list<string> $allowedExtensions
     * @param list<string> $allowedMime
     */
    public static function store(
        array $file,
        string $subdir,
        array $allowedExtensions,
        array $allowedMime,
        int $maxBytes = 5242880
    ): array {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['path' => null, 'error' => null, 'skipped' => true];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['path' => null, 'error' => 'File upload failed.', 'skipped' => false];
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        $original = (string) ($file['name'] ?? '');
        $size = (int) ($file['size'] ?? 0);

        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return ['path' => null, 'error' => 'Invalid upload.', 'skipped' => false];
        }

        if ($size <= 0 || $size > $maxBytes) {
            return ['path' => null, 'error' => 'File exceeds the maximum allowed size.', 'skipped' => false];
        }

        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions, true)) {
            return ['path' => null, 'error' => 'File type not allowed.', 'skipped' => false];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        if ($allowedMime !== [] && !in_array($mime, $allowedMime, true)) {
            return ['path' => null, 'error' => 'Invalid file MIME type.', 'skipped' => false];
        }

        $dir = base_path('public/uploads/' . trim($subdir, '/'));
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ['path' => null, 'error' => 'Upload directory is not writable.', 'skipped' => false];
        }

        $filename = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $dir . '/' . $filename;

        if (!move_uploaded_file($tmp, $dest)) {
            return ['path' => null, 'error' => 'Could not save uploaded file.', 'skipped' => false];
        }

        return [
            'path' => 'uploads/' . trim($subdir, '/') . '/' . $filename,
            'error' => null,
            'skipped' => false,
        ];
    }

    public static function storeMany(
        array $files,
        string $subdir,
        array $allowedExtensions,
        array $allowedMime,
        int $maxBytes = 5242880
    ): array {
        $results = [];
        $names = $files['name'] ?? [];
        if (!is_array($names)) {
            $single = self::store($files, $subdir, $allowedExtensions, $allowedMime, $maxBytes);
            return [$single];
        }

        foreach ($names as $i => $name) {
            $file = [
                'name' => $name,
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$i] ?? 0,
            ];
            $results[] = self::store($file, $subdir, $allowedExtensions, $allowedMime, $maxBytes);
        }

        return $results;
    }

    public static function deletePublic(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        if (!str_starts_with($relativePath, 'uploads/')) {
            return;
        }

        $full = base_path('public/' . $relativePath);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}

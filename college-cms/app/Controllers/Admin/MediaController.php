<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Helpers\Uploader;
use App\Middleware\RoleMiddleware;
use App\Models\Media;

final class MediaController extends Controller
{
    public function index(): void
    {
        RoleMiddleware::permission('media.view');

        $folderId = isset($_GET['folder']) && $_GET['folder'] !== '' ? (int) $_GET['folder'] : null;
        $search = trim((string) ($_GET['q'] ?? ''));
        $currentFolder = null;

        if ($folderId !== null) {
            $currentFolder = Media::findFolder($folderId);
            if ($currentFolder === null) {
                Session::flash('error', 'Folder not found.');
                $this->redirect('media');
            }
        }

        if ($search !== '') {
            $files = Media::searchAll($search);
            $folders = [];
        } else {
            $folders = Media::folders($folderId);
            $files = Media::filesInFolder($folderId);
        }

        $this->view('admin.media.index', [
            'title' => 'File Manager',
            'folders' => $folders,
            'files' => $files,
            'currentFolder' => $currentFolder,
            'folderId' => $folderId,
            'breadcrumb' => Media::breadcrumb($folderId),
            'search' => $search,
            'success' => flash('success'),
            'error' => flash('error'),
            'canManage' => Auth::can('media.manage'),
            'allowed' => Media::ALLOWED_EXTENSIONS,
            'csrfToken' => Csrf::token(),
        ], 'admin.layouts.app');
    }

    public function createFolder(): void
    {
        RoleMiddleware::permission('media.manage');
        $this->validateCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int) $_POST['parent_id'] : null;

        if ($name === '' || mb_strlen($name) > 150) {
            Session::flash('error', 'Folder name is required (max 150 characters).');
            $this->redirect($this->folderRedirect($parentId));
        }

        if (!preg_match('/^[\w\s.\-()]+$/u', $name)) {
            Session::flash('error', 'Folder name contains invalid characters.');
            $this->redirect($this->folderRedirect($parentId));
        }

        if ($parentId !== null && Media::findFolder($parentId) === null) {
            Session::flash('error', 'Parent folder not found.');
            $this->redirect('media');
        }

        if (Media::folderExists($name, $parentId)) {
            Session::flash('error', 'A folder with that name already exists here.');
            $this->redirect($this->folderRedirect($parentId));
        }

        Media::createFolder($name, $parentId);
        Session::flash('success', 'Folder created.');
        $this->redirect($this->folderRedirect($parentId));
    }

    public function deleteFolder(string $id): void
    {
        RoleMiddleware::permission('media.manage');
        $this->validateCsrf();

        $folder = Media::findFolder((int) $id);
        if ($folder === null) {
            Session::flash('error', 'Folder not found.');
            $this->redirect('media');
        }

        $parentId = $folder['parent_id'] !== null ? (int) $folder['parent_id'] : null;
        Media::deleteFolder((int) $id);
        Session::flash('success', 'Folder and its contents deleted.');
        $this->redirect($this->folderRedirect($parentId));
    }

    public function upload(): void
    {
        RoleMiddleware::permission('media.manage');
        $this->validateCsrf();

        $folderId = isset($_POST['folder_id']) && $_POST['folder_id'] !== '' ? (int) $_POST['folder_id'] : null;
        if ($folderId !== null && Media::findFolder($folderId) === null) {
            Session::flash('error', 'Target folder not found.');
            $this->redirect('media');
        }

        $uploads = Uploader::storeMany(
            $_FILES['files'] ?? [],
            Media::storageSubdir($folderId),
            Media::ALLOWED_EXTENSIONS,
            Media::ALLOWED_MIMES,
            Media::MAX_BYTES
        );

        $saved = 0;
        $errors = [];
        $names = $_FILES['files']['name'] ?? [];
        if (!is_array($names)) {
            $names = [$names];
        }

        foreach ($uploads as $i => $upload) {
            $original = is_array($names) ? (string) ($names[$i] ?? 'file') : 'file';
            if ($upload['skipped']) {
                continue;
            }
            if ($upload['error'] !== null || $upload['path'] === null) {
                $errors[] = $original . ': ' . ($upload['error'] ?? 'failed');
                continue;
            }

            $full = base_path('public/' . ltrim($upload['path'], '/'));
            $ext = strtolower(pathinfo($upload['path'], PATHINFO_EXTENSION));
            $mime = is_file($full) ? (new \finfo(FILEINFO_MIME_TYPE))->file($full) : null;

            Media::createFile([
                'folder_id' => $folderId,
                'original_name' => $original,
                'stored_name' => basename($upload['path']),
                'file_path' => $upload['path'],
                'extension' => $ext,
                'mime_type' => $mime,
                'size_bytes' => is_file($full) ? (int) filesize($full) : 0,
            ]);
            $saved++;
        }

        if ($saved > 0) {
            Session::flash('success', $saved . ' file(s) uploaded successfully.');
        }
        if ($errors !== []) {
            Session::flash('error', implode(' ', $errors));
        }
        if ($saved === 0 && $errors === []) {
            Session::flash('error', 'No files were selected.');
        }

        $this->redirect($this->folderRedirect($folderId));
    }

    public function replace(string $id): void
    {
        RoleMiddleware::permission('media.manage');
        $this->validateCsrf();

        $file = Media::findFile((int) $id);
        if ($file === null) {
            Session::flash('error', 'File not found.');
            $this->redirect('media');
        }

        $folderId = $file['folder_id'] !== null ? (int) $file['folder_id'] : null;
        $upload = Uploader::store(
            $_FILES['file'] ?? [],
            Media::storageSubdir($folderId),
            Media::ALLOWED_EXTENSIONS,
            Media::ALLOWED_MIMES,
            Media::MAX_BYTES
        );

        if ($upload['skipped'] || $upload['path'] === null || $upload['error'] !== null) {
            Session::flash('error', $upload['error'] ?? 'Please choose a file to replace with.');
            $this->redirect($this->folderRedirect($folderId));
        }

        $full = base_path('public/' . ltrim($upload['path'], '/'));
        $ext = strtolower(pathinfo($upload['path'], PATHINFO_EXTENSION));
        $mime = is_file($full) ? (new \finfo(FILEINFO_MIME_TYPE))->file($full) : null;
        $original = (string) ($_FILES['file']['name'] ?? $file['original_name']);

        Media::replaceFile((int) $id, [
            'original_name' => $original,
            'stored_name' => basename($upload['path']),
            'file_path' => $upload['path'],
            'extension' => $ext,
            'mime_type' => $mime,
            'size_bytes' => is_file($full) ? (int) filesize($full) : 0,
        ]);

        Session::flash('success', 'File replaced successfully.');
        $this->redirect($this->folderRedirect($folderId));
    }

    public function destroy(string $id): void
    {
        RoleMiddleware::permission('media.manage');
        $this->validateCsrf();

        $file = Media::findFile((int) $id);
        if ($file === null) {
            Session::flash('error', 'File not found.');
            $this->redirect('media');
        }

        $folderId = $file['folder_id'] !== null ? (int) $file['folder_id'] : null;
        Media::deleteFile((int) $id);
        Session::flash('success', 'File deleted.');
        $this->redirect($this->folderRedirect($folderId));
    }

    /**
     * TinyMCE image upload endpoint.
     * Returns JSON: { location: "https://..." }
     */
    public function editorUpload(): void
    {
        RoleMiddleware::permission('media.manage');

        $token = $_POST['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if (!Csrf::validate(is_string($token) ? $token : null)) {
            $this->json(['error' => 'Invalid security token.'], 419);
        }

        $fileKey = isset($_FILES['file']) ? 'file' : (isset($_FILES['image']) ? 'image' : null);
        if ($fileKey === null) {
            $this->json(['error' => 'No image uploaded.'], 422);
        }

        $upload = Uploader::store(
            $_FILES[$fileKey] ?? [],
            'media/editor',
            ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            Media::MAX_BYTES
        );

        if ($upload['skipped'] || $upload['path'] === null || $upload['error'] !== null) {
            $this->json(['error' => $upload['error'] ?? 'Upload failed.'], 422);
        }

        $full = base_path('public/' . ltrim($upload['path'], '/'));
        $ext = strtolower(pathinfo($upload['path'], PATHINFO_EXTENSION));
        $mime = is_file($full) ? (new \finfo(FILEINFO_MIME_TYPE))->file($full) : null;
        $original = (string) ($_FILES[$fileKey]['name'] ?? basename($upload['path']));

        Media::createFile([
            'folder_id' => null,
            'original_name' => $original,
            'stored_name' => basename($upload['path']),
            'file_path' => $upload['path'],
            'extension' => $ext,
            'mime_type' => $mime,
            'size_bytes' => is_file($full) ? (int) filesize($full) : 0,
        ]);

        $this->json([
            'location' => absolute_url(upload_url($upload['path'])),
        ]);
    }

    /** TinyMCE file browser popup. */
    public function picker(): void
    {
        RoleMiddleware::permission('media.view');

        $type = trim((string) ($_GET['type'] ?? 'file')); // file|image|media
        if (!in_array($type, ['file', 'image', 'media'], true)) {
            $type = 'file';
        }

        $folderId = isset($_GET['folder']) && $_GET['folder'] !== '' ? (int) $_GET['folder'] : null;
        $search = trim((string) ($_GET['q'] ?? ''));

        if ($folderId !== null && Media::findFolder($folderId) === null) {
            $folderId = null;
        }

        if ($search !== '') {
            $files = Media::searchAll($search);
            $folders = [];
        } else {
            $folders = Media::folders($folderId);
            $files = Media::filesInFolder($folderId);
        }

        $files = array_values(array_filter($files, static function (array $file) use ($type): bool {
            $ext = strtolower((string) $file['extension']);
            return match ($type) {
                'image' => in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true),
                'media' => in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'pdf'], true),
                default => true,
            };
        }));

        $this->view('admin.media.picker', [
            'title' => 'File Browser',
            'type' => $type,
            'folders' => $folders,
            'files' => $files,
            'folderId' => $folderId,
            'breadcrumb' => Media::breadcrumb($folderId),
            'search' => $search,
            'canManage' => Auth::can('media.manage'),
            'csrfToken' => Csrf::token(),
            'uploadUrl' => url('media/editor-upload'),
        ]);
    }

    private function folderRedirect(?int $folderId): string
    {
        return $folderId === null ? 'media' : 'media?folder=' . $folderId;
    }
}

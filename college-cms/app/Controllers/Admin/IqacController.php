<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Helpers\Uploader;
use App\Middleware\RoleMiddleware;
use App\Models\Iqac;

final class IqacController extends Controller
{
    public function index(): void
    {
        RoleMiddleware::permission('iqac.view');

        $this->view('admin.iqac.index', [
            'title' => 'IQAC',
            'counts' => Iqac::counts(),
            'sections' => Iqac::SECTIONS,
            'committee' => Iqac::getCommittee(),
            'success' => flash('success'),
            'error' => flash('error'),
            'canManage' => Auth::can('iqac.manage'),
        ], 'admin.layouts.app');
    }

    public function committee(): void
    {
        RoleMiddleware::permission('iqac.manage');

        $this->view('admin.iqac.committee', [
            'title' => 'IQAC Committee',
            'committee' => Iqac::getCommittee(),
            'success' => flash('success'),
            'error' => flash('error'),
        ], 'admin.layouts.app');
    }

    public function updateCommittee(): void
    {
        RoleMiddleware::permission('iqac.manage');
        $this->validateCsrf();

        $title = trim((string) ($_POST['title'] ?? ''));
        if ($title === '') {
            Session::flash('error', 'Committee title is required.');
            $this->redirect('iqac/committee');
        }

        Iqac::updateCommittee([
            'title' => $title,
            'description' => (string) ($_POST['description'] ?? ''),
            'vision' => trim((string) ($_POST['vision'] ?? '')) ?: null,
            'mission' => trim((string) ($_POST['mission'] ?? '')) ?: null,
            'status' => isset($_POST['status']) ? 1 : 0,
        ]);

        Session::flash('success', 'Committee details updated.');
        $this->redirect('iqac/committee');
    }

    public function sectionIndex(string $section): void
    {
        RoleMiddleware::permission('iqac.view');
        $meta = $this->requireSection($section);

        $this->view('admin.iqac.section-list', [
            'title' => $meta['label'],
            'section' => $section,
            'meta' => $meta,
            'items' => Iqac::all($section),
            'success' => flash('success'),
            'error' => flash('error'),
            'canManage' => Auth::can('iqac.manage'),
        ], 'admin.layouts.app');
    }

    public function sectionCreate(string $section): void
    {
        RoleMiddleware::permission('iqac.manage');
        $meta = $this->requireSection($section);

        $this->view('admin.iqac.section-form', [
            'title' => 'Add ' . $meta['label'],
            'section' => $section,
            'meta' => $meta,
            'item' => null,
            'error' => flash('error'),
            'old' => Session::get('_old_iqac', []),
        ], 'admin.layouts.app');
    }

    public function sectionStore(string $section): void
    {
        RoleMiddleware::permission('iqac.manage');
        $this->validateCsrf();
        $meta = $this->requireSection($section);

        $data = $this->validatedItem($meta, false);
        Session::set('_old_iqac', $data['old']);

        if ($data['error'] !== null) {
            Session::flash('error', $data['error']);
            $this->redirect('iqac/' . $section . '/create');
        }

        $upload = $this->handleUpload($meta, null);
        if ($upload['error'] !== null) {
            Session::flash('error', $upload['error']);
            $this->redirect('iqac/' . $section . '/create');
        }

        $payload = $data['payload'];
        $fileField = $meta['file_field'] ?? null;
        if ($fileField) {
            $payload[$fileField] = $upload['path'];
        }

        if (!empty($meta['file_required']) && empty($payload[$fileField])) {
            Session::flash('error', 'A file/image upload is required.');
            $this->redirect('iqac/' . $section . '/create');
        }

        Iqac::create($section, $payload);
        Session::remove('_old_iqac');
        Session::flash('success', $meta['label'] . ' item created.');
        $this->redirect('iqac/' . $section);
    }

    public function sectionEdit(string $section, string $id): void
    {
        RoleMiddleware::permission('iqac.manage');
        $meta = $this->requireSection($section);
        $item = Iqac::find($section, (int) $id);
        if ($item === null) {
            Session::flash('error', 'Item not found.');
            $this->redirect('iqac/' . $section);
        }

        $this->view('admin.iqac.section-form', [
            'title' => 'Edit ' . $meta['label'],
            'section' => $section,
            'meta' => $meta,
            'item' => $item,
            'error' => flash('error'),
            'old' => Session::get('_old_iqac', []),
        ], 'admin.layouts.app');
    }

    public function sectionUpdate(string $section, string $id): void
    {
        RoleMiddleware::permission('iqac.manage');
        $this->validateCsrf();
        $meta = $this->requireSection($section);

        $itemId = (int) $id;
        $existing = Iqac::find($section, $itemId);
        if ($existing === null) {
            Session::flash('error', 'Item not found.');
            $this->redirect('iqac/' . $section);
        }

        $data = $this->validatedItem($meta, true);
        Session::set('_old_iqac', $data['old']);

        if ($data['error'] !== null) {
            Session::flash('error', $data['error']);
            $this->redirect('iqac/' . $section . '/' . $itemId . '/edit');
        }

        $payload = $data['payload'];
        $fileField = $meta['file_field'] ?? null;
        $currentFile = $fileField ? ($existing[$fileField] ?? null) : null;

        if ($fileField && !empty($_POST['remove_file'])) {
            Uploader::deletePublic(is_string($currentFile) ? $currentFile : null);
            $currentFile = null;
            $payload[$fileField] = null;
        }

        $upload = $this->handleUpload($meta, is_string($currentFile) ? $currentFile : null);
        if ($upload['error'] !== null) {
            Session::flash('error', $upload['error']);
            $this->redirect('iqac/' . $section . '/' . $itemId . '/edit');
        }

        if ($fileField) {
            if (!$upload['skipped'] && $upload['path']) {
                Uploader::deletePublic(is_string($currentFile) ? $currentFile : null);
                $payload[$fileField] = $upload['path'];
            } elseif (!array_key_exists($fileField, $payload)) {
                $payload[$fileField] = $currentFile;
            }
        }

        if (!empty($meta['file_required']) && empty($payload[$fileField] ?? null)) {
            Session::flash('error', 'A file/image upload is required.');
            $this->redirect('iqac/' . $section . '/' . $itemId . '/edit');
        }

        Iqac::update($section, $itemId, $payload);
        Session::remove('_old_iqac');
        Session::flash('success', $meta['label'] . ' item updated.');
        $this->redirect('iqac/' . $section);
    }

    public function sectionDestroy(string $section, string $id): void
    {
        RoleMiddleware::permission('iqac.manage');
        $this->validateCsrf();
        $meta = $this->requireSection($section);

        Iqac::delete($section, (int) $id);
        Session::flash('success', $meta['label'] . ' item deleted.');
        $this->redirect('iqac/' . $section);
    }

    /** @return array<string,mixed> */
    private function requireSection(string $section): array
    {
        $meta = Iqac::section($section);
        if ($meta === null) {
            http_response_code(404);
            echo 'IQAC section not found';
            exit;
        }
        return $meta;
    }

    /**
     * @param array<string,mixed> $meta
     * @return array{payload:array<string,mixed>,old:array<string,mixed>,error:?string}
     */
    private function validatedItem(array $meta, bool $isUpdate): array
    {
        $payload = [];
        $old = [];
        $error = null;

        foreach ($meta['fields'] as $field) {
            $raw = $_POST[$field] ?? '';
            $value = is_string($raw) ? trim($raw) : $raw;
            $old[$field] = $value;

            if ($value === '') {
                $payload[$field] = in_array($field, ['sort_order'], true) ? 0 : null;
            } elseif ($field === 'sort_order') {
                $payload[$field] = (int) $value;
            } else {
                $payload[$field] = $value;
            }
        }

        $payload['status'] = isset($_POST['status']) ? 1 : 0;
        $old['status'] = $payload['status'];

        foreach ($meta['required'] as $field) {
            if (empty($payload[$field])) {
                $error = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                break;
            }
        }

        if ($error === null && !empty($payload['email']) && !filter_var((string) $payload['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Email is invalid.';
        }

        return ['payload' => $payload, 'old' => $old, 'error' => $error];
    }

    /**
     * @param array<string,mixed> $meta
     * @return array{path:?string,error:?string,skipped:bool}
     */
    private function handleUpload(array $meta, ?string $existingPath): array
    {
        $fileField = $meta['file_field'] ?? null;
        if ($fileField === null) {
            return ['path' => null, 'error' => null, 'skipped' => true];
        }

        $input = $fileField === 'photo' || $fileField === 'image_path' ? 'upload_file' : 'upload_file';

        return Uploader::store(
            $_FILES[$input] ?? [],
            (string) $meta['file_subdir'],
            $meta['file_types'] ?? [],
            $meta['file_mimes'] ?? [],
            20 * 1024 * 1024
        );
    }
}

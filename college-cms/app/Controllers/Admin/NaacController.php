<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Helpers\Uploader;
use App\Middleware\RoleMiddleware;
use App\Models\NaacCriterion;

final class NaacController extends Controller
{
    public function index(): void
    {
        RoleMiddleware::permission('naac.view');

        $this->view('admin.naac.index', [
            'title' => 'NAAC Criteria',
            'criteria' => NaacCriterion::all(),
            'success' => flash('success'),
            'error' => flash('error'),
            'canManage' => Auth::can('naac.manage'),
        ], 'admin.layouts.app');
    }

    public function edit(string $id): void
    {
        RoleMiddleware::permission('naac.manage');

        $criterion = NaacCriterion::withRelations((int) $id);
        if ($criterion === null) {
            Session::flash('error', 'Criterion not found.');
            $this->redirect('naac');
        }

        $this->view('admin.naac.edit', [
            'title' => 'Criteria ' . $criterion['number'],
            'criterion' => $criterion,
            'success' => flash('success'),
            'error' => flash('error'),
            'old' => Session::get('_old', []),
        ], 'admin.layouts.app');
    }

    public function update(string $id): void
    {
        RoleMiddleware::permission('naac.manage');
        $this->validateCsrf();

        $criterionId = (int) $id;
        $existing = NaacCriterion::findById($criterionId);
        if ($existing === null) {
            Session::flash('error', 'Criterion not found.');
            $this->redirect('naac');
        }

        $heading = trim((string) ($_POST['heading'] ?? ''));
        $description = (string) ($_POST['description'] ?? '');
        $slugInput = trim((string) ($_POST['slug'] ?? ''));
        $status = isset($_POST['status']) ? 1 : 0;

        if ($heading === '') {
            Session::flash('error', 'Heading is required.');
            $this->redirect('naac/' . $criterionId . '/edit');
        }

        $slug = $slugInput !== ''
            ? NaacCriterion::uniqueCriterionSlug($slugInput, $criterionId)
            : NaacCriterion::uniqueCriterionSlug('criteria-' . $existing['number'], $criterionId);

        NaacCriterion::updateCriterion($criterionId, [
            'heading' => $heading,
            'description' => $description !== '' ? $description : null,
            'slug' => $slug,
            'status' => $status,
        ]);

        NaacCriterion::syncLinks($criterionId, $this->parseLinks());
        NaacCriterion::syncTables($criterionId, $this->parseTables());
        $this->storeNewFiles($criterionId);
        $this->storeNewImages($criterionId);

        Session::flash('success', 'Criteria ' . $existing['number'] . ' updated successfully.');
        $this->redirect('naac/' . $criterionId . '/edit');
    }

    public function deleteFile(string $id, string $fileId): void
    {
        RoleMiddleware::permission('naac.manage');
        $this->validateCsrf();
        NaacCriterion::deleteFile((int) $id, (int) $fileId);
        Session::flash('success', 'File removed.');
        $this->redirect('naac/' . (int) $id . '/edit');
    }

    public function deleteImage(string $id, string $imageId): void
    {
        RoleMiddleware::permission('naac.manage');
        $this->validateCsrf();
        NaacCriterion::deleteImage((int) $id, (int) $imageId);
        Session::flash('success', 'Image removed.');
        $this->redirect('naac/' . (int) $id . '/edit');
    }

    public function createPage(string $id): void
    {
        RoleMiddleware::permission('naac.manage');

        $criterion = NaacCriterion::findById((int) $id);
        if ($criterion === null) {
            Session::flash('error', 'Criterion not found.');
            $this->redirect('naac');
        }

        $this->view('admin.naac.page-form', [
            'title' => 'Add NAAC Page',
            'criterion' => $criterion,
            'page' => null,
            'error' => flash('error'),
            'old' => Session::get('_old_page', []),
        ], 'admin.layouts.app');
    }

    public function storePage(string $id): void
    {
        RoleMiddleware::permission('naac.manage');
        $this->validateCsrf();

        $criterionId = (int) $id;
        if (NaacCriterion::findById($criterionId) === null) {
            Session::flash('error', 'Criterion not found.');
            $this->redirect('naac');
        }

        $data = $this->validatedPage($criterionId);
        Session::set('_old_page', $data);

        if ($data['error'] !== null) {
            Session::flash('error', $data['error']);
            $this->redirect('naac/' . $criterionId . '/pages/create');
        }

        NaacCriterion::createPage($criterionId, $data);
        Session::remove('_old_page');
        Session::flash('success', 'Dynamic page created.');
        $this->redirect('naac/' . $criterionId . '/edit');
    }

    public function editPage(string $id, string $pageId): void
    {
        RoleMiddleware::permission('naac.manage');

        $criterion = NaacCriterion::findById((int) $id);
        $page = NaacCriterion::findPage((int) $id, (int) $pageId);
        if ($criterion === null || $page === null) {
            Session::flash('error', 'Page not found.');
            $this->redirect('naac');
        }

        $this->view('admin.naac.page-form', [
            'title' => 'Edit NAAC Page',
            'criterion' => $criterion,
            'page' => $page,
            'error' => flash('error'),
            'old' => Session::get('_old_page', []),
        ], 'admin.layouts.app');
    }

    public function updatePage(string $id, string $pageId): void
    {
        RoleMiddleware::permission('naac.manage');
        $this->validateCsrf();

        $criterionId = (int) $id;
        $page = NaacCriterion::findPage($criterionId, (int) $pageId);
        if ($page === null) {
            Session::flash('error', 'Page not found.');
            $this->redirect('naac/' . $criterionId . '/edit');
        }

        $data = $this->validatedPage($criterionId, (int) $pageId);
        Session::set('_old_page', $data);

        if ($data['error'] !== null) {
            Session::flash('error', $data['error']);
            $this->redirect('naac/' . $criterionId . '/pages/' . (int) $pageId . '/edit');
        }

        NaacCriterion::updatePage($criterionId, (int) $pageId, $data);
        Session::remove('_old_page');
        Session::flash('success', 'Dynamic page updated.');
        $this->redirect('naac/' . $criterionId . '/edit');
    }

    public function deletePage(string $id, string $pageId): void
    {
        RoleMiddleware::permission('naac.manage');
        $this->validateCsrf();
        NaacCriterion::deletePage((int) $id, (int) $pageId);
        Session::flash('success', 'Dynamic page deleted.');
        $this->redirect('naac/' . (int) $id . '/edit');
    }

    /** @return list<array<string,mixed>> */
    private function parseLinks(): array
    {
        $rows = $_POST['links'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $title = trim((string) ($row['title'] ?? ''));
            $url = trim((string) ($row['url'] ?? ''));
            if ($title === '' || $url === '') {
                continue;
            }
            $out[] = [
                'id' => $row['id'] ?? '',
                'title' => $title,
                'url' => $url,
                'open_in_new_tab' => isset($row['open_in_new_tab']) ? 1 : 0,
            ];
        }
        return $out;
    }

    /** @return list<array<string,mixed>> */
    private function parseTables(): array
    {
        $rows = $_POST['tables'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $title = trim((string) ($row['title'] ?? ''));
            $html = trim((string) ($row['table_html'] ?? ''));
            if ($title === '' || $html === '') {
                continue;
            }
            $out[] = [
                'id' => $row['id'] ?? '',
                'title' => $title,
                'table_html' => $html,
            ];
        }
        return $out;
    }

    private function storeNewFiles(int $criterionId): void
    {
        $titles = $_POST['file_title'] ?? [];
        if (!is_array($titles)) {
            $titles = [];
        }

        $uploads = Uploader::storeMany(
            $_FILES['file_upload'] ?? [],
            'naac/files',
            ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'csv'],
            [],
            20 * 1024 * 1024
        );

        $count = count(NaacCriterion::files($criterionId));
        foreach ($uploads as $i => $upload) {
            if ($upload['skipped'] || $upload['path'] === null || $upload['error'] !== null) {
                continue;
            }
            $title = trim((string) ($titles[$i] ?? '')) ?: ('File ' . ($count + $i + 1));
            NaacCriterion::addFile($criterionId, $title, $upload['path'], $count + $i);
        }
    }

    private function storeNewImages(int $criterionId): void
    {
        $titles = $_POST['image_title'] ?? [];
        $captions = $_POST['image_caption'] ?? [];
        if (!is_array($titles)) {
            $titles = [];
        }
        if (!is_array($captions)) {
            $captions = [];
        }

        $uploads = Uploader::storeMany(
            $_FILES['image_upload'] ?? [],
            'naac/images',
            ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            5 * 1024 * 1024
        );

        $count = count(NaacCriterion::images($criterionId));
        foreach ($uploads as $i => $upload) {
            if ($upload['skipped'] || $upload['path'] === null || $upload['error'] !== null) {
                continue;
            }
            $title = trim((string) ($titles[$i] ?? '')) ?: null;
            $caption = trim((string) ($captions[$i] ?? '')) ?: null;
            NaacCriterion::addImage($criterionId, $title, $upload['path'], $caption, $count + $i);
        }
    }

    /** @return array{title:string,slug:string,content:?string,status:int,error:?string} */
    private function validatedPage(int $criterionId, ?int $exceptId = null): array
    {
        $title = trim((string) ($_POST['title'] ?? ''));
        $slugInput = trim((string) ($_POST['slug'] ?? ''));
        $content = (string) ($_POST['content'] ?? '');
        $status = isset($_POST['status']) ? 1 : 0;
        $error = null;

        if ($title === '') {
            $error = 'Page title is required.';
        }

        $slug = $slugInput !== '' ? NaacCriterion::slugify($slugInput) : NaacCriterion::slugify($title);
        if ($error === null) {
            $slug = NaacCriterion::uniquePageSlug($criterionId, $slug, $exceptId);
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'content' => $content !== '' ? $content : null,
            'status' => $status,
            'error' => $error,
        ];
    }
}

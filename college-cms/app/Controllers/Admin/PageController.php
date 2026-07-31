<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Helpers\Uploader;
use App\Middleware\RoleMiddleware;
use App\Models\Menu;
use App\Models\Page;

final class PageController extends Controller
{
    public function index(): void
    {
        RoleMiddleware::permission('pages.view');

        $search = trim((string) ($_GET['q'] ?? ''));

        $this->view('admin.pages.index', [
            'title' => 'Pages',
            'pages' => Page::all($search !== '' ? $search : null),
            'search' => $search,
            'success' => flash('success'),
            'error' => flash('error'),
            'canManage' => Auth::can('pages.manage'),
        ], 'admin.layouts.app');
    }

    public function create(): void
    {
        RoleMiddleware::permission('pages.manage');

        $this->view('admin.pages.form', [
            'title' => 'Create Page',
            'page' => null,
            'menus' => Menu::allByPosition(null),
            'error' => flash('error'),
            'old' => Session::get('_old', []),
        ], 'admin.layouts.app');
    }

    public function store(): void
    {
        RoleMiddleware::permission('pages.manage');
        $this->validateCsrf();

        $data = $this->validatedInput();
        Session::set('_old', $this->oldFromData($data));

        if ($data['error'] !== null) {
            Session::flash('error', $data['error']);
            $this->redirect('pages/create');
        }

        $image = Uploader::store(
            $_FILES['featured_image'] ?? [],
            'images',
            ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            5 * 1024 * 1024
        );
        if ($image['error'] !== null) {
            Session::flash('error', $image['error']);
            $this->redirect('pages/create');
        }

        $pdf = Uploader::store(
            $_FILES['pdf_attachment'] ?? [],
            'documents',
            ['pdf'],
            ['application/pdf'],
            10 * 1024 * 1024
        );
        if ($pdf['error'] !== null) {
            if (!$image['skipped'] && $image['path']) {
                Uploader::deletePublic($image['path']);
            }
            Session::flash('error', $pdf['error']);
            $this->redirect('pages/create');
        }

        $data['featured_image'] = $image['path'];
        $data['pdf_attachment'] = $pdf['path'];

        $id = Page::create($data);
        Page::syncMenuLink($id, $data['menu_id']);

        Session::remove('_old');
        Session::flash('success', 'Page created successfully.');
        $this->redirect('pages');
    }

    public function edit(string $id): void
    {
        RoleMiddleware::permission('pages.manage');

        $page = Page::findById((int) $id);
        if ($page === null) {
            Session::flash('error', 'Page not found.');
            $this->redirect('pages');
        }

        $this->view('admin.pages.form', [
            'title' => 'Edit Page',
            'page' => $page,
            'menus' => Menu::allByPosition(null),
            'error' => flash('error'),
            'old' => Session::get('_old', []),
        ], 'admin.layouts.app');
    }

    public function update(string $id): void
    {
        RoleMiddleware::permission('pages.manage');
        $this->validateCsrf();

        $pageId = (int) $id;
        $existing = Page::findById($pageId);
        if ($existing === null) {
            Session::flash('error', 'Page not found.');
            $this->redirect('pages');
        }

        $data = $this->validatedInput(true, $existing);
        Session::set('_old', $this->oldFromData($data));

        if ($data['error'] !== null) {
            Session::flash('error', $data['error']);
            $this->redirect('pages/' . $pageId . '/edit');
        }

        $featured = $existing['featured_image'] ?? null;
        $pdfPath = $existing['pdf_attachment'] ?? null;

        if (!empty($_POST['remove_featured_image'])) {
            Uploader::deletePublic(is_string($featured) ? $featured : null);
            $featured = null;
        }

        if (!empty($_POST['remove_pdf_attachment'])) {
            Uploader::deletePublic(is_string($pdfPath) ? $pdfPath : null);
            $pdfPath = null;
        }

        $image = Uploader::store(
            $_FILES['featured_image'] ?? [],
            'images',
            ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            5 * 1024 * 1024
        );
        if ($image['error'] !== null) {
            Session::flash('error', $image['error']);
            $this->redirect('pages/' . $pageId . '/edit');
        }
        if (!$image['skipped'] && $image['path']) {
            Uploader::deletePublic(is_string($featured) ? $featured : null);
            $featured = $image['path'];
        }

        $pdf = Uploader::store(
            $_FILES['pdf_attachment'] ?? [],
            'documents',
            ['pdf'],
            ['application/pdf'],
            10 * 1024 * 1024
        );
        if ($pdf['error'] !== null) {
            Session::flash('error', $pdf['error']);
            $this->redirect('pages/' . $pageId . '/edit');
        }
        if (!$pdf['skipped'] && $pdf['path']) {
            Uploader::deletePublic(is_string($pdfPath) ? $pdfPath : null);
            $pdfPath = $pdf['path'];
        }

        $data['featured_image'] = $featured;
        $data['pdf_attachment'] = $pdfPath;

        Page::updatePage($pageId, $data);
        Page::syncMenuLink($pageId, $data['menu_id']);

        Session::remove('_old');
        Session::flash('success', 'Page updated successfully.');
        $this->redirect('pages');
    }

    public function destroy(string $id): void
    {
        RoleMiddleware::permission('pages.manage');
        $this->validateCsrf();

        $page = Page::findById((int) $id);
        if ($page === null) {
            Session::flash('error', 'Page not found.');
            $this->redirect('pages');
        }

        Uploader::deletePublic(isset($page['featured_image']) ? (string) $page['featured_image'] : null);
        Uploader::deletePublic(isset($page['pdf_attachment']) ? (string) $page['pdf_attachment'] : null);
        Page::deleteById((int) $id);

        Session::flash('success', 'Page deleted successfully.');
        $this->redirect('pages');
    }

    /**
     * @return array{
     *   title:string,slug:string,content:?string,menu_id:?int,
     *   meta_title:?string,meta_description:?string,meta_keywords:?string,
     *   featured_image:?string,pdf_attachment:?string,status:int,error:?string
     * }
     */
    private function validatedInput(bool $isUpdate = false, ?array $existing = null): array
    {
        $title = trim((string) ($_POST['title'] ?? ''));
        $slugInput = trim((string) ($_POST['slug'] ?? ''));
        $content = (string) ($_POST['content'] ?? '');
        $menuId = ($_POST['menu_id'] ?? '') !== '' ? (int) $_POST['menu_id'] : null;
        $metaTitle = trim((string) ($_POST['meta_title'] ?? ''));
        $metaDescription = trim((string) ($_POST['meta_description'] ?? ''));
        $metaKeywords = trim((string) ($_POST['meta_keywords'] ?? ''));
        $status = isset($_POST['status']) ? 1 : 0;

        $error = null;
        $exceptId = $isUpdate && $existing ? (int) $existing['id'] : null;

        if ($title === '' || mb_strlen($title) > 200) {
            $error = 'Title is required (max 200 characters).';
        }

        $slug = $slugInput !== '' ? Page::slugify($slugInput) : Page::slugify($title);
        if ($error === null) {
            $slug = Page::uniqueSlug($slug, $exceptId);
        }

        if ($error === null && $menuId !== null && Menu::findById($menuId) === null) {
            $error = 'Selected menu item was not found.';
        }

        if ($error === null && mb_strlen($metaTitle) > 200) {
            $error = 'Meta title is too long (max 200).';
        }
        if ($error === null && mb_strlen($metaDescription) > 500) {
            $error = 'Meta description is too long (max 500).';
        }
        if ($error === null && mb_strlen($metaKeywords) > 500) {
            $error = 'Keywords are too long (max 500).';
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'content' => $content !== '' ? $content : null,
            'menu_id' => $menuId,
            'meta_title' => $metaTitle !== '' ? $metaTitle : null,
            'meta_description' => $metaDescription !== '' ? $metaDescription : null,
            'meta_keywords' => $metaKeywords !== '' ? $metaKeywords : null,
            'featured_image' => null,
            'pdf_attachment' => null,
            'status' => $status,
            'error' => $error,
        ];
    }

    /** @param array<string,mixed> $data */
    private function oldFromData(array $data): array
    {
        return [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'] ?? '',
            'menu_id' => $data['menu_id'],
            'meta_title' => $data['meta_title'] ?? '',
            'meta_description' => $data['meta_description'] ?? '',
            'meta_keywords' => $data['meta_keywords'] ?? '',
            'status' => $data['status'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Middleware\RoleMiddleware;
use App\Models\Department;
use App\Models\Download;
use App\Models\Gallery;
use App\Models\Iqac;
use App\Models\Menu;
use App\Models\NaacCriterion;
use App\Models\Page;

final class MenuController extends Controller
{
    public function index(): void
    {
        RoleMiddleware::permission('menus.view');

        $position = trim((string) ($_GET['position'] ?? ''));
        if ($position !== '' && !isset(Menu::POSITIONS[$position])) {
            $position = '';
        }

        $this->view('admin.menus.index', [
            'title' => 'Menus',
            'trees' => Menu::treeByPosition($position !== '' ? $position : null),
            'positions' => Menu::POSITIONS,
            'filterPosition' => $position,
            'success' => flash('success'),
            'error' => flash('error'),
            'canManage' => Auth::can('menus.manage'),
            'csrfToken' => \App\Core\Csrf::token(),
            'reorderUrl' => url('menus/reorder'),
        ], 'admin.layouts.app');
    }

    public function create(): void
    {
        RoleMiddleware::permission('menus.manage');

        $position = (string) (Session::get('_old')['position'] ?? $_GET['position'] ?? 'header');
        if (!isset(Menu::POSITIONS[$position])) {
            $position = 'header';
        }

        $this->view('admin.menus.form', $this->formData([
            'title' => 'Add Menu Item',
            'menu' => null,
            'defaultPosition' => $position,
        ]), 'admin.layouts.app');
    }

    public function store(): void
    {
        RoleMiddleware::permission('menus.manage');
        $this->validateCsrf();

        $data = $this->validatedInput();
        Session::set('_old', $this->oldFromData($data));

        if ($data['error'] !== null) {
            Session::flash('error', $data['error']);
            $this->redirect('menus/create');
        }

        Menu::create($data);
        Session::remove('_old');
        Session::flash('success', 'Menu item created.');
        $this->redirect('menus?position=' . urlencode($data['position']));
    }

    public function edit(string $id): void
    {
        RoleMiddleware::permission('menus.manage');

        $menu = Menu::findById((int) $id);
        if ($menu === null) {
            Session::flash('error', 'Menu item not found.');
            $this->redirect('menus');
        }

        $position = (string) (Session::get('_old')['position'] ?? $menu['position']);
        if (!isset(Menu::POSITIONS[$position])) {
            $position = (string) $menu['position'];
        }

        $this->view('admin.menus.form', $this->formData([
            'title' => 'Edit Menu Item',
            'menu' => $menu,
            'defaultPosition' => $position,
            'parents' => Menu::optionsForParent(null, (int) $menu['id']),
        ]), 'admin.layouts.app');
    }

    public function update(string $id): void
    {
        RoleMiddleware::permission('menus.manage');
        $this->validateCsrf();

        $menuId = (int) $id;
        $existing = Menu::findById($menuId);
        if ($existing === null) {
            Session::flash('error', 'Menu item not found.');
            $this->redirect('menus');
        }

        $data = $this->validatedInput(true, $existing);
        Session::set('_old', $this->oldFromData($data));

        if ($data['error'] !== null) {
            Session::flash('error', $data['error']);
            $this->redirect('menus/' . $menuId . '/edit');
        }

        $allowedParents = Menu::optionsForParent($data['position'], $menuId);
        if ($data['parent_id'] !== null) {
            $ok = false;
            foreach ($allowedParents as $p) {
                if ((int) $p['id'] === (int) $data['parent_id']) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                Session::flash('error', 'Invalid parent menu selection.');
                $this->redirect('menus/' . $menuId . '/edit');
            }
        }

        Menu::updateMenu($menuId, $data);
        Session::remove('_old');
        Session::flash('success', 'Menu item updated.');
        $this->redirect('menus?position=' . urlencode($data['position']));
    }

    public function destroy(string $id): void
    {
        RoleMiddleware::permission('menus.manage');
        $this->validateCsrf();

        $menu = Menu::findById((int) $id);
        if ($menu === null) {
            Session::flash('error', 'Menu item not found.');
            $this->redirect('menus');
        }

        $position = (string) $menu['position'];
        Menu::deleteById((int) $id);
        Session::flash('success', 'Menu item deleted. Child items (if any) were moved to top level.');
        $this->redirect('menus?position=' . urlencode($position));
    }

    public function reorder(): void
    {
        RoleMiddleware::permission('menus.manage');
        $this->validateCsrf();

        $body = $this->jsonBody();
        $items = $body['items'] ?? null;

        if (!is_array($items) || $items === []) {
            $this->json(['ok' => false, 'message' => 'No menu items provided.'], 422);
        }

        $normalized = [];
        foreach ($items as $item) {
            if (!is_array($item) || !isset($item['id'])) {
                continue;
            }
            $normalized[] = [
                'id' => (int) $item['id'],
                'parent_id' => isset($item['parent_id']) && $item['parent_id'] !== '' && $item['parent_id'] !== null
                    ? (int) $item['parent_id']
                    : null,
                'sort_order' => (int) ($item['sort_order'] ?? 0),
            ];
        }

        if ($normalized === []) {
            $this->json(['ok' => false, 'message' => 'Invalid payload.'], 422);
        }

        try {
            Menu::reorder($normalized);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'message' => 'Failed to save order.'], 500);
        }

        $this->json(['ok' => true, 'message' => 'Order saved.']);
    }

    /** @param array<string,mixed> $extra */
    private function formData(array $extra): array
    {
        $iqacSections = [];
        foreach (Iqac::SECTIONS as $key => $meta) {
            $iqacSections[$key] = $meta['label'];
        }

        return array_merge([
            'positions' => Menu::POSITIONS,
            'linkTypes' => Menu::LINK_TYPES,
            'parents' => Menu::optionsForParent(null),
            'pages' => Page::allPublished(),
            'departments' => Department::allActive(),
            'naacCriteria' => NaacCriterion::all(),
            'iqacSections' => $iqacSections,
            'downloads' => Download::allPublished(),
            'galleries' => Gallery::allPublished(),
            'error' => flash('error'),
            'old' => Session::get('_old', []),
        ], $extra);
    }

    /**
     * @return array{
     *   name:string,position:string,sort_order:int,parent_id:?int,status:int,
     *   open_in_new_tab:int,link_type:string,url:?string,page_id:?int,
     *   department_id:?int,naac_criterion_id:?int,iqac_section:?string,
     *   download_id:?int,gallery_id:?int,error:?string
     * }
     */
    private function validatedInput(bool $isUpdate = false, ?array $existing = null): array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $position = trim((string) ($_POST['position'] ?? 'header'));
        $parentId = ($_POST['parent_id'] ?? '') !== '' ? (int) $_POST['parent_id'] : null;
        $status = isset($_POST['status']) ? 1 : 0;
        $openInNewTab = isset($_POST['open_in_new_tab']) ? 1 : 0;
        $linkType = trim((string) ($_POST['link_type'] ?? 'custom'));
        $url = trim((string) ($_POST['url'] ?? ''));
        $pageId = ($_POST['page_id'] ?? '') !== '' ? (int) $_POST['page_id'] : null;
        $departmentId = ($_POST['department_id'] ?? '') !== '' ? (int) $_POST['department_id'] : null;
        $naacCriterionId = ($_POST['naac_criterion_id'] ?? '') !== '' ? (int) $_POST['naac_criterion_id'] : null;
        $iqacSection = trim((string) ($_POST['iqac_section'] ?? ''));
        $downloadId = ($_POST['download_id'] ?? '') !== '' ? (int) $_POST['download_id'] : null;
        $galleryId = ($_POST['gallery_id'] ?? '') !== '' ? (int) $_POST['gallery_id'] : null;
        $sortOrder = (int) ($_POST['sort_order'] ?? ($existing['sort_order'] ?? 0));

        $error = null;

        if ($name === '' || mb_strlen($name) > 150) {
            $error = 'Menu name is required (max 150 characters).';
        } elseif (!isset(Menu::POSITIONS[$position])) {
            $error = 'Please choose a valid menu position.';
        } elseif (!isset(Menu::LINK_TYPES[$linkType])) {
            $error = 'Please choose a valid link type.';
        } else {
            switch ($linkType) {
                case 'page':
                    if ($pageId === null || Page::findById($pageId) === null) {
                        $error = 'Please select a valid internal page.';
                    }
                    break;
                case 'department':
                    if ($departmentId === null || Department::findById($departmentId) === null) {
                        $error = 'Please select a valid department.';
                    }
                    break;
                case 'naac':
                    if ($naacCriterionId !== null && NaacCriterion::findById($naacCriterionId) === null) {
                        $error = 'Please select a valid NAAC criterion.';
                    }
                    break;
                case 'iqac':
                    if ($iqacSection !== '' && !isset(Iqac::SECTIONS[$iqacSection])) {
                        $error = 'Please select a valid IQAC section.';
                    }
                    break;
                case 'downloads':
                    if ($downloadId !== null && Download::findById($downloadId) === null) {
                        $error = 'Please select a valid download.';
                    }
                    break;
                case 'gallery':
                    if ($galleryId !== null && Gallery::findById($galleryId) === null) {
                        $error = 'Please select a valid gallery.';
                    }
                    break;
                case 'external':
                    if ($url === '') {
                        $error = 'External URL is required.';
                    } elseif (mb_strlen($url) > 500) {
                        $error = 'URL is too long.';
                    } elseif (!preg_match('#^https?://#i', $url)) {
                        $error = 'External URL must start with http:// or https://.';
                    }
                    break;
                case 'custom':
                    if ($url === '') {
                        $error = 'Custom link path is required.';
                    } elseif (mb_strlen($url) > 500) {
                        $error = 'Custom link is too long.';
                    }
                    break;
            }
        }

        if ($parentId !== null && $parentId <= 0) {
            $parentId = null;
        }

        if ($isUpdate && $existing !== null && $parentId === (int) $existing['id']) {
            $error = 'A menu item cannot be its own parent.';
        }

        return [
            'name' => $name,
            'position' => $position,
            'sort_order' => $sortOrder,
            'parent_id' => $parentId,
            'status' => $status,
            'open_in_new_tab' => $openInNewTab,
            'link_type' => $linkType,
            'url' => $url !== '' ? $url : null,
            'page_id' => $pageId,
            'department_id' => $departmentId,
            'naac_criterion_id' => $naacCriterionId,
            'iqac_section' => $iqacSection !== '' ? $iqacSection : null,
            'download_id' => $downloadId,
            'gallery_id' => $galleryId,
            'error' => $error,
        ];
    }

    /** @param array<string,mixed> $data */
    private function oldFromData(array $data): array
    {
        return [
            'name' => $data['name'],
            'position' => $data['position'],
            'sort_order' => $data['sort_order'],
            'parent_id' => $data['parent_id'],
            'status' => $data['status'],
            'open_in_new_tab' => $data['open_in_new_tab'],
            'link_type' => $data['link_type'],
            'url' => $data['url'] ?? '',
            'page_id' => $data['page_id'],
            'department_id' => $data['department_id'],
            'naac_criterion_id' => $data['naac_criterion_id'],
            'iqac_section' => $data['iqac_section'],
            'download_id' => $data['download_id'],
            'gallery_id' => $data['gallery_id'],
        ];
    }
}

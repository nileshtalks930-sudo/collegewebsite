<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Middleware\RoleMiddleware;
use App\Models\Menu;
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

        $this->view('admin.menus.form', [
            'title' => 'Add Menu Item',
            'menu' => null,
            'positions' => Menu::POSITIONS,
            'parents' => Menu::optionsForParent(null),
            'pages' => Page::allPublished(),
            'error' => flash('error'),
            'old' => Session::get('_old', []),
            'defaultPosition' => $position,
        ], 'admin.layouts.app');
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

        $this->view('admin.menus.form', [
            'title' => 'Edit Menu Item',
            'menu' => $menu,
            'positions' => Menu::POSITIONS,
            'parents' => Menu::optionsForParent(null, (int) $menu['id']),
            'pages' => Page::allPublished(),
            'error' => flash('error'),
            'old' => Session::get('_old', []),
            'defaultPosition' => $position,
        ], 'admin.layouts.app');
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

        // Prevent assigning a descendant as parent
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

    /**
     * @return array{
     *   name:string,position:string,sort_order:int,parent_id:?int,status:int,
     *   open_in_new_tab:int,link_type:string,url:?string,page_id:?int,error:?string
     * }
     */
    private function validatedInput(bool $isUpdate = false, ?array $existing = null): array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $position = trim((string) ($_POST['position'] ?? 'header'));
        $parentId = ($_POST['parent_id'] ?? '') !== '' ? (int) $_POST['parent_id'] : null;
        $status = isset($_POST['status']) ? 1 : 0;
        $openInNewTab = isset($_POST['open_in_new_tab']) ? 1 : 0;
        $linkType = trim((string) ($_POST['link_type'] ?? 'url'));
        $url = trim((string) ($_POST['url'] ?? ''));
        $pageId = ($_POST['page_id'] ?? '') !== '' ? (int) $_POST['page_id'] : null;
        $sortOrder = (int) ($_POST['sort_order'] ?? ($existing['sort_order'] ?? 0));

        $error = null;

        if ($name === '' || mb_strlen($name) > 150) {
            $error = 'Menu name is required (max 150 characters).';
        } elseif (!isset(Menu::POSITIONS[$position])) {
            $error = 'Please choose a valid menu position.';
        } elseif (!in_array($linkType, ['url', 'page'], true)) {
            $error = 'Link type must be URL or Internal Page.';
        } elseif ($linkType === 'url') {
            if ($url === '') {
                $error = 'URL is required when link type is URL.';
            } elseif (mb_strlen($url) > 500) {
                $error = 'URL is too long.';
            } else {
                $pageId = null;
            }
        } elseif ($linkType === 'page') {
            if ($pageId === null || Page::findById($pageId) === null) {
                $error = 'Please select a valid internal page.';
            } else {
                $url = null;
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
        ];
    }
}

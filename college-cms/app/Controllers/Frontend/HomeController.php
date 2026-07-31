<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Models\Department;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Setting;
use Throwable;

final class HomeController extends Controller
{
    public function index(): void
    {
        $settings = [];
        $menus = [];
        $departments = [];
        $pages = [];

        try {
            $settings = Setting::allKeyed();
        } catch (Throwable) {
            $settings = [];
        }

        try {
            $tree = Menu::treeByPosition('header');
            $menus = $tree['header'] ?? [];
        } catch (Throwable) {
            $menus = [];
        }

        try {
            if (method_exists(Department::class, 'allActive')) {
                $departments = Department::allActive();
            } else {
                $departments = array_values(array_filter(
                    Department::all(),
                    static fn (array $d): bool => (int) ($d['status'] ?? 0) === 1
                ));
            }
        } catch (Throwable) {
            $departments = [];
        }

        try {
            $pages = Page::allPublished();
        } catch (Throwable) {
            $pages = [];
        }

        $this->view('frontend.home.index', [
            'title' => $settings['website_name'] ?? 'College Website',
            'settings' => $settings,
            'menus' => $menus,
            'departments' => $departments,
            'pages' => $pages,
        ], 'frontend.layouts.app');
    }
}

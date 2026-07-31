<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\User;

final class DashboardController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::handle();

        $user = Auth::user();

        $this->view('admin.dashboard.index', [
            'title' => 'Dashboard',
            'user' => $user,
            'activeUsers' => User::countActive(),
        ], 'admin.layouts.app');
    }
}

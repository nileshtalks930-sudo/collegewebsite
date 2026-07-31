<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;

final class RoleMiddleware
{
    public static function permission(string $permission): void
    {
        AuthMiddleware::handle();

        if (!Auth::can($permission)) {
            http_response_code(403);
            Session::flash('error', 'You do not have permission to access that resource.');
            header('Location: ' . url('dashboard'));
            exit;
        }
    }

    public static function role(string ...$slugs): void
    {
        AuthMiddleware::handle();

        $user = Auth::user();
        $current = $user['role_slug'] ?? '';

        if (!in_array($current, $slugs, true)) {
            http_response_code(403);
            Session::flash('error', 'You do not have the required role.');
            header('Location: ' . url('dashboard'));
            exit;
        }
    }
}

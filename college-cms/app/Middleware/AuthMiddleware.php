<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

final class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            header('Location: ' . url('login'));
            exit;
        }
    }

    public static function guest(): void
    {
        if (Auth::check()) {
            header('Location: ' . url('dashboard'));
            exit;
        }
    }
}

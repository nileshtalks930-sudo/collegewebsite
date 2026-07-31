<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Session;

function base_path(string $path = ''): string
{
    $root = dirname(__DIR__, 2);
    return $path === '' ? $root : $root . '/' . ltrim($path, '/');
}

function app_config(?string $key = null, mixed $default = null): mixed
{
    static $config;
    $config ??= require base_path('config/app.php');

    if ($key === null) {
        return $config;
    }

    return $config[$key] ?? $default;
}

function url(string $path = ''): string
{
    $configured = (string) app_config('url', '');
    if ($configured !== '') {
        return rtrim($configured, '/') . '/' . ltrim($path, '/');
    }

    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $base = str_replace('\\', '/', dirname($script));
    if ($base === '/' || $base === '\\') {
        $base = '';
    }

    $path = ltrim($path, '/');
    return ($base === '' ? '' : $base) . ($path === '' ? '' : '/' . $path);
}

function redirect_to(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_field(): string
{
    return Csrf::field();
}

function old(string $key, string $default = ''): string
{
    $old = Session::get('_old', []);
    return e(is_array($old) && isset($old[$key]) ? (string) $old[$key] : $default);
}

function flash(string $key): mixed
{
    return Session::flash($key);
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

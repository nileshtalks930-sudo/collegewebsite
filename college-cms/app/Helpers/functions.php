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

function public_url(string $path = ''): string
{
    $configured = (string) (app_config('public_url') ?? '');
    if ($configured !== '') {
        return rtrim($configured, '/') . '/' . ltrim($path, '/');
    }

    $script = $_SERVER['SCRIPT_NAME'] ?? '/admin/index.php';
    $adminBase = str_replace('\\', '/', dirname($script));
    if (str_ends_with($adminBase, '/admin')) {
        $publicBase = substr($adminBase, 0, -6) . '/public';
    } else {
        $publicBase = $adminBase . '/../public';
    }

    $path = ltrim($path, '/');
    return rtrim($publicBase, '/') . ($path === '' ? '' : '/' . $path);
}

function upload_url(?string $relativePath): string
{
    if ($relativePath === null || $relativePath === '') {
        return '';
    }
    return public_url(ltrim($relativePath, '/'));
}

function absolute_url(string $path): string
{
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . '/' . ltrim($path, '/');
}

function admin_asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function can(string $permission): bool
{
    return \App\Core\Auth::can($permission);
}

function current_path(): string
{
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($scriptDir !== '/' && $scriptDir !== '' && str_starts_with($path, $scriptDir)) {
        $path = substr($path, strlen($scriptDir)) ?: '/';
    }
    return '/' . trim($path, '/');
}

function nav_active(string $prefix): string
{
    $path = current_path();
    $prefix = '/' . trim($prefix, '/');
    if ($prefix === '/') {
        return $path === '/' || $path === '/dashboard' ? 'active' : '';
    }
    return $path === $prefix || str_starts_with($path, $prefix . '/') ? 'active' : '';
}

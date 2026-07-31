<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\RememberToken;
use App\Models\Role;
use App\Models\User;

final class Auth
{
    private const SESSION_USER_ID = 'auth_user_id';

    public static function attempt(string $email, string $password, bool $remember = false): bool
    {
        $user = User::findByEmail($email);

        if ($user === null || (int) ($user['status'] ?? 0) !== 1) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            User::updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        }

        self::loginUser($user);

        if ($remember) {
            self::createRememberToken((int) $user['id']);
        }

        User::touchLastLogin((int) $user['id']);

        return true;
    }

    public static function loginUser(array $user): void
    {
        Session::regenerate(true);
        Session::set(self::SESSION_USER_ID, (int) $user['id']);
        Session::set('auth_user', self::toSessionUser($user));
    }

    public static function check(): bool
    {
        if (Session::has(self::SESSION_USER_ID)) {
            return true;
        }

        return self::loginFromRememberCookie();
    }

    public static function id(): ?int
    {
        $id = Session::get(self::SESSION_USER_ID);
        return $id !== null ? (int) $id : null;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        $cached = Session::get('auth_user');
        if (is_array($cached) && isset($cached['role_slug'], $cached['permissions'])) {
            return $cached;
        }

        $user = User::findById((int) Session::get(self::SESSION_USER_ID));
        if ($user === null) {
            self::logout();
            return null;
        }

        $lite = self::toSessionUser($user);
        Session::set('auth_user', $lite);

        return $lite;
    }

    public static function can(string $permission): bool
    {
        $user = self::user();
        if ($user === null) {
            return false;
        }

        if (($user['role_slug'] ?? '') === 'super_admin') {
            return true;
        }

        $permissions = $user['permissions'] ?? [];
        return is_array($permissions) && in_array($permission, $permissions, true);
    }

    public static function hasRole(string $slug): bool
    {
        $user = self::user();
        return $user !== null && ($user['role_slug'] ?? '') === $slug;
    }

    public static function logout(): void
    {
        $userId = self::id();
        self::clearRememberCookie($userId);
        Session::remove(self::SESSION_USER_ID);
        Session::remove('auth_user');
        Session::regenerate(true);
    }

    /** @return array{id:int,name:string,email:string,role_id:int,role:string,role_slug:string,role_name:string,permissions:list<string>} */
    private static function toSessionUser(array $user): array
    {
        $roleId = (int) ($user['role_id'] ?? 0);
        $slug = (string) ($user['role_slug'] ?? $user['role'] ?? '');
        $name = (string) ($user['role_name'] ?? $slug);

        return [
            'id' => (int) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
            'role_id' => $roleId,
            'role' => $slug,
            'role_slug' => $slug,
            'role_name' => $name,
            'permissions' => $roleId > 0 ? Role::permissionSlugs($roleId) : [],
        ];
    }

    private static function createRememberToken(int $userId): void
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        $days = (int) ($app['remember']['days'] ?? 30);
        $cookieName = $app['remember']['cookie'] ?? 'college_cms_remember';

        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $hash = hash('sha256', $validator);
        $expires = (new \DateTimeImmutable("+{$days} days"))->format('Y-m-d H:i:s');

        RememberToken::create($userId, $selector, $hash, $expires);

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie($cookieName, $selector . ':' . $validator, [
            'expires' => time() + ($days * 86400),
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function loginFromRememberCookie(): bool
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        $cookieName = $app['remember']['cookie'] ?? 'college_cms_remember';
        $raw = $_COOKIE[$cookieName] ?? null;

        if (!is_string($raw) || !str_contains($raw, ':')) {
            return false;
        }

        [$selector, $validator] = explode(':', $raw, 2);
        if ($selector === '' || $validator === '') {
            return false;
        }

        $token = RememberToken::findValidBySelector($selector);
        if ($token === null) {
            self::expireCookie($cookieName);
            return false;
        }

        if (!hash_equals($token['token_hash'], hash('sha256', $validator))) {
            RememberToken::deleteBySelector($selector);
            self::expireCookie($cookieName);
            return false;
        }

        $user = User::findById((int) $token['user_id']);
        if ($user === null || (int) $user['status'] !== 1) {
            RememberToken::deleteBySelector($selector);
            self::expireCookie($cookieName);
            return false;
        }

        RememberToken::deleteBySelector($selector);
        self::loginUser($user);
        self::createRememberToken((int) $user['id']);

        return true;
    }

    private static function clearRememberCookie(?int $userId): void
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        $cookieName = $app['remember']['cookie'] ?? 'college_cms_remember';
        $raw = $_COOKIE[$cookieName] ?? null;

        if (is_string($raw) && str_contains($raw, ':')) {
            [$selector] = explode(':', $raw, 2);
            RememberToken::deleteBySelector($selector);
        }

        if ($userId !== null) {
            RememberToken::deleteByUserId($userId);
        }

        self::expireCookie($cookieName);
    }

    private static function expireCookie(string $name): void
    {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE[$name]);
    }
}

<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\RememberToken;
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

        // Rehash if algorithm/options changed
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
        Session::set('auth_user', [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ]);
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
        if (is_array($cached)) {
            return $cached;
        }

        $user = User::findById((int) Session::get(self::SESSION_USER_ID));
        if ($user === null) {
            self::logout();
            return null;
        }

        $lite = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        Session::set('auth_user', $lite);

        return $lite;
    }

    public static function logout(): void
    {
        $userId = self::id();
        self::clearRememberCookie($userId);
        Session::remove(self::SESSION_USER_ID);
        Session::remove('auth_user');
        Session::regenerate(true);
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

        // Rotate remember token
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

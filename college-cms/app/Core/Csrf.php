<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        $key = $app['csrf_token_key'] ?? '_csrf_token';

        $token = Session::get($key);
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set($key, $token);
        }

        return $token;
    }

    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }

    public static function validate(?string $token): bool
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        $key = $app['csrf_token_key'] ?? '_csrf_token';
        $sessionToken = Session::get($key);

        return is_string($token)
            && is_string($sessionToken)
            && hash_equals($sessionToken, $token);
    }
}

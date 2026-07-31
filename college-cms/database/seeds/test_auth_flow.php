<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/app/Core/Autoload.php';
require dirname(__DIR__, 2) . '/app/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Session;
use App\Models\PasswordReset;
use App\Models\User;

Session::start(['name' => 'test', 'lifetime' => 3600]);

$ok = Auth::attempt('admin@college.local', 'Admin@123', true);
echo 'login_ok=' . ($ok ? '1' : '0') . PHP_EOL;
echo 'check=' . (Auth::check() ? '1' : '0') . PHP_EOL;

$user = Auth::user();
echo 'user=' . ($user['email'] ?? 'null') . ' role=' . ($user['role'] ?? '') . PHP_EOL;

$bad = Auth::attempt('admin@college.local', 'wrong', false);
echo 'bad_login=' . ($bad ? '1' : '0') . PHP_EOL;

$plain = bin2hex(random_bytes(16));
PasswordReset::create(
    'admin@college.local',
    hash('sha256', $plain),
    (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s')
);
$row = PasswordReset::findValid('admin@college.local', hash('sha256', $plain));
echo 'reset_token_ok=' . ($row ? '1' : '0') . PHP_EOL;

User::updatePassword((int) $user['id'], password_hash('NewPass@123', PASSWORD_DEFAULT));
Auth::logout();

$re = Auth::attempt('admin@college.local', 'NewPass@123', false);
echo 'relogin_new_pass=' . ($re ? '1' : '0') . PHP_EOL;

User::updatePassword(1, password_hash('Admin@123', PASSWORD_DEFAULT));
Auth::logout();

echo "DONE\n";

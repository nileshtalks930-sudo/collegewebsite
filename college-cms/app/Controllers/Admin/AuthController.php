<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\PasswordReset;
use App\Models\User;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        AuthMiddleware::guest();
        $this->view('admin.auth.login', [
            'title' => 'Admin Login',
            'error' => flash('error'),
            'success' => flash('success'),
        ]);
    }

    public function login(): void
    {
        AuthMiddleware::guest();
        $this->validateCsrf();

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $remember = isset($_POST['remember']);

        Session::set('_old', ['email' => $email]);

        if ($email === '' || $password === '') {
            Session::flash('error', 'Email and password are required.');
            $this->redirect('login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid email address.');
            $this->redirect('login');
        }

        try {
            $ok = Auth::attempt($email, $password, $remember);
        } catch (\Throwable $e) {
            Session::flash('error', 'Unable to sign in. Check database configuration.');
            if (app_config('debug')) {
                Session::flash('error', 'Unable to sign in: ' . $e->getMessage());
            }
            $this->redirect('login');
        }

        if (!$ok) {
            Session::flash('error', 'Invalid email or password.');
            $this->redirect('login');
        }

        Session::remove('_old');
        $this->redirect('dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        Session::flash('success', 'You have been logged out.');
        $this->redirect('login');
    }

    public function showForgotPassword(): void
    {
        AuthMiddleware::guest();
        $this->view('admin.auth.forgot-password', [
            'title' => 'Forgot Password',
            'error' => flash('error'),
            'success' => flash('success'),
            'reset_link' => flash('reset_link'),
        ]);
    }

    public function sendResetLink(): void
    {
        AuthMiddleware::guest();
        $this->validateCsrf();

        $email = trim((string) ($_POST['email'] ?? ''));
        Session::set('_old', ['email' => $email]);

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid email address.');
            $this->redirect('forgot-password');
        }

        $user = User::findByEmail($email);

        // Always show success to avoid account enumeration
        $generic = 'If that email exists in our system, a password reset link has been generated.';

        if ($user !== null && (int) $user['status'] === 1) {
            $plainToken = bin2hex(random_bytes(32));
            $hash = hash('sha256', $plainToken);
            $expires = (new \DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

            PasswordReset::create($email, $hash, $expires);

            $link = url('reset-password?email=' . urlencode(strtolower($email)) . '&token=' . urlencode($plainToken));

            $this->logResetLink($email, $link);

            // In local/debug mode surface the link (no mailer configured yet)
            if (app_config('debug')) {
                Session::flash('reset_link', $link);
            }
        }

        Session::flash('success', $generic);
        $this->redirect('forgot-password');
    }

    public function showResetPassword(): void
    {
        AuthMiddleware::guest();

        $email = trim((string) ($_GET['email'] ?? ''));
        $token = trim((string) ($_GET['token'] ?? ''));

        if ($email === '' || $token === '') {
            Session::flash('error', 'Invalid or expired reset link.');
            $this->redirect('forgot-password');
        }

        $this->view('admin.auth.reset-password', [
            'title' => 'Reset Password',
            'email' => $email,
            'token' => $token,
            'error' => flash('error'),
            'success' => flash('success'),
        ]);
    }

    public function resetPassword(): void
    {
        AuthMiddleware::guest();
        $this->validateCsrf();

        $email = trim((string) ($_POST['email'] ?? ''));
        $token = trim((string) ($_POST['token'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirmation'] ?? '');

        if ($email === '' || $token === '') {
            Session::flash('error', 'Invalid reset request.');
            $this->redirect('forgot-password');
        }

        if (strlen($password) < 8) {
            Session::flash('error', 'Password must be at least 8 characters.');
            $this->redirect('reset-password?email=' . urlencode($email) . '&token=' . urlencode($token));
        }

        if (!hash_equals($password, $confirm)) {
            Session::flash('error', 'Password confirmation does not match.');
            $this->redirect('reset-password?email=' . urlencode($email) . '&token=' . urlencode($token));
        }

        $row = PasswordReset::findValid($email, hash('sha256', $token));
        if ($row === null) {
            Session::flash('error', 'This reset link is invalid or has expired.');
            $this->redirect('forgot-password');
        }

        $user = User::findByEmail($email);
        if ($user === null) {
            Session::flash('error', 'Unable to reset password for this account.');
            $this->redirect('forgot-password');
        }

        User::updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        PasswordReset::deleteByEmail($email);

        Session::flash('success', 'Password updated successfully. You can now sign in.');
        $this->redirect('login');
    }

    private function logResetLink(string $email, string $link): void
    {
        $logDir = base_path('storage/logs');
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        $line = sprintf("[%s] password_reset email=%s link=%s\n", date('c'), $email, $link);
        file_put_contents($logDir . '/password_resets.log', $line, FILE_APPEND | LOCK_EX);
    }
}

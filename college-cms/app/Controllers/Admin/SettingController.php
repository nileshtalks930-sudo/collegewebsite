<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Middleware\RoleMiddleware;
use App\Models\Setting;

final class SettingController extends Controller
{
    public function index(): void
    {
        RoleMiddleware::permission('settings.view');

        $this->view('admin.settings.index', [
            'title' => 'Site Settings',
            'settings' => Setting::allKeyed(),
            'groups' => Setting::GROUPS,
            'success' => flash('success'),
            'error' => flash('error'),
            'canManage' => Auth::can('settings.manage'),
            'activeTab' => trim((string) ($_GET['tab'] ?? 'general')),
        ], 'admin.layouts.app');
    }

    public function update(): void
    {
        RoleMiddleware::permission('settings.manage');
        $this->validateCsrf();

        $current = Setting::allKeyed();
        $uploads = Setting::handleUploads($current);
        if ($uploads['error'] !== null) {
            Session::flash('error', $uploads['error']);
            $this->redirect('settings?tab=general');
        }

        $values = [
            'website_name' => $this->str('website_name', 200),
            'logo' => $uploads['logo'],
            'favicon' => $uploads['favicon'],
            'address' => $this->str('address', 2000),
            'email' => $this->str('email', 190),
            'phone' => $this->str('phone', 50),
            'google_map' => $this->str('google_map', 5000),
            'social_facebook' => $this->str('social_facebook', 500),
            'social_twitter' => $this->str('social_twitter', 500),
            'social_instagram' => $this->str('social_instagram', 500),
            'social_youtube' => $this->str('social_youtube', 500),
            'social_linkedin' => $this->str('social_linkedin', 500),
            'smtp_host' => $this->str('smtp_host', 255),
            'smtp_port' => $this->str('smtp_port', 10),
            'smtp_username' => $this->str('smtp_username', 255),
            'smtp_encryption' => $this->str('smtp_encryption', 20),
            'smtp_from_email' => $this->str('smtp_from_email', 190),
            'smtp_from_name' => $this->str('smtp_from_name', 150),
            'analytics_code' => $this->raw('analytics_code', 20000),
            'footer_text' => $this->str('footer_text', 5000),
            'copyright' => $this->str('copyright', 500),
        ];

        // Keep existing password unless a new one is provided
        $newPassword = trim((string) ($_POST['smtp_password'] ?? ''));
        if ($newPassword !== '') {
            $values['smtp_password'] = $newPassword;
        } else {
            $values['smtp_password'] = $current['smtp_password'] ?? null;
        }

        $email = $values['email'] ?? '';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid contact email address.');
            $this->redirect('settings?tab=contact');
        }

        $fromEmail = $values['smtp_from_email'] ?? '';
        if ($fromEmail !== '' && !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid SMTP from email.');
            $this->redirect('settings?tab=smtp');
        }

        $encryption = strtolower((string) ($values['smtp_encryption'] ?? 'tls'));
        if (!in_array($encryption, ['', 'none', 'tls', 'ssl'], true)) {
            Session::flash('error', 'SMTP encryption must be none, tls, or ssl.');
            $this->redirect('settings?tab=smtp');
        }
        $values['smtp_encryption'] = $encryption === 'none' ? '' : $encryption;

        $websiteName = trim((string) ($values['website_name'] ?? ''));
        if ($websiteName === '') {
            Session::flash('error', 'Website name is required.');
            $this->redirect('settings?tab=general');
        }

        try {
            Setting::setMany($values);
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to save settings.');
            $this->redirect('settings');
        }

        Session::flash('success', 'Settings saved successfully.');
        $tab = trim((string) ($_POST['_tab'] ?? 'general'));
        if (!isset(Setting::GROUPS[$tab])) {
            $tab = 'general';
        }
        $this->redirect('settings?tab=' . urlencode($tab));
    }

    private function str(string $key, int $max): ?string
    {
        $value = trim((string) ($_POST[$key] ?? ''));
        if ($value === '') {
            return null;
        }
        if (mb_strlen($value) > $max) {
            $value = mb_substr($value, 0, $max);
        }
        return $value;
    }

    private function raw(string $key, int $max): ?string
    {
        $value = (string) ($_POST[$key] ?? '');
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        if (mb_strlen($value) > $max) {
            $value = mb_substr($value, 0, $max);
        }
        return $value;
    }
}

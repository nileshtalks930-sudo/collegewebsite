<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Helpers\DatabaseConfig;
use App\Middleware\RoleMiddleware;

final class DatabaseConfigController extends Controller
{
    public function index(): void
    {
        $this->authorize();

        $config = DatabaseConfig::read();

        $this->view('admin.settings.database', [
            'title' => 'Database Configuration',
            'config' => $config,
            'success' => flash('success'),
            'error' => flash('error'),
            'info' => flash('info'),
            'old' => Session::get('_old_db', []),
            'canManage' => true,
            'configPath' => 'config/database.php',
            'writable' => is_writable(DatabaseConfig::path()) || (!is_file(DatabaseConfig::path()) && is_writable(dirname(DatabaseConfig::path()))),
        ], 'admin.layouts.app');

        Session::remove('_old_db');
    }

    public function test(): void
    {
        $this->authorize();
        $this->validateCsrf();

        $input = $this->inputFromRequest();
        Session::set('_old_db', $this->oldFromInput($input));

        $result = DatabaseConfig::validate($input);
        if ($result['ok']) {
            Session::flash('info', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('settings/database');
    }

    public function update(): void
    {
        $this->authorize();
        $this->validateCsrf();

        $input = $this->inputFromRequest();
        Session::set('_old_db', $this->oldFromInput($input));

        $result = DatabaseConfig::write($input);
        if (!$result['ok']) {
            Session::flash('error', $result['message']);
            $this->redirect('settings/database');
        }

        Database::reset();
        Session::remove('_old_db');
        Session::flash('success', $result['message']);
        $this->redirect('settings/database');
    }

    private function authorize(): void
    {
        // Super Admin only — writing DB credentials is a privileged operation
        RoleMiddleware::role('super_admin');
    }

    /**
     * @return array{host:string,port:string,database:string,username:string,password:string,charset:string}
     */
    private function inputFromRequest(): array
    {
        $current = DatabaseConfig::read();

        $host = trim((string) ($_POST['host'] ?? ''));
        $port = trim((string) ($_POST['port'] ?? ''));
        $database = trim((string) ($_POST['database'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $keepPassword = isset($_POST['keep_password']) || $password === '';

        if ($keepPassword && $password === '') {
            $password = $current['password'];
        }

        return [
            'host' => $host,
            'port' => $port !== '' ? $port : '3306',
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'charset' => $current['charset'] ?: 'utf8mb4',
        ];
    }

    /** @param array<string,string> $input @return array<string,string> */
    private function oldFromInput(array $input): array
    {
        return [
            'host' => $input['host'],
            'port' => $input['port'],
            'database' => $input['database'],
            'username' => $input['username'],
            // never echo password back into the form value
        ];
    }
}

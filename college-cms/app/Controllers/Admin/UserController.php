<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Middleware\RoleMiddleware;
use App\Models\Role;
use App\Models\User;

final class UserController extends Controller
{
    public function index(): void
    {
        RoleMiddleware::permission('users.view');

        $search = trim((string) ($_GET['q'] ?? ''));

        $this->view('admin.users.index', [
            'title' => 'Users',
            'users' => User::all($search !== '' ? $search : null),
            'search' => $search,
            'success' => flash('success'),
            'error' => flash('error'),
            'canCreate' => Auth::can('users.create'),
            'canUpdate' => Auth::can('users.update'),
            'canDelete' => Auth::can('users.delete'),
        ], 'admin.layouts.app');
    }

    public function create(): void
    {
        RoleMiddleware::permission('users.create');

        $this->view('admin.users.form', [
            'title' => 'Create User',
            'user' => null,
            'roles' => $this->assignableRoles(),
            'error' => flash('error'),
            'old' => Session::get('_old', []),
        ], 'admin.layouts.app');
    }

    public function store(): void
    {
        RoleMiddleware::permission('users.create');
        $this->validateCsrf();

        $data = $this->validatedInput();
        Session::set('_old', [
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'status' => $data['status'],
        ]);

        if ($data['error'] !== null) {
            Session::flash('error', $data['error']);
            $this->redirect('users/create');
        }

        if (User::emailExists($data['email'])) {
            Session::flash('error', 'Email is already registered.');
            $this->redirect('users/create');
        }

        if (!$this->canAssignRole((int) $data['role_id'])) {
            Session::flash('error', 'You cannot assign that role.');
            $this->redirect('users/create');
        }

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role_id' => $data['role_id'],
            'status' => $data['status'],
        ]);

        Session::remove('_old');
        Session::flash('success', 'User created successfully.');
        $this->redirect('users');
    }

    public function edit(string $id): void
    {
        RoleMiddleware::permission('users.update');

        $user = User::findById((int) $id);
        if ($user === null) {
            Session::flash('error', 'User not found.');
            $this->redirect('users');
        }

        $this->view('admin.users.form', [
            'title' => 'Edit User',
            'user' => $user,
            'roles' => $this->assignableRoles(),
            'error' => flash('error'),
            'old' => Session::get('_old', []),
        ], 'admin.layouts.app');
    }

    public function update(string $id): void
    {
        RoleMiddleware::permission('users.update');
        $this->validateCsrf();

        $userId = (int) $id;
        $existing = User::findById($userId);
        if ($existing === null) {
            Session::flash('error', 'User not found.');
            $this->redirect('users');
        }

        $data = $this->validatedInput(true);
        Session::set('_old', [
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'status' => $data['status'],
        ]);

        if ($data['error'] !== null) {
            Session::flash('error', $data['error']);
            $this->redirect('users/' . $userId . '/edit');
        }

        if (User::emailExists($data['email'], $userId)) {
            Session::flash('error', 'Email is already registered.');
            $this->redirect('users/' . $userId . '/edit');
        }

        if (!$this->canAssignRole((int) $data['role_id'])) {
            Session::flash('error', 'You cannot assign that role.');
            $this->redirect('users/' . $userId . '/edit');
        }

        // Prevent demoting/locking the last super admin
        if (
            ($existing['role_slug'] ?? '') === 'super_admin'
            && ((int) $data['role_id'] !== (int) $existing['role_id'] || (int) $data['status'] !== 1)
            && $this->countActiveSuperAdmins() <= 1
        ) {
            Session::flash('error', 'Cannot demote or disable the last Super Admin.');
            $this->redirect('users/' . $userId . '/edit');
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'status' => $data['status'],
        ];

        if ($data['password'] !== '') {
            $payload['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        User::updateUser($userId, $payload);

        // Refresh session if editing self
        if (Auth::id() === $userId) {
            $fresh = User::findById($userId);
            if ($fresh !== null) {
                Auth::loginUser($fresh);
            }
        }

        Session::remove('_old');
        Session::flash('success', 'User updated successfully.');
        $this->redirect('users');
    }

    public function destroy(string $id): void
    {
        RoleMiddleware::permission('users.delete');
        $this->validateCsrf();

        $userId = (int) $id;
        $existing = User::findById($userId);

        if ($existing === null) {
            Session::flash('error', 'User not found.');
            $this->redirect('users');
        }

        if (Auth::id() === $userId) {
            Session::flash('error', 'You cannot delete your own account.');
            $this->redirect('users');
        }

        if (($existing['role_slug'] ?? '') === 'super_admin' && $this->countActiveSuperAdmins() <= 1) {
            Session::flash('error', 'Cannot delete the last Super Admin.');
            $this->redirect('users');
        }

        User::deleteById($userId);
        Session::flash('success', 'User deleted successfully.');
        $this->redirect('users');
    }

    /** @return array{name:string,email:string,password:string,role_id:int,status:int,error:?string} */
    private function validatedInput(bool $isUpdate = false): array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        $error = null;

        if ($name === '' || mb_strlen($name) > 120) {
            $error = 'Name is required (max 120 characters).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'A valid email is required.';
        } elseif ($roleId <= 0 || Role::findById($roleId) === null) {
            $error = 'Please select a valid role.';
        } elseif (!$isUpdate && strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== '' && strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        }

        return [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role_id' => $roleId,
            'status' => $status,
            'error' => $error,
        ];
    }

    /** @return list<array<string,mixed>> */
    private function assignableRoles(): array
    {
        $roles = Role::all();

        // Only Super Admin can assign Super Admin
        if (!Auth::hasRole('super_admin')) {
            $roles = array_values(array_filter(
                $roles,
                static fn (array $r): bool => ($r['slug'] ?? '') !== 'super_admin'
            ));
        }

        return $roles;
    }

    private function canAssignRole(int $roleId): bool
    {
        $role = Role::findById($roleId);
        if ($role === null) {
            return false;
        }

        if (($role['slug'] ?? '') === 'super_admin' && !Auth::hasRole('super_admin')) {
            return false;
        }

        return true;
    }

    private function countActiveSuperAdmins(): int
    {
        $role = Role::findBySlug('super_admin');
        if ($role === null) {
            return 0;
        }

        $stmt = \App\Core\Database::connection()->prepare(
            'SELECT COUNT(*) FROM users WHERE role_id = :role_id AND status = 1'
        );
        $stmt->execute(['role_id' => (int) $role['id']]);

        return (int) $stmt->fetchColumn();
    }
}

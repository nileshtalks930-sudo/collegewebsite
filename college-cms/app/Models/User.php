<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

final class User extends Model
{
    private const SELECT_WITH_ROLE = 'SELECT u.*, r.name AS role_name, r.slug AS role_slug
        FROM users u
        INNER JOIN roles r ON r.id = u.role_id';

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare(self::SELECT_WITH_ROLE . ' WHERE u.email = :email LIMIT 1');
        $stmt->execute(['email' => strtolower(trim($email))]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare(self::SELECT_WITH_ROLE . ' WHERE u.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function all(?string $search = null): array
    {
        $sql = self::SELECT_WITH_ROLE;
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= ' WHERE (u.name LIKE :q OR u.email LIKE :q2)';
            $like = '%' . $search . '%';
            $params['q'] = $like;
            $params['q2'] = $like;
        }

        $sql .= ' ORDER BY u.id ASC';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO users (name, email, password, role_id, status, created_at)
             VALUES (:name, :email, :password, :role_id, :status, NOW())'
        );
        $stmt->execute([
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'password' => $data['password'],
            'role_id' => (int) $data['role_id'],
            'status' => (int) $data['status'],
        ]);

        return (int) self::db()->lastInsertId();
    }

    public static function updateUser(int $id, array $data): void
    {
        $fields = [
            'name = :name',
            'email = :email',
            'role_id = :role_id',
            'status = :status',
            'updated_at = NOW()',
        ];
        $params = [
            'id' => $id,
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'role_id' => (int) $data['role_id'],
            'status' => (int) $data['status'],
        ];

        if (!empty($data['password'])) {
            $fields[] = 'password = :password';
            $params['password'] = $data['password'];
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
    }

    public static function deleteById(int $id): void
    {
        $stmt = self::db()->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';
        $params = ['email' => strtolower(trim($email))];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function updatePassword(int $id, string $hash): void
    {
        $stmt = self::db()->prepare('UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['password' => $hash, 'id' => $id]);
    }

    public static function touchLastLogin(int $id): void
    {
        $stmt = self::db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function countActive(): int
    {
        return (int) self::db()->query('SELECT COUNT(*) FROM users WHERE status = 1')->fetchColumn();
    }
}

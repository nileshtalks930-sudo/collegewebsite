<?php

declare(strict_types=1);

namespace App\Helpers;

use PDO;
use PDOException;
use Throwable;

final class DatabaseConfig
{
    public static function path(): string
    {
        return base_path('config/database.php');
    }

    /** @return array{host:string,port:string,database:string,username:string,password:string,charset:string} */
    public static function read(): array
    {
        $path = self::path();
        $config = is_file($path) ? require $path : [];

        return [
            'host' => (string) ($config['host'] ?? '127.0.0.1'),
            'port' => (string) ($config['port'] ?? '3306'),
            'database' => (string) ($config['database'] ?? 'college_cms'),
            'username' => (string) ($config['username'] ?? 'root'),
            'password' => (string) ($config['password'] ?? ''),
            'charset' => (string) ($config['charset'] ?? 'utf8mb4'),
        ];
    }

    /**
     * @param array{host:string,port:string,database:string,username:string,password:string,charset?:string} $config
     * @return array{ok:bool,message:string}
     */
    public static function validate(array $config): array
    {
        $host = trim($config['host']);
        $port = trim($config['port']);
        $database = trim($config['database']);
        $username = trim($config['username']);
        $password = (string) $config['password'];
        $charset = trim((string) ($config['charset'] ?? 'utf8mb4')) ?: 'utf8mb4';

        if ($host === '') {
            return ['ok' => false, 'message' => 'Host is required.'];
        }
        if (strlen($host) > 255) {
            return ['ok' => false, 'message' => 'Host is too long.'];
        }
        if ($database === '') {
            return ['ok' => false, 'message' => 'Database name is required.'];
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $database)) {
            return ['ok' => false, 'message' => 'Database name may only contain letters, numbers, and underscores.'];
        }
        if ($username === '') {
            return ['ok' => false, 'message' => 'Username is required.'];
        }
        if ($port === '' || !ctype_digit($port)) {
            return ['ok' => false, 'message' => 'Port must be a number.'];
        }
        $portNum = (int) $port;
        if ($portNum < 1 || $portNum > 65535) {
            return ['ok' => false, 'message' => 'Port must be between 1 and 65535.'];
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $host,
            $portNum,
            $database,
            $charset
        );

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            $pdo->query('SELECT 1');
            $pdo = null;
        } catch (PDOException $e) {
            return [
                'ok' => false,
                'message' => 'Connection failed: ' . self::safeErrorMessage($e->getMessage()),
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Connection failed: ' . self::safeErrorMessage($e->getMessage()),
            ];
        }

        return ['ok' => true, 'message' => 'Connection successful.'];
    }

    /**
     * @param array{host:string,port:string,database:string,username:string,password:string,charset?:string} $config
     * @return array{ok:bool,message:string}
     */
    public static function write(array $config): array
    {
        $path = self::path();
        $dir = dirname($path);

        if (!is_dir($dir)) {
            return ['ok' => false, 'message' => 'Config directory does not exist.'];
        }
        if (is_file($path) && !is_writable($path)) {
            return ['ok' => false, 'message' => 'config/database.php is not writable.'];
        }
        if (!is_file($path) && !is_writable($dir)) {
            return ['ok' => false, 'message' => 'Config directory is not writable.'];
        }

        $validation = self::validate($config);
        if (!$validation['ok']) {
            return $validation;
        }

        $host = trim($config['host']);
        $port = trim($config['port']);
        $database = trim($config['database']);
        $username = trim($config['username']);
        $password = (string) $config['password'];
        $charset = trim((string) ($config['charset'] ?? 'utf8mb4')) ?: 'utf8mb4';

        $contents = self::renderFile([
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'charset' => $charset,
        ]);

        if (is_file($path)) {
            @copy($path, $path . '.bak');
        }

        $written = @file_put_contents($path, $contents, LOCK_EX);
        if ($written === false) {
            return ['ok' => false, 'message' => 'Failed to write config/database.php.'];
        }

        // Ensure PHP can parse the new file
        try {
            $loaded = require $path;
            if (!is_array($loaded) || empty($loaded['host']) || empty($loaded['database'])) {
                return ['ok' => false, 'message' => 'Wrote config file but it could not be loaded correctly.'];
            }
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Wrote config file but PHP failed to load it.'];
        }

        return ['ok' => true, 'message' => 'Database configuration saved to config/database.php.'];
    }

    /** @param array{host:string,port:string,database:string,username:string,password:string,charset:string} $config */
    public static function renderFile(array $config): string
    {
        $host = var_export($config['host'], true);
        $port = var_export((string) $config['port'], true);
        $database = var_export($config['database'], true);
        $username = var_export($config['username'], true);
        $password = var_export($config['password'], true);
        $charset = var_export($config['charset'], true);

        return <<<PHP
<?php

declare(strict_types=1);

/**
 * Database connection settings.
 * Managed by Admin → Settings → Database Configuration.
 * A backup is saved as database.php.bak on each successful write.
 */
return [
    'host' => {$host},
    'port' => {$port},
    'database' => {$database},
    'username' => {$username},
    'password' => {$password},
    'charset' => {$charset},
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];

PHP;
    }

    private static function safeErrorMessage(string $message): string
    {
        // Avoid leaking full credentials if the driver echoes the DSN oddly
        $message = preg_replace('/password=[^;\s]*/i', 'password=***', $message) ?? $message;
        return substr($message, 0, 300);
    }
}

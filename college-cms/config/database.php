<?php

declare(strict_types=1);

/**
 * Database connection settings.
 * If .preview-local exists in the project root, local preview credentials are used.
 * Otherwise cPanel credentials are used.
 */
$previewLocal = is_file(dirname(__DIR__) . '/.preview-local');

if ($previewLocal) {
    return [
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'college_cms',
        'username' => 'cms',
        'password' => 'cms_preview',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ];
}

return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_DATABASE') ?: 'nileshta_cmcs2',
    'username' => getenv('DB_USERNAME') ?: 'nileshta_cmcs2',
    'password' => (getenv('DB_PASSWORD') !== false && getenv('DB_PASSWORD') !== '')
        ? (string) getenv('DB_PASSWORD')
        : 'Shree@123456',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];

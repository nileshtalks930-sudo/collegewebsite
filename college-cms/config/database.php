<?php

declare(strict_types=1);

/**
 * Database connection settings.
 * Managed by Admin → Settings → Database Configuration.
 */
return [
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'nileshta_cmcs2',
    'username' => 'nileshta_cmcs2',
    'password' => 'Shree@123456',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];

#!/usr/bin/env php
<?php
// Local preview wrapper — forces local MySQL credentials
putenv('DB_HOST=127.0.0.1');
putenv('DB_PORT=3306');
putenv('DB_DATABASE=college_cms');
putenv('DB_USERNAME=cms');
putenv('DB_PASSWORD=cms_preview');
$_SERVER['DB_HOST'] = '127.0.0.1';

// Built-in server router
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}
require __DIR__ . '/index.php';

<?php

declare(strict_types=1);

/**
 * Public site bootstrap — loads config, autoload, router.
 */

use App\Core\Router;
use App\Core\Session;

define('CMS_ROOT', dirname(__DIR__, 2));

require CMS_ROOT . '/app/Core/Autoload.php';
require_once CMS_ROOT . '/app/Helpers/functions.php';

$app = require CMS_ROOT . '/config/app.php';
date_default_timezone_set($app['timezone'] ?? 'UTC');

Session::start($app['session'] ?? []);

$router = new Router();
require CMS_ROOT . '/routes/web.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$path = parse_url($uri, PHP_URL_PATH) ?: '/';
if ($scriptDir !== '/' && $scriptDir !== '' && str_starts_with($path, $scriptDir)) {
    $path = substr($path, strlen($scriptDir)) ?: '/';
}

$router->dispatch($method, $path);

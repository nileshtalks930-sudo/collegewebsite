<?php

declare(strict_types=1);

use App\Controllers\Frontend\HomeController;
use App\Core\Router;

/** @var Router $router */

$router->get('/', [HomeController::class, 'index']);
$router->get('/home', [HomeController::class, 'index']);

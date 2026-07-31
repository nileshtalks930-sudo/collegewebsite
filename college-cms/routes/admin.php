<?php

declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\DepartmentController;
use App\Controllers\Admin\MenuController;
use App\Controllers\Admin\PageController;
use App\Controllers\Admin\UserController;
use App\Core\Router;

/** @var Router $router */

$router->get('/', [AuthController::class, 'showLogin']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'sendResetLink']);
$router->get('/reset-password', [AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);

$router->get('/dashboard', [DashboardController::class, 'index']);

// User Management
$router->get('/users', [UserController::class, 'index']);
$router->get('/users/create', [UserController::class, 'create']);
$router->post('/users', [UserController::class, 'store']);
$router->get('/users/{id}/edit', [UserController::class, 'edit']);
$router->post('/users/{id}/update', [UserController::class, 'update']);
$router->post('/users/{id}/delete', [UserController::class, 'destroy']);

// Menu Management
$router->get('/menus', [MenuController::class, 'index']);
$router->get('/menus/create', [MenuController::class, 'create']);
$router->post('/menus', [MenuController::class, 'store']);
$router->post('/menus/reorder', [MenuController::class, 'reorder']);
$router->get('/menus/{id}/edit', [MenuController::class, 'edit']);
$router->post('/menus/{id}/update', [MenuController::class, 'update']);
$router->post('/menus/{id}/delete', [MenuController::class, 'destroy']);

// Page Builder
$router->get('/pages', [PageController::class, 'index']);
$router->get('/pages/create', [PageController::class, 'create']);
$router->post('/pages', [PageController::class, 'store']);
$router->get('/pages/{id}/edit', [PageController::class, 'edit']);
$router->post('/pages/{id}/update', [PageController::class, 'update']);
$router->post('/pages/{id}/delete', [PageController::class, 'destroy']);

// Department Module
$router->get('/departments', [DepartmentController::class, 'index']);
$router->get('/departments/create', [DepartmentController::class, 'create']);
$router->post('/departments', [DepartmentController::class, 'store']);
$router->get('/departments/{id}/edit', [DepartmentController::class, 'edit']);
$router->post('/departments/{id}/update', [DepartmentController::class, 'update']);
$router->post('/departments/{id}/delete', [DepartmentController::class, 'destroy']);
$router->post('/departments/{id}/downloads/{downloadId}/delete', [DepartmentController::class, 'deleteDownload']);
$router->post('/departments/{id}/gallery/{imageId}/delete', [DepartmentController::class, 'deleteGallery']);

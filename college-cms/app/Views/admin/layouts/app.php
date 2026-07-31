<?php
/** @var string $content */
/** @var string $title */
/** @var array|null $user */

$user = $user ?? \App\Core\Auth::user();
$roleLabel = $user['role_name'] ?? ucfirst(str_replace('_', ' ', (string) ($user['role_slug'] ?? $user['role'] ?? '')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Dashboard') ?> | College CMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-rc4/dist/css/adminlte.min.css">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-md-block">
                    <a href="<?= e(url('dashboard')) ?>" class="nav-link">Home</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-bs-toggle="dropdown" href="#">
                        <i class="bi bi-person-circle"></i>
                        <?= e($user['name'] ?? 'Admin') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted"><?= e($user['email'] ?? '') ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="px-3 pb-2">
                            <form action="<?= e(url('logout')) ?>" method="post">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">Logout</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="<?= e(url('dashboard')) ?>" class="brand-link">
                <span class="brand-text fw-light"><b>College</b> CMS</span>
            </a>
        </div>
        <div class="sidebar-wrapper">
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                    <li class="nav-header">ACCOUNT</li>
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="nav-icon bi bi-person"></i>
                            <p>
                                <?= e($user['name'] ?? 'Admin') ?>
                                <br><small class="text-secondary"><?= e((string) $roleLabel) ?></small>
                            </p>
                        </span>
                    </li>
                    <li class="nav-header">MAIN</li>
                    <li class="nav-item">
                        <a href="<?= e(url('dashboard')) ?>" class="nav-link <?= nav_active('dashboard') ?>">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <?php if (can('users.view')): ?>
                        <li class="nav-item">
                            <a href="<?= e(url('users')) ?>" class="nav-link <?= nav_active('users') ?>">
                                <i class="nav-icon bi bi-people"></i>
                                <p>Users</p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-header">MODULES</li>
                    <?php if (can('menus.view')): ?>
                        <li class="nav-item">
                            <a href="<?= e(url('menus')) ?>" class="nav-link <?= nav_active('menus') ?>">
                                <i class="nav-icon bi bi-list"></i>
                                <p>Menus</p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (can('pages.view')): ?>
                        <li class="nav-item">
                            <a href="<?= e(url('pages')) ?>" class="nav-link <?= nav_active('pages') ?>">
                                <i class="nav-icon bi bi-file-earmark-text"></i>
                                <p>Pages</p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (can('departments.view')): ?>
                        <li class="nav-item">
                            <a href="<?= e(url('departments')) ?>" class="nav-link <?= nav_active('departments') ?>">
                                <i class="nav-icon bi bi-building"></i>
                                <p>Departments</p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (can('naac.view')): ?>
                        <li class="nav-item">
                            <a href="<?= e(url('naac')) ?>" class="nav-link <?= nav_active('naac') ?>">
                                <i class="nav-icon bi bi-award"></i>
                                <p>NAAC</p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (can('iqac.view')): ?>
                        <li class="nav-item">
                            <a href="<?= e(url('iqac')) ?>" class="nav-link <?= nav_active('iqac') ?>">
                                <i class="nav-icon bi bi-clipboard2-check"></i>
                                <p>IQAC</p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (can('media.view')): ?>
                        <li class="nav-item">
                            <a href="<?= e(url('media')) ?>" class="nav-link <?= nav_active('media') ?>">
                                <i class="nav-icon bi bi-folder2-open"></i>
                                <p>File Manager</p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (can('settings.view')): ?>
                        <li class="nav-item">
                            <a href="<?= e(url('settings')) ?>" class="nav-link <?= nav_active('settings') ?>">
                                <i class="nav-icon bi bi-gear"></i>
                                <p>Settings</p>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </aside>

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6"><h3 class="mb-0"><?= e($title ?? 'Dashboard') ?></h3></div>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <?php if ($flashError = flash('error')): ?>
                    <div class="alert alert-danger"><?= e((string) $flashError) ?></div>
                <?php endif; ?>
                <?= $content ?>
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <strong>College CMS</strong> — Admin Panel
    </footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-rc4/dist/js/adminlte.min.js"></script>
</body>
</html>

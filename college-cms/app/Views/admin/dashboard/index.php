<?php
/** @var array|null $user */
/** @var int $activeUsers */
?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-bg-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-2 fw-semibold"><?= (int) ($activeUsers ?? 0) ?></div>
                        <div>Active Admin Users</div>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold">Authenticated</div>
                        <div>Session Login Active</div>
                    </div>
                    <i class="bi bi-shield-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-semibold text-dark"><?= e((string) ($user['role_name'] ?? ucfirst(str_replace('_', ' ', (string) ($user['role_slug'] ?? $user['role'] ?? 'admin'))))) ?></div>
                        <div class="text-dark">Your Role</div>
                    </div>
                    <i class="bi bi-person-badge fs-1 opacity-50 text-dark"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h3 class="card-title mb-0 h5">Welcome, <?= e($user['name'] ?? 'Admin') ?></h3>
    </div>
    <div class="card-body">
        <p class="mb-2">Signed in as <strong><?= e($user['email'] ?? '') ?></strong>.</p>
        <p class="mb-0 text-muted">Login module ready: PDO auth, password hashing, session login, remember me, and forgot password.</p>
    </div>
</div>

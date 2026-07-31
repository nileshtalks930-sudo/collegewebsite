<?php
/** @var array{host:string,port:string,database:string,username:string,password:string,charset:string} $config */
/** @var array<string,string> $old */
/** @var string|null $success */
/** @var string|null $error */
/** @var string|null $info */
/** @var bool $writable */
/** @var string $configPath */

$host = (string) ($old['host'] ?? $config['host'] ?? '');
$port = (string) ($old['port'] ?? $config['port'] ?? '3306');
$database = (string) ($old['database'] ?? $config['database'] ?? '');
$username = (string) ($old['username'] ?? $config['username'] ?? '');
$hasPassword = ($config['password'] ?? '') !== '';
?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e((string) $success) ?></div>
<?php endif; ?>
<?php if (!empty($info)): ?>
    <div class="alert alert-info"><?= e((string) $info) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-3">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h3 class="h6 mb-0">Settings</h3>
            </div>
            <div class="list-group list-group-flush">
                <a href="<?= e(url('settings')) ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-sliders me-2"></i>Site Settings
                </a>
                <a href="<?= e(url('settings/database')) ?>" class="list-group-item list-group-item-action active">
                    <i class="bi bi-database me-2"></i>Database
                </a>
            </div>
        </div>
        <div class="card shadow-sm mt-3">
            <div class="card-body small text-muted">
                <p class="mb-2"><strong>Target file:</strong><br><code><?= e($configPath) ?></code></p>
                <p class="mb-0">
                    Writable:
                    <?php if ($writable): ?>
                        <span class="text-success">Yes</span>
                    <?php else: ?>
                        <span class="text-danger">No</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="h6 mb-0">Database Configuration</h3>
                <a href="<?= e(url('settings')) ?>" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
            <div class="card-body">
                <div class="alert alert-warning small">
                    Connection is validated before saving. A backup is written to
                    <code>config/database.php.bak</code>. Incorrect values can lock you out of the admin panel.
                </div>

                <form method="post" id="db-config-form">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="host">Host</label>
                            <input type="text" name="host" id="host" class="form-control" required maxlength="255"
                                   value="<?= e($host) ?>" placeholder="127.0.0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="port">Port</label>
                            <input type="number" name="port" id="port" class="form-control" required min="1" max="65535"
                                   value="<?= e($port) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="database">Database</label>
                            <input type="text" name="database" id="database" class="form-control" required maxlength="64"
                                   value="<?= e($database) ?>" pattern="[A-Za-z0-9_]+"
                                   title="Letters, numbers, and underscores only">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="username">Username</label>
                            <input type="text" name="username" id="username" class="form-control" required maxlength="128"
                                   value="<?= e($username) ?>" autocomplete="off">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label" for="password">Password</label>
                            <input type="password" name="password" id="password" class="form-control" maxlength="255"
                                   value="" autocomplete="new-password"
                                   placeholder="<?= $hasPassword ? '•••••••• (leave blank to keep current)' : '' ?>">
                            <div class="form-text">Leave blank to keep the current password.</div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-outline-secondary"
                                formaction="<?= e(url('settings/database/test')) ?>">
                            <i class="bi bi-plug"></i> Test Connection
                        </button>
                        <button type="submit" class="btn btn-primary"
                                formaction="<?= e(url('settings/database')) ?>"
                                onclick="return confirm('Save these database settings to config/database.php?');">
                            <i class="bi bi-save"></i> Save Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

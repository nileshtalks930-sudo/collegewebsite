<?php
/** @var array<string,mixed>|null $user */
/** @var list<array<string,mixed>> $roles */
/** @var string|null $error */
/** @var array<string,mixed> $old */

$isEdit = $user !== null;
$action = $isEdit ? url('users/' . $user['id'] . '/update') : url('users');
$name = (string) ($old['name'] ?? $user['name'] ?? '');
$email = (string) ($old['email'] ?? $user['email'] ?? '');
$roleId = (int) ($old['role_id'] ?? $user['role_id'] ?? 0);
$status = array_key_exists('status', $old)
    ? (int) $old['status']
    : (int) ($user['status'] ?? 1);
?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0 h5"><?= e($title ?? ($isEdit ? 'Edit User' : 'Create User')) ?></h3>
        <a href="<?= e(url('users')) ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
    <div class="card-body">
        <form method="post" action="<?= e($action) ?>" autocomplete="off">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-control" required maxlength="120"
                           value="<?= e($name) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required maxlength="190"
                           value="<?= e($email) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password">
                        Password<?= $isEdit ? ' <span class="text-muted fw-normal">(leave blank to keep)</span>' : '' ?>
                    </label>
                    <input type="password" id="password" name="password" class="form-control"
                           <?= $isEdit ? '' : 'required' ?> minlength="8">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="role_id">Role</label>
                    <select id="role_id" name="role_id" class="form-select" required>
                        <option value="">Select role</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= (int) $role['id'] ?>" <?= $roleId === (int) $role['id'] ? 'selected' : '' ?>>
                                <?= e((string) $role['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="status"
                            <?= $status === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update User' : 'Create User' ?></button>
                <a href="<?= e(url('users')) ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php if ($isEdit): ?>
    <div class="card shadow-sm mt-3">
        <div class="card-header bg-white">
            <h3 class="card-title mb-0 h6">Role permissions</h3>
        </div>
        <div class="card-body">
            <?php
            $perms = \App\Models\Role::permissionSlugs((int) $user['role_id']);
            if ($perms === []):
            ?>
                <p class="text-muted mb-0">No permissions assigned to this role.</p>
            <?php else: ?>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($perms as $slug): ?>
                        <span class="badge text-bg-light border"><?= e($slug) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

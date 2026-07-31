<?php
/** @var list<array<string,mixed>> $users */
/** @var string $search */
/** @var string|null $success */
/** @var string|null $error */
/** @var bool $canCreate */
/** @var bool $canUpdate */
/** @var bool $canDelete */
?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e((string) $success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <form method="get" action="<?= e(url('users')) ?>" class="d-flex gap-2">
                <input type="search" name="q" value="<?= e($search) ?>" class="form-control" placeholder="Search name or email" style="min-width:220px">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </form>
            <?php if ($canCreate): ?>
                <a href="<?= e(url('users/create')) ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add User
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
            <tr>
                <th style="width:60px">ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Last Login</th>
                <th style="width:160px">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($users === []): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
            <?php else: ?>
                <?php foreach ($users as $row): ?>
                    <tr>
                        <td><?= (int) $row['id'] ?></td>
                        <td><?= e((string) $row['name']) ?></td>
                        <td><?= e((string) $row['email']) ?></td>
                        <td><span class="badge text-bg-info"><?= e((string) $row['role_name']) ?></span></td>
                        <td>
                            <?php if ((int) $row['status'] === 1): ?>
                                <span class="badge text-bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted">
                            <?= e($row['last_login_at'] ? (string) $row['last_login_at'] : '—') ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <?php if ($canUpdate): ?>
                                    <a href="<?= e(url('users/' . $row['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <?php endif; ?>
                                <?php if ($canDelete): ?>
                                    <form action="<?= e(url('users/' . $row['id'] . '/delete')) ?>" method="post"
                                          onsubmit="return confirm('Delete this user?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
/** @var list<array<string,mixed>> $departments */
/** @var string $search */
/** @var string|null $success */
/** @var string|null $error */
/** @var bool $canManage */
?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e((string) $success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <form method="get" action="<?= e(url('departments')) ?>" class="d-flex gap-2">
            <input type="search" name="q" value="<?= e($search) ?>" class="form-control"
                   placeholder="Search name, head, slug" style="min-width:240px">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
        </form>
        <?php if ($canManage): ?>
            <a href="<?= e(url('departments/create')) ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add Department
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
            <tr>
                <th>Department</th>
                <th>Head</th>
                <th>URL</th>
                <th>Faculty</th>
                <th>Gallery</th>
                <th>Downloads</th>
                <th>Status</th>
                <th style="width:150px">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($departments === []): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No departments found.</td></tr>
            <?php else: ?>
                <?php foreach ($departments as $row): ?>
                    <tr>
                        <td><strong><?= e((string) $row['name']) ?></strong></td>
                        <td><?= e((string) ($row['head_name'] ?? '—')) ?></td>
                        <td><code><?= e(\App\Models\Department::publicUrl((string) $row['slug'])) ?></code></td>
                        <td><?= (int) ($row['faculty_count'] ?? 0) ?></td>
                        <td><?= (int) ($row['gallery_count'] ?? 0) ?></td>
                        <td><?= (int) ($row['downloads_count'] ?? 0) ?></td>
                        <td>
                            <?php if ((int) $row['status'] === 1): ?>
                                <span class="badge text-bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($canManage): ?>
                                <div class="d-flex gap-1">
                                    <a href="<?= e(url('departments/' . $row['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="post" action="<?= e(url('departments/' . $row['id'] . '/delete')) ?>"
                                          onsubmit="return confirm('Delete this department and all related content?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

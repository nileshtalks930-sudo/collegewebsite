<?php
/** @var list<array<string,mixed>> $pages */
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
        <form method="get" action="<?= e(url('pages')) ?>" class="d-flex gap-2">
            <input type="search" name="q" value="<?= e($search) ?>" class="form-control" placeholder="Search title, slug, keywords" style="min-width:240px">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
        </form>
        <?php if ($canManage): ?>
            <a href="<?= e(url('pages/create')) ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add Page
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
            <tr>
                <th style="width:56px">ID</th>
                <th>Title</th>
                <th>Slug</th>
                <th>Menu</th>
                <th>Status</th>
                <th>Created</th>
                <th>Modified</th>
                <th style="width:150px">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($pages === []): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No pages found.</td></tr>
            <?php else: ?>
                <?php foreach ($pages as $row): ?>
                    <tr>
                        <td><?= (int) $row['id'] ?></td>
                        <td>
                            <strong><?= e((string) $row['title']) ?></strong>
                            <?php if (!empty($row['featured_image'])): ?>
                                <i class="bi bi-image text-muted" title="Has featured image"></i>
                            <?php endif; ?>
                            <?php if (!empty($row['pdf_attachment'])): ?>
                                <i class="bi bi-file-earmark-pdf text-danger" title="Has PDF"></i>
                            <?php endif; ?>
                        </td>
                        <td><code><?= e((string) $row['slug']) ?></code></td>
                        <td class="small">
                            <?= e((string) ($row['menu_name'] ?? '—')) ?>
                            <?php if (!empty($row['menu_position'])): ?>
                                <span class="text-muted">(<?= e((string) $row['menu_position']) ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((int) $row['status'] === 1): ?>
                                <span class="badge text-bg-success">Published</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= e((string) ($row['created_at'] ?? '—')) ?></td>
                        <td class="small text-muted"><?= e((string) ($row['updated_at'] ?? '—')) ?></td>
                        <td>
                            <?php if ($canManage): ?>
                                <div class="d-flex gap-1">
                                    <a href="<?= e(url('pages/' . $row['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="post" action="<?= e(url('pages/' . $row['id'] . '/delete')) ?>"
                                          onsubmit="return confirm('Delete this page?');">
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

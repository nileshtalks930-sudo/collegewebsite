<?php
/** @var string $section */
/** @var array<string,mixed> $meta */
/** @var list<array<string,mixed>> $items */
/** @var string|null $success */
/** @var string|null $error */
/** @var bool $canManage */

$fileField = $meta['file_field'] ?? null;
?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e((string) $success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="h5 mb-0"><?= e((string) $meta['label']) ?></h3>
            <a href="<?= e(url('iqac')) ?>" class="small">← IQAC Home</a>
        </div>
        <?php if ($canManage): ?>
            <a href="<?= e(url('iqac/' . $section . '/create')) ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
            <tr>
                <th>Title / Name</th>
                <th>Details</th>
                <th>File</th>
                <th>Status</th>
                <?php if ($canManage): ?><th style="width:150px">Actions</th><?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php if ($items === []): ?>
                <tr><td colspan="<?= $canManage ? 5 : 4 ?>" class="text-center text-muted py-4">No items yet.</td></tr>
            <?php else: ?>
                <?php foreach ($items as $row): ?>
                    <?php
                    $primary = (string) ($row['title'] ?? $row['name'] ?? 'Item #' . $row['id']);
                    $details = [];
                    foreach (['designation', 'role', 'academic_year', 'meeting_date', 'notice_date', 'circular_date', 'reference_no', 'category', 'caption'] as $k) {
                        if (!empty($row[$k])) {
                            $details[] = e((string) $row[$k]);
                        }
                    }
                    $file = $fileField ? (string) ($row[$fileField] ?? '') : '';
                    ?>
                    <tr>
                        <td><strong><?= e($primary) ?></strong></td>
                        <td class="small text-muted"><?= $details !== [] ? implode(' · ', $details) : '—' ?></td>
                        <td>
                            <?php if ($file !== ''): ?>
                                <?php if (str_contains($file, '/gallery/') || str_contains($file, '/members/') || preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)): ?>
                                    <img src="<?= e(upload_url($file)) ?>" alt="" style="height:40px;width:40px;object-fit:cover" class="rounded border">
                                <?php else: ?>
                                    <a href="<?= e(upload_url($file)) ?>" target="_blank" rel="noopener">Download</a>
                                <?php endif; ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((int) ($row['status'] ?? 1) === 1): ?>
                                <span class="badge text-bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($canManage): ?>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= e(url('iqac/' . $section . '/' . $row['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="post" action="<?= e(url('iqac/' . $section . '/' . $row['id'] . '/delete')) ?>"
                                          onsubmit="return confirm('Delete this item?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

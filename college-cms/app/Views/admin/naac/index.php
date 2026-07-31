<?php
/** @var list<array<string,mixed>> $criteria */
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

<p class="text-muted mb-3">Manage NAAC Criteria 1–7. Each criterion supports heading, description, files, links, tables, images, and dynamic pages.</p>

<div class="row g-3">
    <?php foreach ($criteria as $row): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge text-bg-primary">Criteria <?= (int) $row['number'] ?></span>
                        <?php if ((int) $row['status'] === 1): ?>
                            <span class="badge text-bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                    <h3 class="h5"><?= e((string) $row['heading']) ?></h3>
                    <p class="small text-muted mb-2">
                        URL: <code><?= e(\App\Models\NaacCriterion::publicUrl($row)) ?></code>
                    </p>
                    <div class="small text-muted mb-3">
                        Files <?= (int) $row['files_count'] ?> ·
                        Links <?= (int) $row['links_count'] ?> ·
                        Tables <?= (int) $row['tables_count'] ?> ·
                        Images <?= (int) $row['images_count'] ?> ·
                        Pages <?= (int) $row['pages_count'] ?>
                    </div>
                    <?php if ($canManage): ?>
                        <a href="<?= e(url('naac/' . $row['id'] . '/edit')) ?>" class="btn btn-sm btn-primary">Manage</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
/** @var array<string,int> $counts */
/** @var array<string,array<string,mixed>> $sections */
/** @var array<string,mixed> $committee */
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
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="h5 mb-1"><?= e((string) ($committee['title'] ?? 'IQAC')) ?></h3>
            <p class="text-muted mb-0 small">Manage committee profile and IQAC content sections.</p>
        </div>
        <?php if ($canManage): ?>
            <a href="<?= e(url('iqac/committee')) ?>" class="btn btn-primary">Edit Committee</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6 col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-people-fill fs-4 text-primary"></i>
                    <h3 class="h6 mb-0">Committee</h3>
                </div>
                <p class="small text-muted">Overview, vision, and mission.</p>
                <?php if ($canManage): ?>
                    <a href="<?= e(url('iqac/committee')) ?>" class="btn btn-sm btn-outline-primary">Manage</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php foreach ($sections as $key => $meta): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi <?= e((string) $meta['icon']) ?> fs-4 text-primary"></i>
                            <h3 class="h6 mb-0"><?= e((string) $meta['label']) ?></h3>
                        </div>
                        <span class="badge text-bg-light border"><?= (int) ($counts[$key] ?? 0) ?></span>
                    </div>
                    <a href="<?= e(url('iqac/' . $key)) ?>" class="btn btn-sm btn-outline-primary">Open</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

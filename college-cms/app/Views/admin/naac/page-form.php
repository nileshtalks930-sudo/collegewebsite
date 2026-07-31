<?php
/** @var array<string,mixed> $criterion */
/** @var array<string,mixed>|null $page */
/** @var string|null $error */
/** @var array<string,mixed> $old */

$isEdit = $page !== null;
$action = $isEdit
    ? url('naac/' . $criterion['id'] . '/pages/' . $page['id'] . '/update')
    : url('naac/' . $criterion['id'] . '/pages');

$titleVal = (string) ($old['title'] ?? $page['title'] ?? '');
$slugVal = (string) ($old['slug'] ?? $page['slug'] ?? '');
$contentVal = (string) ($old['content'] ?? $page['content'] ?? '');
$status = array_key_exists('status', $old) ? (int) $old['status'] : (int) ($page['status'] ?? 1);
?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h5 mb-0"><?= e($title ?? ($isEdit ? 'Edit Page' : 'Add Page')) ?></h3>
            <div class="small text-muted">Criteria <?= (int) $criterion['number'] ?> · <?= e((string) $criterion['heading']) ?></div>
        </div>
        <a href="<?= e(url('naac/' . $criterion['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
    <div class="card-body">
        <form method="post" action="<?= e($action) ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="title">Title</label>
                <input type="text" name="title" id="title" class="form-control" required maxlength="200"
                       value="<?= e($titleVal) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="slug">Slug</label>
                <div class="input-group">
                    <span class="input-group-text">/naac/<?= e((string) $criterion['slug']) ?>/</span>
                    <input type="text" name="slug" id="slug" class="form-control" value="<?= e($slugVal) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="content">Content</label>
                <textarea name="content" id="content" class="form-control cms-editor" rows="16"><?= e($contentVal) ?></textarea>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="status" value="1" id="status"
                    <?= $status === 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="status">Published</label>
            </div>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update Page' : 'Create Page' ?></button>
        </form>
    </div>
</div>

<script>
(() => {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    let touched = <?= $isEdit ? 'true' : 'false' ?>;
    slugInput.addEventListener('input', () => { touched = slugInput.value.trim() !== ''; });
    titleInput.addEventListener('input', () => {
        if (touched) return;
        slugInput.value = titleInput.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    });
})();
</script>

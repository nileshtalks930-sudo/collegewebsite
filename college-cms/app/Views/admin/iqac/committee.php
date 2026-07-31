<?php
/** @var array<string,mixed> $committee */
/** @var string|null $success */
/** @var string|null $error */
?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e((string) $success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(url('iqac/committee')) ?>">
    <?= csrf_field() ?>
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h3 class="h5 mb-0">IQAC Committee</h3>
            <a href="<?= e(url('iqac')) ?>" class="btn btn-sm btn-outline-secondary">Back</a>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label" for="title">Title</label>
                <input type="text" name="title" id="title" class="form-control" required maxlength="200"
                       value="<?= e((string) ($committee['title'] ?? '')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="description">Description</label>
                <textarea name="description" id="description" class="form-control" rows="10"><?= e((string) ($committee['description'] ?? '')) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="vision">Vision</label>
                <textarea name="vision" id="vision" class="form-control" rows="3"><?= e((string) ($committee['vision'] ?? '')) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="mission">Mission</label>
                <textarea name="mission" id="mission" class="form-control" rows="3"><?= e((string) ($committee['mission'] ?? '')) ?></textarea>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="status" value="1" id="status"
                    <?= ((int) ($committee['status'] ?? 1) === 1) ? 'checked' : '' ?>>
                <label class="form-check-label" for="status">Active</label>
            </div>
            <button type="submit" class="btn btn-primary">Save Committee</button>
        </div>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.4/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#description',
    height: 300,
    menubar: false,
    plugins: 'lists link table code',
    toolbar: 'undo redo | styles | bold italic | bullist numlist | link | code',
    branding: false,
    promotion: false,
    convert_urls: false,
});
</script>

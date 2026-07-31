<?php
/** @var array<string,mixed>|null $page */
/** @var list<array<string,mixed>> $menus */
/** @var string|null $error */
/** @var array<string,mixed> $old */

$isEdit = $page !== null;
$action = $isEdit ? url('pages/' . $page['id'] . '/update') : url('pages');

$titleVal = (string) ($old['title'] ?? $page['title'] ?? '');
$slugVal = (string) ($old['slug'] ?? $page['slug'] ?? '');
$contentVal = (string) ($old['content'] ?? $page['content'] ?? '');
$menuId = array_key_exists('menu_id', $old)
    ? ($old['menu_id'] !== null && $old['menu_id'] !== '' ? (int) $old['menu_id'] : 0)
    : (int) ($page['menu_id'] ?? 0);
$metaTitle = (string) ($old['meta_title'] ?? $page['meta_title'] ?? '');
$metaDescription = (string) ($old['meta_description'] ?? $page['meta_description'] ?? '');
$metaKeywords = (string) ($old['meta_keywords'] ?? $page['meta_keywords'] ?? '');
$status = array_key_exists('status', $old) ? (int) $old['status'] : (int) ($page['status'] ?? 1);
$featured = $isEdit ? (string) ($page['featured_image'] ?? '') : '';
$pdf = $isEdit ? (string) ($page['pdf_attachment'] ?? '') : '';
?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" id="page-form">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="h5 mb-0"><?= e($title ?? ($isEdit ? 'Edit Page' : 'Create Page')) ?></h3>
                    <a href="<?= e(url('pages')) ?>" class="btn btn-sm btn-outline-secondary">Back</a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="title">Title</label>
                        <input type="text" name="title" id="title" class="form-control" required maxlength="200"
                               value="<?= e($titleVal) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="slug">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control" maxlength="200"
                               value="<?= e($slugVal) ?>" placeholder="auto-generated-from-title">
                        <div class="form-text">Leave blank to auto-generate from title.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="content">HTML Content</label>
                        <textarea name="content" id="content" class="form-control cms-editor" rows="18"><?= e($contentVal) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h3 class="h6 mb-0">SEO Settings</h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="meta_title">Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" class="form-control" maxlength="200"
                               value="<?= e($metaTitle) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="meta_description">Meta Description</label>
                        <textarea name="meta_description" id="meta_description" class="form-control" rows="3" maxlength="500"><?= e($metaDescription) ?></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="meta_keywords">Keywords</label>
                        <input type="text" name="meta_keywords" id="meta_keywords" class="form-control" maxlength="500"
                               value="<?= e($metaKeywords) ?>" placeholder="college, admissions, courses">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h3 class="h6 mb-0">Publish</h3></div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="status"
                            <?= $status === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status">Published</label>
                    </div>
                    <?php if ($isEdit): ?>
                        <p class="small text-muted mb-1">Created: <?= e((string) ($page['created_at'] ?? '—')) ?></p>
                        <p class="small text-muted mb-3">Modified: <?= e((string) ($page['updated_at'] ?? '—')) ?></p>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary w-100">
                        <?= $isEdit ? 'Update Page' : 'Create Page' ?>
                    </button>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h3 class="h6 mb-0">Menu Selection</h3></div>
                <div class="card-body">
                    <label class="form-label" for="menu_id">Attach to menu item</label>
                    <select name="menu_id" id="menu_id" class="form-select">
                        <option value="">— None —</option>
                        <?php foreach ($menus as $menu): ?>
                            <option value="<?= (int) $menu['id'] ?>" <?= $menuId === (int) $menu['id'] ? 'selected' : '' ?>>
                                <?= e((string) $menu['name']) ?>
                                (<?= e((string) ($menu['position'] ?? '')) ?><?= !empty($menu['parent_name']) ? ' · under ' . $menu['parent_name'] : '' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Links the selected menu item to this page.</div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h3 class="h6 mb-0">Featured Image</h3></div>
                <div class="card-body">
                    <?php if ($featured !== ''): ?>
                        <div class="mb-2">
                            <img src="<?= e(upload_url($featured)) ?>" alt="" class="img-fluid rounded border" style="max-height:160px">
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="remove_featured_image" value="1" id="remove_featured_image">
                            <label class="form-check-label" for="remove_featured_image">Remove current image</label>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="featured_image" id="featured_image" class="form-control" accept="image/*">
                    <div class="form-text">JPG, PNG, GIF, WebP · max 5MB</div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h3 class="h6 mb-0">PDF Attachment</h3></div>
                <div class="card-body">
                    <?php if ($pdf !== ''): ?>
                        <p class="mb-2">
                            <a href="<?= e(upload_url($pdf)) ?>" target="_blank" rel="noopener">
                                <i class="bi bi-file-earmark-pdf"></i> Current PDF
                            </a>
                        </p>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="remove_pdf_attachment" value="1" id="remove_pdf_attachment">
                            <label class="form-check-label" for="remove_pdf_attachment">Remove current PDF</label>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="pdf_attachment" id="pdf_attachment" class="form-control" accept="application/pdf,.pdf">
                    <div class="form-text">PDF only · max 10MB</div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
(() => {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    let slugTouched = <?= $isEdit ? 'true' : 'false' ?>;

    slugInput.addEventListener('input', () => { slugTouched = slugInput.value.trim() !== ''; });

    titleInput.addEventListener('input', () => {
        if (slugTouched) return;
        slugInput.value = titleInput.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    });
})();
</script>

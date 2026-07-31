<?php
/** @var string $section */
/** @var array<string,mixed> $meta */
/** @var array<string,mixed>|null $item */
/** @var string|null $error */
/** @var array<string,mixed> $old */

$isEdit = $item !== null;
$action = $isEdit
    ? url('iqac/' . $section . '/' . $item['id'] . '/update')
    : url('iqac/' . $section);

$fileField = $meta['file_field'] ?? null;
$currentFile = $isEdit && $fileField ? (string) ($item[$fileField] ?? '') : '';
$richtext = $meta['richtext'] ?? [];

$fieldLabels = [
    'name' => 'Name',
    'title' => 'Title',
    'designation' => 'Designation',
    'role' => 'Role / Position',
    'email' => 'Email',
    'phone' => 'Phone',
    'meeting_date' => 'Meeting Date',
    'notice_date' => 'Notice Date',
    'circular_date' => 'Circular Date',
    'academic_year' => 'Academic Year',
    'reference_no' => 'Reference No.',
    'category' => 'Category',
    'caption' => 'Caption',
    'description' => 'Description',
    'content' => 'Content',
    'sort_order' => 'Sort Order',
];

$status = array_key_exists('status', $old)
    ? (int) $old['status']
    : (int) ($item['status'] ?? 1);
?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h3 class="h5 mb-0"><?= e($title ?? ($isEdit ? 'Edit' : 'Add')) ?></h3>
            <a href="<?= e(url('iqac/' . $section)) ?>" class="btn btn-sm btn-outline-secondary">Back</a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($meta['fields'] as $field): ?>
                    <?php
                    $value = (string) ($old[$field] ?? $item[$field] ?? '');
                    $label = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));
                    $required = in_array($field, $meta['required'], true);
                    $isDate = str_ends_with($field, '_date');
                    $isTextarea = in_array($field, ['description', 'content'], true);
                    ?>
                    <div class="col-md-<?= $isTextarea ? '12' : '6' ?>">
                        <label class="form-label" for="<?= e($field) ?>"><?= e($label) ?></label>
                        <?php if ($isTextarea): ?>
                            <textarea name="<?= e($field) ?>" id="<?= e($field) ?>" class="form-control" rows="<?= in_array($field, $richtext, true) ? 12 : 4 ?>"><?= e($value) ?></textarea>
                        <?php elseif ($isDate): ?>
                            <input type="date" name="<?= e($field) ?>" id="<?= e($field) ?>" class="form-control" value="<?= e($value) ?>">
                        <?php elseif ($field === 'sort_order'): ?>
                            <input type="number" name="<?= e($field) ?>" id="<?= e($field) ?>" class="form-control" value="<?= e($value !== '' ? $value : '0') ?>">
                        <?php elseif ($field === 'email'): ?>
                            <input type="email" name="<?= e($field) ?>" id="<?= e($field) ?>" class="form-control" value="<?= e($value) ?>">
                        <?php else: ?>
                            <input type="text" name="<?= e($field) ?>" id="<?= e($field) ?>" class="form-control"
                                   <?= $required ? 'required' : '' ?> value="<?= e($value) ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <?php if ($fileField): ?>
                    <div class="col-12">
                        <label class="form-label" for="upload_file">
                            <?= str_contains($fileField, 'image') || $fileField === 'photo' ? 'Image' : 'File' ?>
                            <?= !empty($meta['file_required']) && !$isEdit ? '(required)' : '' ?>
                        </label>
                        <?php if ($currentFile !== ''): ?>
                            <div class="mb-2">
                                <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $currentFile)): ?>
                                    <img src="<?= e(upload_url($currentFile)) ?>" alt="" class="rounded border mb-2" style="max-height:120px">
                                <?php else: ?>
                                    <a href="<?= e(upload_url($currentFile)) ?>" target="_blank" rel="noopener">Current file</a>
                                <?php endif; ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_file" value="1" id="remove_file">
                                    <label class="form-check-label" for="remove_file">Remove current file</label>
                                </div>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="upload_file" id="upload_file" class="form-control"
                            <?= !empty($meta['file_required']) && !$isEdit ? 'required' : '' ?>>
                        <div class="form-text">Allowed: <?= e(implode(', ', $meta['file_types'] ?? [])) ?></div>
                    </div>
                <?php endif; ?>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="status"
                            <?= $status === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?></button>
            </div>
        </div>
    </div>
</form>

<?php if ($richtext !== []): ?>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.4/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#<?= e(implode(', #', $richtext)) ?>',
    height: 320,
    menubar: false,
    plugins: 'lists link table code',
    toolbar: 'undo redo | styles | bold italic | bullist numlist | link | code',
    branding: false,
    promotion: false,
    convert_urls: false,
});
</script>
<?php endif; ?>

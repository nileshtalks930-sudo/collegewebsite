<?php
/** @var array<string,mixed>|null $department */
/** @var list<array<string,mixed>> $faculty */
/** @var list<array<string,mixed>> $achievements */
/** @var list<array<string,mixed>> $downloads */
/** @var list<array<string,mixed>> $gallery */
/** @var string|null $error */
/** @var string|null $success */
/** @var array<string,mixed> $old */

$isEdit = $department !== null;
$action = $isEdit ? url('departments/' . $department['id'] . '/update') : url('departments');

$name = (string) ($old['name'] ?? $department['name'] ?? '');
$slug = (string) ($old['slug'] ?? $department['slug'] ?? '');
$headName = (string) ($old['head_name'] ?? $department['head_name'] ?? '');
$headDesignation = (string) ($old['head_designation'] ?? $department['head_designation'] ?? '');
$description = (string) ($old['description'] ?? $department['description'] ?? '');
$contactEmail = (string) ($old['contact_email'] ?? $department['contact_email'] ?? '');
$contactPhone = (string) ($old['contact_phone'] ?? $department['contact_phone'] ?? '');
$contactAddress = (string) ($old['contact_address'] ?? $department['contact_address'] ?? '');
$status = array_key_exists('status', $old) ? (int) $old['status'] : (int) ($department['status'] ?? 1);
$headPhoto = $isEdit ? (string) ($department['head_photo'] ?? '') : '';

$facultyRows = is_array($old['faculty'] ?? null) && $old['faculty'] !== [] ? $old['faculty'] : $faculty;
$achievementRows = is_array($old['achievements'] ?? null) && $old['achievements'] !== [] ? $old['achievements'] : $achievements;
?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e((string) $success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" id="department-form">
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="h5 mb-0"><?= e($title ?? ($isEdit ? 'Edit Department' : 'Add Department')) ?></h3>
                    <a href="<?= e(url('departments')) ?>" class="btn btn-sm btn-outline-secondary">Back</a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Department Name</label>
                            <input type="text" name="name" id="name" class="form-control" required maxlength="200"
                                   value="<?= e($name) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="slug">Dynamic URL (slug)</label>
                            <div class="input-group">
                                <span class="input-group-text">/department/</span>
                                <input type="text" name="slug" id="slug" class="form-control" maxlength="200"
                                       value="<?= e($slug) ?>" placeholder="computer-science">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="description">Description</label>
                            <textarea name="description" id="description" class="form-control cms-editor" rows="12"><?= e($description) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="h6 mb-0">Faculty List</h3>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-faculty">Add Faculty</button>
                </div>
                <div class="card-body" id="faculty-list">
                    <?php if ($facultyRows === []): ?>
                        <p class="text-muted small mb-2" id="faculty-empty">No faculty added yet.</p>
                    <?php endif; ?>
                    <?php foreach ($facultyRows as $i => $row): ?>
                        <div class="border rounded p-3 mb-2 faculty-row">
                            <input type="hidden" name="faculty[<?= (int) $i ?>][id]" value="<?= e((string) ($row['id'] ?? '')) ?>">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <input type="text" name="faculty[<?= (int) $i ?>][name]" class="form-control" placeholder="Name"
                                           value="<?= e((string) ($row['name'] ?? '')) ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="faculty[<?= (int) $i ?>][designation]" class="form-control" placeholder="Designation"
                                           value="<?= e((string) ($row['designation'] ?? '')) ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="email" name="faculty[<?= (int) $i ?>][email]" class="form-control" placeholder="Email"
                                           value="<?= e((string) ($row['email'] ?? '')) ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="faculty[<?= (int) $i ?>][phone]" class="form-control" placeholder="Phone"
                                           value="<?= e((string) ($row['phone'] ?? '')) ?>">
                                </div>
                                <div class="col-md-7">
                                    <input type="text" name="faculty[<?= (int) $i ?>][bio]" class="form-control" placeholder="Short bio"
                                           value="<?= e((string) ($row['bio'] ?? '')) ?>">
                                </div>
                                <div class="col-md-1 d-grid">
                                    <button type="button" class="btn btn-outline-danger remove-row">&times;</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="h6 mb-0">Achievements</h3>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-achievement">Add Achievement</button>
                </div>
                <div class="card-body" id="achievement-list">
                    <?php if ($achievementRows === []): ?>
                        <p class="text-muted small mb-2" id="achievement-empty">No achievements added yet.</p>
                    <?php endif; ?>
                    <?php foreach ($achievementRows as $i => $row): ?>
                        <div class="border rounded p-3 mb-2 achievement-row">
                            <input type="hidden" name="achievements[<?= (int) $i ?>][id]" value="<?= e((string) ($row['id'] ?? '')) ?>">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input type="text" name="achievements[<?= (int) $i ?>][title]" class="form-control" placeholder="Title"
                                           value="<?= e((string) ($row['title'] ?? '')) ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="date" name="achievements[<?= (int) $i ?>][achieved_on]" class="form-control"
                                           value="<?= e((string) ($row['achieved_on'] ?? '')) ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="achievements[<?= (int) $i ?>][description]" class="form-control" placeholder="Description"
                                           value="<?= e((string) ($row['description'] ?? '')) ?>">
                                </div>
                                <div class="col-md-1 d-grid">
                                    <button type="button" class="btn btn-outline-danger remove-row">&times;</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($isEdit): ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white"><h3 class="h6 mb-0">Downloads</h3></div>
                    <div class="card-body">
                        <?php if ($downloads === []): ?>
                            <p class="text-muted small">No downloads yet.</p>
                        <?php else: ?>
                            <ul class="list-group mb-3">
                                <?php foreach ($downloads as $file): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <a href="<?= e(upload_url((string) $file['file_path'])) ?>" target="_blank" rel="noopener">
                                            <?= e((string) $file['title']) ?>
                                        </a>
                                        <form method="post" action="<?= e(url('departments/' . $department['id'] . '/downloads/' . $file['id'] . '/delete')) ?>"
                                              onsubmit="return confirm('Remove this download?');">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <div class="row g-2" id="download-new">
                            <div class="col-md-5">
                                <input type="text" name="download_title[]" class="form-control" placeholder="Document title">
                            </div>
                            <div class="col-md-7">
                                <input type="file" name="download_file[]" class="form-control"
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-download">Add another file</button>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white"><h3 class="h6 mb-0">Photo Gallery</h3></div>
                    <div class="card-body">
                        <?php if ($gallery === []): ?>
                            <p class="text-muted small">No gallery images yet.</p>
                        <?php else: ?>
                            <div class="row g-2 mb-3">
                                <?php foreach ($gallery as $img): ?>
                                    <div class="col-md-3">
                                        <div class="border rounded p-2 h-100">
                                            <img src="<?= e(upload_url((string) $img['image_path'])) ?>" alt="" class="img-fluid rounded mb-2">
                                            <div class="small mb-2"><?= e((string) ($img['title'] ?? '')) ?></div>
                                            <form method="post" action="<?= e(url('departments/' . $department['id'] . '/gallery/' . $img['id'] . '/delete')) ?>"
                                                  onsubmit="return confirm('Remove this image?');">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-sm btn-outline-danger w-100" type="submit">Remove</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="row g-2" id="gallery-new">
                            <div class="col-md-5">
                                <input type="text" name="gallery_title[]" class="form-control" placeholder="Caption (optional)">
                            </div>
                            <div class="col-md-7">
                                <input type="file" name="gallery_image[]" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-gallery">Add another image</button>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Save the department first to upload gallery images and downloads.</div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h3 class="h6 mb-0">Publish</h3></div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="status"
                            <?= $status === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                    <?php if ($isEdit): ?>
                        <p class="small text-muted mb-1">URL: <code><?= e(\App\Models\Department::publicUrl((string) $department['slug'])) ?></code></p>
                        <p class="small text-muted mb-1">Created: <?= e((string) ($department['created_at'] ?? '—')) ?></p>
                        <p class="small text-muted mb-3">Modified: <?= e((string) ($department['updated_at'] ?? '—')) ?></p>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary w-100">
                        <?= $isEdit ? 'Update Department' : 'Create Department' ?>
                    </button>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h3 class="h6 mb-0">Department Head</h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="head_name">Name</label>
                        <input type="text" name="head_name" id="head_name" class="form-control" maxlength="150"
                               value="<?= e($headName) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="head_designation">Designation</label>
                        <input type="text" name="head_designation" id="head_designation" class="form-control" maxlength="150"
                               value="<?= e($headDesignation) ?>">
                    </div>
                    <?php if ($headPhoto !== ''): ?>
                        <img src="<?= e(upload_url($headPhoto)) ?>" alt="" class="img-fluid rounded border mb-2" style="max-height:140px">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="remove_head_photo" value="1" id="remove_head_photo">
                            <label class="form-check-label" for="remove_head_photo">Remove photo</label>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="head_photo" id="head_photo" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h3 class="h6 mb-0">Contact</h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="contact_email">Email</label>
                        <input type="email" name="contact_email" id="contact_email" class="form-control"
                               value="<?= e($contactEmail) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="contact_phone">Phone</label>
                        <input type="text" name="contact_phone" id="contact_phone" class="form-control"
                               value="<?= e($contactPhone) ?>">
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="contact_address">Address</label>
                        <textarea name="contact_address" id="contact_address" class="form-control" rows="3"><?= e($contactAddress) ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="faculty-template">
    <div class="border rounded p-3 mb-2 faculty-row">
        <input type="hidden" name="faculty[__i__][id]" value="">
        <div class="row g-2">
            <div class="col-md-4"><input type="text" name="faculty[__i__][name]" class="form-control" placeholder="Name"></div>
            <div class="col-md-4"><input type="text" name="faculty[__i__][designation]" class="form-control" placeholder="Designation"></div>
            <div class="col-md-4"><input type="email" name="faculty[__i__][email]" class="form-control" placeholder="Email"></div>
            <div class="col-md-4"><input type="text" name="faculty[__i__][phone]" class="form-control" placeholder="Phone"></div>
            <div class="col-md-7"><input type="text" name="faculty[__i__][bio]" class="form-control" placeholder="Short bio"></div>
            <div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger remove-row">&times;</button></div>
        </div>
    </div>
</template>

<template id="achievement-template">
    <div class="border rounded p-3 mb-2 achievement-row">
        <input type="hidden" name="achievements[__i__][id]" value="">
        <div class="row g-2">
            <div class="col-md-5"><input type="text" name="achievements[__i__][title]" class="form-control" placeholder="Title"></div>
            <div class="col-md-3"><input type="date" name="achievements[__i__][achieved_on]" class="form-control"></div>
            <div class="col-md-3"><input type="text" name="achievements[__i__][description]" class="form-control" placeholder="Description"></div>
            <div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger remove-row">&times;</button></div>
        </div>
    </div>
</template>

<script>
(() => {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    let slugTouched = <?= $isEdit ? 'true' : 'false' ?>;
    slugInput.addEventListener('input', () => { slugTouched = slugInput.value.trim() !== ''; });
    nameInput.addEventListener('input', () => {
        if (slugTouched) return;
        slugInput.value = nameInput.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    });

    function bindRemove(root) {
        root.querySelectorAll('.remove-row').forEach((btn) => {
            btn.onclick = () => btn.closest('.faculty-row, .achievement-row')?.remove();
        });
    }
    bindRemove(document);

    let facultyIndex = <?= count($facultyRows) ?>;
    document.getElementById('add-faculty')?.addEventListener('click', () => {
        document.getElementById('faculty-empty')?.remove();
        const html = document.getElementById('faculty-template').innerHTML.replaceAll('__i__', String(facultyIndex++));
        const wrap = document.createElement('div');
        wrap.innerHTML = html;
        const node = wrap.firstElementChild;
        document.getElementById('faculty-list').appendChild(node);
        bindRemove(node);
    });

    let achievementIndex = <?= count($achievementRows) ?>;
    document.getElementById('add-achievement')?.addEventListener('click', () => {
        document.getElementById('achievement-empty')?.remove();
        const html = document.getElementById('achievement-template').innerHTML.replaceAll('__i__', String(achievementIndex++));
        const wrap = document.createElement('div');
        wrap.innerHTML = html;
        const node = wrap.firstElementChild;
        document.getElementById('achievement-list').appendChild(node);
        bindRemove(node);
    });

    document.getElementById('add-download')?.addEventListener('click', () => {
        const box = document.getElementById('download-new');
        const row = document.createElement('div');
        row.className = 'row g-2 mt-2';
        row.innerHTML = `
            <div class="col-md-5"><input type="text" name="download_title[]" class="form-control" placeholder="Document title"></div>
            <div class="col-md-7"><input type="file" name="download_file[]" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip"></div>`;
        box.parentElement.insertBefore(row, document.getElementById('add-download'));
    });

    document.getElementById('add-gallery')?.addEventListener('click', () => {
        const box = document.getElementById('gallery-new');
        const row = document.createElement('div');
        row.className = 'row g-2 mt-2';
        row.innerHTML = `
            <div class="col-md-5"><input type="text" name="gallery_title[]" class="form-control" placeholder="Caption (optional)"></div>
            <div class="col-md-7"><input type="file" name="gallery_image[]" class="form-control" accept="image/*"></div>`;
        box.parentElement.insertBefore(row, document.getElementById('add-gallery'));
    });
})();
</script>

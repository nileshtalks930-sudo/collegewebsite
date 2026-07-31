<?php
/** @var array<string,mixed> $criterion */
/** @var string|null $success */
/** @var string|null $error */
/** @var array<string,mixed> $old */

$heading = (string) ($old['heading'] ?? $criterion['heading'] ?? '');
$slug = (string) ($old['slug'] ?? $criterion['slug'] ?? '');
$description = (string) ($old['description'] ?? $criterion['description'] ?? '');
$status = array_key_exists('status', $old) ? (int) $old['status'] : (int) ($criterion['status'] ?? 1);
$links = is_array($old['links'] ?? null) ? $old['links'] : ($criterion['links'] ?? []);
$tables = is_array($old['tables'] ?? null) ? $old['tables'] : ($criterion['tables'] ?? []);
$files = $criterion['files'] ?? [];
$images = $criterion['images'] ?? [];
$pages = $criterion['pages'] ?? [];
?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e((string) $success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div>
        <h3 class="h4 mb-0">Criteria <?= (int) $criterion['number'] ?></h3>
        <div class="small text-muted">Public URL: <code><?= e(\App\Models\NaacCriterion::publicUrl($criterion)) ?></code></div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= e(url('naac')) ?>" class="btn btn-outline-secondary btn-sm">All Criteria</a>
        <button type="submit" form="naac-main" class="btn btn-primary btn-sm">Save Changes</button>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <form method="post" action="<?= e(url('naac/' . $criterion['id'] . '/update')) ?>" enctype="multipart/form-data" id="naac-main">
            <?= csrf_field() ?>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h3 class="h6 mb-0">Heading &amp; Description</h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="heading">Heading</label>
                        <input type="text" name="heading" id="heading" class="form-control" required maxlength="255"
                               value="<?= e($heading) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="slug">Dynamic URL slug</label>
                        <div class="input-group">
                            <span class="input-group-text">/naac/</span>
                            <input type="text" name="slug" id="slug" class="form-control" value="<?= e($slug) ?>">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="description">Description</label>
                        <textarea name="description" id="description" rows="12" class="form-control cms-editor"><?= e($description) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="h6 mb-0">Links</h3>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-link">Add Link</button>
                </div>
                <div class="card-body" id="links-list">
                    <?php if ($links === []): ?>
                        <p class="text-muted small" id="links-empty">No links yet.</p>
                    <?php endif; ?>
                    <?php foreach ($links as $i => $link): ?>
                        <div class="border rounded p-3 mb-2 link-row">
                            <input type="hidden" name="links[<?= (int) $i ?>][id]" value="<?= e((string) ($link['id'] ?? '')) ?>">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <input type="text" name="links[<?= (int) $i ?>][title]" class="form-control" placeholder="Title"
                                           value="<?= e((string) ($link['title'] ?? '')) ?>">
                                </div>
                                <div class="col-md-5">
                                    <input type="url" name="links[<?= (int) $i ?>][url]" class="form-control" placeholder="https://"
                                           value="<?= e((string) ($link['url'] ?? '')) ?>">
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="links[<?= (int) $i ?>][open_in_new_tab]" value="1"
                                            <?= ((int) ($link['open_in_new_tab'] ?? 1) === 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label">New tab</label>
                                    </div>
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
                    <h3 class="h6 mb-0">Tables</h3>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-table">Add Table</button>
                </div>
                <div class="card-body" id="tables-list">
                    <?php if ($tables === []): ?>
                        <p class="text-muted small" id="tables-empty">No tables yet. Paste HTML table markup below.</p>
                    <?php endif; ?>
                    <?php foreach ($tables as $i => $table): ?>
                        <div class="border rounded p-3 mb-2 table-row">
                            <input type="hidden" name="tables[<?= (int) $i ?>][id]" value="<?= e((string) ($table['id'] ?? '')) ?>">
                            <div class="mb-2 d-flex gap-2">
                                <input type="text" name="tables[<?= (int) $i ?>][title]" class="form-control" placeholder="Table title"
                                       value="<?= e((string) ($table['title'] ?? '')) ?>">
                                <button type="button" class="btn btn-outline-danger remove-row">&times;</button>
                            </div>
                            <textarea name="tables[<?= (int) $i ?>][table_html]" class="form-control font-monospace" rows="6"
                                      placeholder="<table>...</table>"><?= e((string) ($table['table_html'] ?? '')) ?></textarea>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h3 class="h6 mb-0">Upload Files</h3></div>
                <div class="card-body">
                    <div class="row g-2" id="files-new">
                        <div class="col-md-5"><input type="text" name="file_title[]" class="form-control" placeholder="File title"></div>
                        <div class="col-md-7"><input type="file" name="file_upload[]" class="form-control"></div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-file">Add another file</button>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h3 class="h6 mb-0">Upload Images</h3></div>
                <div class="card-body">
                    <div class="row g-2" id="images-new">
                        <div class="col-md-4"><input type="text" name="image_title[]" class="form-control" placeholder="Title"></div>
                        <div class="col-md-4"><input type="text" name="image_caption[]" class="form-control" placeholder="Caption"></div>
                        <div class="col-md-4"><input type="file" name="image_upload[]" class="form-control" accept="image/*"></div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-image">Add another image</button>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white"><h3 class="h6 mb-0">Status</h3></div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="status"
                            <?= $status === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white"><h3 class="h6 mb-0">Files</h3></div>
            <div class="card-body">
                <?php if ($files === []): ?>
                    <p class="text-muted small mb-0">No files uploaded.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($files as $file): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="<?= e(upload_url((string) $file['file_path'])) ?>" target="_blank" rel="noopener">
                                    <?= e((string) $file['title']) ?>
                                </a>
                                <form method="post" action="<?= e(url('naac/' . $criterion['id'] . '/files/' . $file['id'] . '/delete')) ?>"
                                      onsubmit="return confirm('Remove this file?');">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white"><h3 class="h6 mb-0">Images</h3></div>
            <div class="card-body">
                <?php if ($images === []): ?>
                    <p class="text-muted small mb-0">No images yet.</p>
                <?php else: ?>
                    <div class="row g-2">
                        <?php foreach ($images as $img): ?>
                            <div class="col-md-3">
                                <div class="border rounded p-2 h-100">
                                    <img src="<?= e(upload_url((string) $img['image_path'])) ?>" alt="" class="img-fluid rounded mb-2">
                                    <div class="small mb-1"><?= e((string) ($img['title'] ?? '')) ?></div>
                                    <div class="small text-muted mb-2"><?= e((string) ($img['caption'] ?? '')) ?></div>
                                    <form method="post" action="<?= e(url('naac/' . $criterion['id'] . '/images/' . $img['id'] . '/delete')) ?>"
                                          onsubmit="return confirm('Remove this image?');">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-danger w-100" type="submit">Remove</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="h6 mb-0">Dynamic Pages</h3>
                <a href="<?= e(url('naac/' . $criterion['id'] . '/pages/create')) ?>" class="btn btn-sm btn-outline-primary">Add Page</a>
            </div>
            <div class="card-body">
                <?php if ($pages === []): ?>
                    <p class="text-muted small mb-0">No dynamic pages for this criterion.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($pages as $page): ?>
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between gap-2">
                                    <div>
                                        <strong><?= e((string) $page['title']) ?></strong>
                                        <div class="small text-muted">
                                            <code><?= e(\App\Models\NaacCriterion::pagePublicUrl($criterion, $page)) ?></code>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="<?= e(url('naac/' . $criterion['id'] . '/pages/' . $page['id'] . '/edit')) ?>">Edit</a>
                                        <form method="post"
                                              action="<?= e(url('naac/' . $criterion['id'] . '/pages/' . $page['id'] . '/delete')) ?>"
                                              onsubmit="return confirm('Delete this page?');">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<template id="link-template">
    <div class="border rounded p-3 mb-2 link-row">
        <input type="hidden" name="links[__i__][id]" value="">
        <div class="row g-2 align-items-center">
            <div class="col-md-4"><input type="text" name="links[__i__][title]" class="form-control" placeholder="Title"></div>
            <div class="col-md-5"><input type="url" name="links[__i__][url]" class="form-control" placeholder="https://"></div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="links[__i__][open_in_new_tab]" value="1" checked>
                    <label class="form-check-label">New tab</label>
                </div>
            </div>
            <div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger remove-row">&times;</button></div>
        </div>
    </div>
</template>

<template id="table-template">
    <div class="border rounded p-3 mb-2 table-row">
        <input type="hidden" name="tables[__i__][id]" value="">
        <div class="mb-2 d-flex gap-2">
            <input type="text" name="tables[__i__][title]" class="form-control" placeholder="Table title">
            <button type="button" class="btn btn-outline-danger remove-row">&times;</button>
        </div>
        <textarea name="tables[__i__][table_html]" class="form-control font-monospace" rows="6" placeholder="<table>...</table>"></textarea>
    </div>
</template>

<script>
(() => {
    function bindRemove(scope) {
        scope.querySelectorAll('.remove-row').forEach((btn) => {
            btn.onclick = () => btn.closest('.link-row, .table-row')?.remove();
        });
    }
    bindRemove(document);

    let linkIndex = <?= count($links) ?>;
    document.getElementById('add-link')?.addEventListener('click', () => {
        document.getElementById('links-empty')?.remove();
        const html = document.getElementById('link-template').innerHTML.replaceAll('__i__', String(linkIndex++));
        const wrap = document.createElement('div');
        wrap.innerHTML = html;
        const node = wrap.firstElementChild;
        document.getElementById('links-list').appendChild(node);
        bindRemove(node);
    });

    let tableIndex = <?= count($tables) ?>;
    document.getElementById('add-table')?.addEventListener('click', () => {
        document.getElementById('tables-empty')?.remove();
        const html = document.getElementById('table-template').innerHTML.replaceAll('__i__', String(tableIndex++));
        const wrap = document.createElement('div');
        wrap.innerHTML = html;
        const node = wrap.firstElementChild;
        document.getElementById('tables-list').appendChild(node);
        bindRemove(node);
    });

    document.getElementById('add-file')?.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'row g-2 mt-2';
        row.innerHTML = `
            <div class="col-md-5"><input type="text" name="file_title[]" class="form-control" placeholder="File title"></div>
            <div class="col-md-7"><input type="file" name="file_upload[]" class="form-control"></div>`;
        document.getElementById('add-file').before(row);
    });

    document.getElementById('add-image')?.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'row g-2 mt-2';
        row.innerHTML = `
            <div class="col-md-4"><input type="text" name="image_title[]" class="form-control" placeholder="Title"></div>
            <div class="col-md-4"><input type="text" name="image_caption[]" class="form-control" placeholder="Caption"></div>
            <div class="col-md-4"><input type="file" name="image_upload[]" class="form-control" accept="image/*"></div>`;
        document.getElementById('add-image').before(row);
    });
})();
</script>

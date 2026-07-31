<?php
/** @var list<array<string,mixed>> $folders */
/** @var list<array<string,mixed>> $files */
/** @var array<string,mixed>|null $currentFolder */
/** @var int|null $folderId */
/** @var list<array<string,mixed>> $breadcrumb */
/** @var string $search */
/** @var string|null $success */
/** @var string|null $error */
/** @var bool $canManage */
/** @var list<string> $allowed */
/** @var string $csrfToken */
?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e((string) $success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <nav aria-label="breadcrumb" class="mb-0">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= e(url('media')) ?>">Root</a></li>
                    <?php foreach ($breadcrumb as $crumb): ?>
                        <li class="breadcrumb-item">
                            <a href="<?= e(url('media?folder=' . $crumb['id'])) ?>"><?= e((string) $crumb['name']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <div class="small text-muted">Allowed: <?= e(strtoupper(implode(', ', $allowed))) ?></div>
        </div>

        <div class="d-flex flex-wrap gap-2 justify-content-between">
            <form method="get" action="<?= e(url('media')) ?>" class="d-flex gap-2">
                <?php if ($folderId !== null && $search === ''): ?>
                    <input type="hidden" name="folder" value="<?= (int) $folderId ?>">
                <?php endif; ?>
                <input type="search" name="q" value="<?= e($search) ?>" class="form-control" placeholder="Search files..." style="min-width:220px">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
                <?php if ($search !== ''): ?>
                    <a href="<?= e(url($folderId !== null ? 'media?folder=' . $folderId : 'media')) ?>" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>

            <?php if ($canManage && $search === ''): ?>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                        <i class="bi bi-folder-plus"></i> New Folder
                    </button>
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-upload"></i> Upload Files
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($search !== ''): ?>
    <div class="alert alert-info py-2">Search results for <strong><?= e($search) ?></strong></div>
<?php endif; ?>

<div class="row g-3">
    <?php if ($search === ''): ?>
        <?php foreach ($folders as $folder): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <a href="<?= e(url('media?folder=' . $folder['id'])) ?>" class="text-decoration-none text-dark d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-folder-fill text-warning fs-3"></i>
                            <strong><?= e((string) $folder['name']) ?></strong>
                        </a>
                        <div class="small text-muted mb-2">
                            <?= (int) ($folder['child_folders'] ?? 0) ?> folders · <?= (int) ($folder['file_count'] ?? 0) ?> files
                        </div>
                        <?php if ($canManage): ?>
                            <form method="post" action="<?= e(url('media/folders/' . $folder['id'] . '/delete')) ?>"
                                  onsubmit="return confirm('Delete this folder and all contents?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php foreach ($files as $file): ?>
        <?php
        $ext = strtolower((string) $file['extension']);
        $url = upload_url((string) $file['file_path']);
        $isImage = \App\Models\Media::isImage($ext);
        $canPreview = \App\Models\Media::isPreviewable($ext);
        $icon = match ($ext) {
            'pdf' => 'bi-file-earmark-pdf text-danger',
            'doc', 'docx' => 'bi-file-earmark-word text-primary',
            'zip' => 'bi-file-earmark-zip text-warning',
            'jpg', 'png' => 'bi-file-earmark-image text-success',
            default => 'bi-file-earmark',
        };
        ?>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card shadow-sm h-100 media-file-card">
                <div class="card-body">
                    <div class="media-thumb mb-2 text-center">
                        <?php if ($isImage): ?>
                            <img src="<?= e($url) ?>" alt="" class="img-fluid rounded border" style="max-height:120px;object-fit:cover">
                        <?php else: ?>
                            <i class="bi <?= e($icon) ?>" style="font-size:3rem"></i>
                        <?php endif; ?>
                    </div>
                    <div class="small fw-semibold text-truncate" title="<?= e((string) $file['original_name']) ?>">
                        <?= e((string) $file['original_name']) ?>
                    </div>
                    <div class="small text-muted mb-2">
                        <?= e(strtoupper($ext)) ?> · <?= e(\App\Models\Media::formatSize((int) $file['size_bytes'])) ?>
                        <?php if ($search !== '' && !empty($file['folder_name'])): ?>
                            · in <?= e((string) $file['folder_name']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php if ($canPreview): ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-preview"
                                    data-url="<?= e($url) ?>"
                                    data-name="<?= e((string) $file['original_name']) ?>"
                                    data-type="<?= e($ext) ?>">
                                Preview
                            </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-copy-link"
                                data-url="<?= e($url) ?>">
                            Copy Link
                        </button>
                        <?php if ($canManage): ?>
                            <button type="button" class="btn btn-sm btn-outline-warning btn-replace"
                                    data-id="<?= (int) $file['id'] ?>"
                                    data-name="<?= e((string) $file['original_name']) ?>">
                                Replace
                            </button>
                            <form method="post" action="<?= e(url('media/files/' . $file['id'] . '/delete')) ?>"
                                  onsubmit="return confirm('Delete this file?');" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if ($folders === [] && $files === []): ?>
        <div class="col-12">
            <div class="card shadow-sm"><div class="card-body text-muted text-center py-5">This folder is empty.</div></div>
        </div>
    <?php endif; ?>
</div>

<?php if ($canManage): ?>
<!-- Create Folder -->
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="<?= e(url('media/folders')) ?>" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="parent_id" value="<?= $folderId !== null ? (int) $folderId : '' ?>">
            <div class="modal-header">
                <h5 class="modal-title">Create Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label" for="folder_name">Folder name</label>
                <input type="text" name="name" id="folder_name" class="form-control" required maxlength="150">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<!-- Multiple Upload -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="<?= e(url('media/upload')) ?>" enctype="multipart/form-data" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="folder_id" value="<?= $folderId !== null ? (int) $folderId : '' ?>">
            <div class="modal-header">
                <h5 class="modal-title">Upload Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="file" name="files[]" class="form-control" multiple required
                       accept=".pdf,.doc,.docx,.jpg,.png,.zip,application/pdf,image/jpeg,image/png,application/zip">
                <div class="form-text">You can select multiple files. Max 25MB each.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Replace -->
<div class="modal fade" id="replaceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" id="replace-form" enctype="multipart/form-data" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Replace File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-2">Replacing: <strong id="replace-name"></strong></p>
                <input type="file" name="file" class="form-control" required
                       accept=".pdf,.doc,.docx,.jpg,.png,.zip,application/pdf,image/jpeg,image/png,application/zip">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">Replace</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Preview -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="preview-title">Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="preview-body" style="min-height:320px"></div>
        </div>
    </div>
</div>

<div id="copy-toast" class="alert alert-success position-fixed bottom-0 end-0 m-3 d-none" style="z-index:1080">Link copied</div>

<script>
(() => {
    const previewModalEl = document.getElementById('previewModal');
    const previewModal = previewModalEl ? new bootstrap.Modal(previewModalEl) : null;
    const replaceModalEl = document.getElementById('replaceModal');
    const replaceModal = replaceModalEl ? new bootstrap.Modal(replaceModalEl) : null;

    document.querySelectorAll('.btn-preview').forEach((btn) => {
        btn.addEventListener('click', () => {
            const url = btn.dataset.url;
            const name = btn.dataset.name;
            const type = (btn.dataset.type || '').toLowerCase();
            document.getElementById('preview-title').textContent = name;
            const body = document.getElementById('preview-body');
            if (['jpg', 'jpeg', 'png'].includes(type)) {
                body.innerHTML = `<img src="${url}" alt="" class="img-fluid mx-auto d-block">`;
            } else if (type === 'pdf') {
                body.innerHTML = `<iframe src="${url}" title="PDF preview" style="width:100%;height:70vh;border:0"></iframe>`;
            } else {
                body.innerHTML = `<p class="text-muted">Preview not available. <a href="${url}" target="_blank" rel="noopener">Open file</a></p>`;
            }
            previewModal?.show();
        });
    });

    document.querySelectorAll('.btn-copy-link').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const url = btn.dataset.url;
            const absolute = new URL(url, window.location.origin).toString();
            try {
                await navigator.clipboard.writeText(absolute);
            } catch (e) {
                const tmp = document.createElement('input');
                tmp.value = absolute;
                document.body.appendChild(tmp);
                tmp.select();
                document.execCommand('copy');
                tmp.remove();
            }
            const toast = document.getElementById('copy-toast');
            toast.classList.remove('d-none');
            setTimeout(() => toast.classList.add('d-none'), 1600);
        });
    });

    document.querySelectorAll('.btn-replace').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            document.getElementById('replace-name').textContent = btn.dataset.name || '';
            document.getElementById('replace-form').action = <?= json_encode(url('media/files')) ?> + '/' + id + '/replace';
            replaceModal?.show();
        });
    });
})();
</script>

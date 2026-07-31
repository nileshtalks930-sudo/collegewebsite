<?php
/** @var string $type */
/** @var list<array<string,mixed>> $folders */
/** @var list<array<string,mixed>> $files */
/** @var int|null $folderId */
/** @var list<array<string,mixed>> $breadcrumb */
/** @var string $search */
/** @var bool $canManage */
/** @var string $csrfToken */
/** @var string $uploadUrl */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File Browser | College CMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f5f7fb; }
        .file-card { cursor: pointer; transition: box-shadow .15s ease; }
        .file-card:hover { box-shadow: 0 0 0 .2rem rgba(13,110,253,.25); }
        .thumb { height: 90px; object-fit: cover; width: 100%; }
    </style>
</head>
<body>
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h5 mb-1">File Browser</h1>
            <div class="small text-muted">Select a file for TinyMCE (<?= e($type) ?>)</div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.close()">Close</button>
    </div>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= e(url('media/picker?type=' . urlencode($type))) ?>">Root</a></li>
            <?php foreach ($breadcrumb as $crumb): ?>
                <li class="breadcrumb-item">
                    <a href="<?= e(url('media/picker?type=' . urlencode($type) . '&folder=' . $crumb['id'])) ?>">
                        <?= e((string) $crumb['name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <form method="get" action="<?= e(url('media/picker')) ?>" class="d-flex gap-2">
            <input type="hidden" name="type" value="<?= e($type) ?>">
            <?php if ($folderId !== null): ?>
                <input type="hidden" name="folder" value="<?= (int) $folderId ?>">
            <?php endif; ?>
            <input type="search" name="q" value="<?= e($search) ?>" class="form-control form-control-sm" placeholder="Search">
            <button class="btn btn-sm btn-outline-secondary" type="submit">Search</button>
        </form>
        <?php if ($canManage && $type === 'image'): ?>
            <form id="quick-upload" class="d-flex gap-2">
                <input type="file" id="quick-file" accept="image/*" class="form-control form-control-sm">
                <button type="submit" class="btn btn-sm btn-primary">Upload Image</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="row g-2 mb-3">
        <?php if ($search === ''): ?>
            <?php foreach ($folders as $folder): ?>
                <div class="col-6 col-md-3">
                    <a class="card file-card text-decoration-none text-dark"
                       href="<?= e(url('media/picker?type=' . urlencode($type) . '&folder=' . $folder['id'])) ?>">
                        <div class="card-body py-3">
                            <i class="bi bi-folder-fill text-warning"></i>
                            <strong class="ms-1"><?= e((string) $folder['name']) ?></strong>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php foreach ($files as $file): ?>
            <?php
            $ext = strtolower((string) $file['extension']);
            $fileUrl = absolute_url(upload_url((string) $file['file_path']));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
            ?>
            <div class="col-6 col-md-3">
                <div class="card file-card h-100 pick-file"
                     data-url="<?= e($fileUrl) ?>"
                     data-name="<?= e((string) $file['original_name']) ?>"
                     data-type="<?= e($ext) ?>">
                    <div class="card-body">
                        <?php if ($isImage): ?>
                            <img src="<?= e($fileUrl) ?>" alt="" class="thumb rounded border mb-2">
                        <?php else: ?>
                            <div class="mb-2"><i class="bi bi-file-earmark fs-2"></i></div>
                        <?php endif; ?>
                        <div class="small fw-semibold text-truncate"><?= e((string) $file['original_name']) ?></div>
                        <div class="small text-muted"><?= e(strtoupper($ext)) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($folders === [] && $files === []): ?>
        <div class="alert alert-light border">No files found in this location.</div>
    <?php endif; ?>

    <?php if ($type === 'media'): ?>
        <div class="alert alert-info small mb-0">
            For YouTube/Vimeo embeds, paste the video URL in TinyMCE’s media dialog. Use this browser to insert hosted files/images.
        </div>
    <?php endif; ?>
</div>

<script>
(() => {
    function selectFile(url, name, type) {
        const payload = { url, name, type };
        if (window.opener && typeof window.opener.CollegeCmsEditor?.onFilePicked === 'function') {
            window.opener.CollegeCmsEditor.onFilePicked(payload);
            window.close();
            return;
        }
        // Fallback for older callback style
        if (window.opener && typeof window.opener.cmsFileBrowserCallback === 'function') {
            window.opener.cmsFileBrowserCallback(url, { text: name, alt: name, title: name });
            window.close();
        }
    }

    document.querySelectorAll('.pick-file').forEach((el) => {
        el.addEventListener('click', () => {
            selectFile(el.dataset.url, el.dataset.name, el.dataset.type);
        });
    });

    const form = document.getElementById('quick-upload');
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = document.getElementById('quick-file');
        if (!input.files?.length) return;
        const data = new FormData();
        data.append('file', input.files[0]);
        data.append('_token', <?= json_encode($csrfToken) ?>);
        const res = await fetch(<?= json_encode($uploadUrl) ?>, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': <?= json_encode($csrfToken) ?>, 'Accept': 'application/json' },
            body: data,
        });
        const json = await res.json();
        if (json.location) {
            selectFile(json.location, input.files[0].name, 'image');
        } else {
            alert(json.error || 'Upload failed');
        }
    });
})();
</script>
</body>
</html>

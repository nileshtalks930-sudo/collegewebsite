<?php
/** @var array<string,mixed>|null $menu */
/** @var array<string,string> $positions */
/** @var array<string,string> $linkTypes */
/** @var list<array<string,mixed>> $parents */
/** @var list<array<string,mixed>> $pages */
/** @var list<array<string,mixed>> $departments */
/** @var list<array<string,mixed>> $naacCriteria */
/** @var array<string,string> $iqacSections */
/** @var list<array<string,mixed>> $downloads */
/** @var list<array<string,mixed>> $galleries */
/** @var string|null $error */
/** @var array<string,mixed> $old */
/** @var string $defaultPosition */

$isEdit = $menu !== null;
$action = $isEdit ? url('menus/' . $menu['id'] . '/update') : url('menus');

$name = (string) ($old['name'] ?? $menu['name'] ?? '');
$position = (string) ($old['position'] ?? $menu['position'] ?? $defaultPosition ?? 'header');
$parentId = array_key_exists('parent_id', $old)
    ? ($old['parent_id'] !== null && $old['parent_id'] !== '' ? (int) $old['parent_id'] : 0)
    : (int) ($menu['parent_id'] ?? 0);
$sortOrder = (int) ($old['sort_order'] ?? $menu['sort_order'] ?? 0);
$status = array_key_exists('status', $old) ? (int) $old['status'] : (int) ($menu['status'] ?? 1);
$openNew = array_key_exists('open_in_new_tab', $old)
    ? (int) $old['open_in_new_tab']
    : (int) ($menu['open_in_new_tab'] ?? 0);
$linkType = (string) ($old['link_type'] ?? $menu['link_type'] ?? 'custom');
if ($linkType === 'url') {
    $linkType = preg_match('#^https?://#i', (string) ($old['url'] ?? $menu['url'] ?? '')) ? 'external' : 'custom';
}
$urlValue = (string) ($old['url'] ?? $menu['url'] ?? '');
$pageId = array_key_exists('page_id', $old) ? (int) ($old['page_id'] ?? 0) : (int) ($menu['page_id'] ?? 0);
$departmentId = array_key_exists('department_id', $old)
    ? (int) ($old['department_id'] ?? 0)
    : (int) ($menu['department_id'] ?? 0);
$naacCriterionId = array_key_exists('naac_criterion_id', $old)
    ? (int) ($old['naac_criterion_id'] ?? 0)
    : (int) ($menu['naac_criterion_id'] ?? 0);
$iqacSection = (string) ($old['iqac_section'] ?? $menu['iqac_section'] ?? '');
$downloadId = array_key_exists('download_id', $old)
    ? (int) ($old['download_id'] ?? 0)
    : (int) ($menu['download_id'] ?? 0);
$galleryId = array_key_exists('gallery_id', $old)
    ? (int) ($old['gallery_id'] ?? 0)
    : (int) ($menu['gallery_id'] ?? 0);
?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0 h5"><?= e($title ?? ($isEdit ? 'Edit Menu Item' : 'Add Menu Item')) ?></h3>
        <a href="<?= e(url('menus')) ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
    <div class="card-body">
        <form method="post" action="<?= e($action) ?>" id="menu-form">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="name">Menu Name</label>
                    <input type="text" name="name" id="name" class="form-control" required maxlength="150"
                           value="<?= e($name) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="position">Menu Position</label>
                    <select name="position" id="position" class="form-select" required>
                        <?php foreach ($positions as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $position === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="sort_order">Order</label>
                    <input type="number" name="sort_order" id="sort_order" class="form-control"
                           value="<?= $sortOrder ?>" <?= $isEdit ? '' : 'readonly' ?>
                           title="<?= $isEdit ? 'Or use drag-and-drop on the list' : 'Auto-assigned; reorder via drag-and-drop' ?>">
                    <?php if (!$isEdit): ?>
                        <div class="form-text">Auto-set; drag to reorder later.</div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="parent_id">Parent Menu</label>
                    <select name="parent_id" id="parent_id" class="form-select">
                        <option value="">— None (top level) —</option>
                        <?php foreach ($parents as $parent): ?>
                            <option value="<?= (int) $parent['id'] ?>"
                                    data-position="<?= e((string) $parent['position']) ?>"
                                    <?= $parentId === (int) $parent['id'] ? 'selected' : '' ?>>
                                <?= e((string) $parent['name']) ?>
                                (<?= e($positions[$parent['position']] ?? (string) $parent['position']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="link_type">Link To</label>
                    <select name="link_type" id="link_type" class="form-select" required>
                        <?php foreach ($linkTypes as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $linkType === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 link-field" data-types="external,custom">
                    <label class="form-label" for="url">
                        <span class="label-external">External URL</span>
                        <span class="label-custom d-none">Custom Link</span>
                    </label>
                    <input type="text" name="url" id="url" class="form-control" maxlength="500"
                           placeholder="https://example.com or /path" value="<?= e($urlValue) ?>">
                    <div class="form-text link-hint-external">Full URL starting with http:// or https://</div>
                    <div class="form-text link-hint-custom d-none">Internal path such as /admissions/apply</div>
                </div>

                <div class="col-md-6 link-field" data-types="page">
                    <label class="form-label" for="page_id">Internal Page</label>
                    <select name="page_id" id="page_id" class="form-select">
                        <option value="">Select page</option>
                        <?php foreach ($pages as $page): ?>
                            <option value="<?= (int) $page['id'] ?>" <?= $pageId === (int) $page['id'] ? 'selected' : '' ?>>
                                <?= e((string) $page['title']) ?> (<?= e((string) $page['slug']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 link-field" data-types="department">
                    <label class="form-label" for="department_id">Department</label>
                    <select name="department_id" id="department_id" class="form-select">
                        <option value="">Select department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= (int) $dept['id'] ?>" <?= $departmentId === (int) $dept['id'] ? 'selected' : '' ?>>
                                <?= e((string) $dept['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 link-field" data-types="naac">
                    <label class="form-label" for="naac_criterion_id">NAAC Criterion</label>
                    <select name="naac_criterion_id" id="naac_criterion_id" class="form-select">
                        <option value="">NAAC home (/naac)</option>
                        <?php foreach ($naacCriteria as $criterion): ?>
                            <option value="<?= (int) $criterion['id'] ?>" <?= $naacCriterionId === (int) $criterion['id'] ? 'selected' : '' ?>>
                                Criterion <?= (int) ($criterion['number'] ?? 0) ?> — <?= e((string) $criterion['heading']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 link-field" data-types="iqac">
                    <label class="form-label" for="iqac_section">IQAC Section</label>
                    <select name="iqac_section" id="iqac_section" class="form-select">
                        <option value="">IQAC home (/iqac)</option>
                        <?php foreach ($iqacSections as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $iqacSection === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 link-field" data-types="downloads">
                    <label class="form-label" for="download_id">Download</label>
                    <select name="download_id" id="download_id" class="form-select">
                        <option value="">Downloads home (/downloads)</option>
                        <?php foreach ($downloads as $item): ?>
                            <option value="<?= (int) $item['id'] ?>" <?= $downloadId === (int) $item['id'] ? 'selected' : '' ?>>
                                <?= e((string) $item['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 link-field" data-types="gallery">
                    <label class="form-label" for="gallery_id">Gallery</label>
                    <select name="gallery_id" id="gallery_id" class="form-select">
                        <option value="">Gallery home (/gallery)</option>
                        <?php foreach ($galleries as $item): ?>
                            <option value="<?= (int) $item['id'] ?>" <?= $galleryId === (int) $item['id'] ? 'selected' : '' ?>>
                                <?= e((string) $item['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 d-flex align-items-end gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="status"
                            <?= $status === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="open_in_new_tab" value="1" id="open_in_new_tab"
                            <?= $openNew === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="open_in_new_tab">Open in New Tab</label>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update Menu' : 'Create Menu' ?></button>
                <a href="<?= e(url('menus')) ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const linkType = document.getElementById('link_type');
    const urlInput = document.getElementById('url');
    const positionSelect = document.getElementById('position');
    const parentSelect = document.getElementById('parent_id');
    const fields = Array.from(document.querySelectorAll('.link-field'));

    function syncLinkFields() {
        const type = linkType.value;
        fields.forEach((el) => {
            const types = (el.dataset.types || '').split(',').map((t) => t.trim());
            const show = types.includes(type);
            el.classList.toggle('d-none', !show);
            el.querySelectorAll('input, select').forEach((input) => {
                if (input.id === 'url') {
                    input.required = show && (type === 'external' || type === 'custom');
                } else if (input.id === 'page_id') {
                    input.required = show && type === 'page';
                } else if (input.id === 'department_id') {
                    input.required = show && type === 'department';
                } else {
                    input.required = false;
                }
            });
        });

        document.querySelectorAll('.label-external, .link-hint-external').forEach((el) => {
            el.classList.toggle('d-none', type !== 'external');
        });
        document.querySelectorAll('.label-custom, .link-hint-custom').forEach((el) => {
            el.classList.toggle('d-none', type !== 'custom');
        });

        if (type === 'external') {
            urlInput.placeholder = 'https://example.com';
        } else if (type === 'custom') {
            urlInput.placeholder = '/path or /section/page';
        }
    }

    function syncParents() {
        const pos = positionSelect.value;
        Array.from(parentSelect.options).forEach((opt) => {
            if (!opt.value) {
                opt.hidden = false;
                return;
            }
            const match = opt.dataset.position === pos;
            opt.hidden = !match;
            if (!match && opt.selected) {
                parentSelect.value = '';
            }
        });
    }

    linkType.addEventListener('change', syncLinkFields);
    positionSelect.addEventListener('change', syncParents);
    syncLinkFields();
    syncParents();
})();
</script>

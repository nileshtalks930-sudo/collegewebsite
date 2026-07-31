<?php
/** @var array<string,mixed>|null $menu */
/** @var array<string,string> $positions */
/** @var list<array<string,mixed>> $parents */
/** @var list<array<string,mixed>> $pages */
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
$linkType = (string) ($old['link_type'] ?? $menu['link_type'] ?? 'url');
$urlValue = (string) ($old['url'] ?? $menu['url'] ?? '');
$pageId = array_key_exists('page_id', $old)
    ? (int) ($old['page_id'] ?? 0)
    : (int) ($menu['page_id'] ?? 0);
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
                    <label class="form-label" for="link_type">URL or Internal Page</label>
                    <select name="link_type" id="link_type" class="form-select" required>
                        <option value="url" <?= $linkType === 'url' ? 'selected' : '' ?>>Custom URL</option>
                        <option value="page" <?= $linkType === 'page' ? 'selected' : '' ?>>Internal Page</option>
                    </select>
                </div>

                <div class="col-md-6 link-url">
                    <label class="form-label" for="url">URL</label>
                    <input type="text" name="url" id="url" class="form-control" maxlength="500"
                           placeholder="/about or https://example.com" value="<?= e($urlValue) ?>">
                </div>

                <div class="col-md-6 link-page">
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
    const urlBox = document.querySelector('.link-url');
    const pageBox = document.querySelector('.link-page');
    const urlInput = document.getElementById('url');
    const pageSelect = document.getElementById('page_id');
    const positionSelect = document.getElementById('position');
    const parentSelect = document.getElementById('parent_id');

    function syncLinkFields() {
        const isPage = linkType.value === 'page';
        urlBox.classList.toggle('d-none', isPage);
        pageBox.classList.toggle('d-none', !isPage);
        urlInput.required = !isPage;
        pageSelect.required = isPage;
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

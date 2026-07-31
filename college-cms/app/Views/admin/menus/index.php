<?php
/** @var array<string, list<array<string,mixed>>> $trees */
/** @var array<string,string> $positions */
/** @var string $filterPosition */
/** @var string|null $success */
/** @var string|null $error */
/** @var bool $canManage */
/** @var string $csrfToken */
/** @var string $reorderUrl */

$renderItems = null;
$renderItems = static function (array $nodes, bool $canManage) use (&$renderItems): void {
    echo '<ol class="menu-sortable list-unstyled mb-0">';
    foreach ($nodes as $node) {
        $href = \App\Models\Menu::resolveHref($node);
        $badge = \App\Models\Menu::linkBadge($node);
        echo '<li class="menu-item" data-id="' . (int) $node['id'] . '">';
        echo '<div class="menu-row d-flex align-items-center gap-2">';
        if ($canManage) {
            echo '<span class="drag-handle text-muted" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>';
        }
        echo '<div class="flex-grow-1">';
        echo '<strong>' . e((string) $node['name']) . '</strong> ';
        echo '<span class="badge text-bg-' . ((int) $node['status'] === 1 ? 'success' : 'secondary') . '">'
            . ((int) $node['status'] === 1 ? 'Active' : 'Inactive') . '</span> ';
        if ((int) ($node['open_in_new_tab'] ?? 0) === 1) {
            echo '<span class="badge text-bg-light border">New tab</span> ';
        }
        echo '<div class="small text-muted">' . e((string) $badge) . ' · <code>' . e($href) . '</code></div>';
        echo '</div>';
        if ($canManage) {
            echo '<div class="d-flex gap-1">';
            echo '<a class="btn btn-sm btn-outline-primary" href="' . e(url('menus/' . $node['id'] . '/edit')) . '">Edit</a>';
            echo '<form method="post" action="' . e(url('menus/' . $node['id'] . '/delete')) . '" onsubmit="return confirm(\'Delete this menu item?\');">';
            echo csrf_field();
            echo '<button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>';
            echo '</form></div>';
        }
        echo '</div>';
        $children = $node['children'] ?? [];
        if (is_array($children) && $children !== []) {
            $renderItems($children, $canManage);
        } else {
            echo '<ol class="menu-sortable list-unstyled mb-0"></ol>';
        }
        echo '</li>';
    }
    echo '</ol>';
};
?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e((string) $success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <form method="get" action="<?= e(url('menus')) ?>" class="d-flex gap-2 align-items-center">
            <label class="form-label mb-0" for="position">Position</label>
            <select name="position" id="position" class="form-select" onchange="this.form.submit()" style="min-width:160px">
                <option value="">All</option>
                <?php foreach ($positions as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $filterPosition === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php if ($canManage): ?>
            <a href="<?= e(url('menus/create' . ($filterPosition !== '' ? '?position=' . urlencode($filterPosition) : ''))) ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add Menu Item
            </a>
        <?php endif; ?>
    </div>
</div>

<div id="menu-reorder-status" class="alert d-none py-2" role="status"></div>

<?php if ($trees === []): ?>
    <div class="card shadow-sm"><div class="card-body text-muted">No menu items yet.</div></div>
<?php else: ?>
    <?php foreach ($trees as $pos => $nodes): ?>
        <div class="card shadow-sm mb-3 menu-position-block" data-position="<?= e((string) $pos) ?>">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="h6 mb-0"><?= e($positions[$pos] ?? ucfirst((string) $pos)) ?> Menu</h3>
                <?php if ($canManage): ?>
                    <span class="small text-muted">Drag items to reorder or nest under a parent</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($nodes === []): ?>
                    <p class="text-muted mb-0">No items in this position.</p>
                <?php else: ?>
                    <?php $renderItems($nodes, $canManage); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<style>
    .menu-item { margin: 0.35rem 0; }
    .menu-row {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 0.65rem 0.75rem;
    }
    .menu-sortable { min-height: 8px; padding-left: 1.25rem; }
    .menu-position-block > .card-body > .menu-sortable { padding-left: 0; }
    .drag-handle { cursor: grab; font-size: 1.15rem; }
    .sortable-ghost { opacity: 0.45; }
    .sortable-chosen .menu-row { border-color: #0d6efd; box-shadow: 0 0 0 0.15rem rgba(13,110,253,.15); }
</style>

<?php if ($canManage): ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(() => {
    const reorderUrl = <?= json_encode($reorderUrl, JSON_UNESCAPED_SLASHES) ?>;
    const csrfToken = <?= json_encode($csrfToken) ?>;
    const statusEl = document.getElementById('menu-reorder-status');
    let saveTimer = null;

    function showStatus(ok, message) {
        statusEl.classList.remove('d-none', 'alert-success', 'alert-danger');
        statusEl.classList.add(ok ? 'alert-success' : 'alert-danger');
        statusEl.textContent = message;
    }

    function serializeList(ol, parentId) {
        const items = [];
        Array.from(ol.children).forEach((li, index) => {
            if (!li.classList.contains('menu-item')) return;
            const id = parseInt(li.dataset.id, 10);
            items.push({ id, parent_id: parentId, sort_order: index });
            const childOl = li.querySelector(':scope > ol.menu-sortable');
            if (childOl) {
                items.push(...serializeList(childOl, id));
            }
        });
        return items;
    }

    function collectAll() {
        const items = [];
        document.querySelectorAll('.menu-position-block').forEach((block) => {
            const root = block.querySelector(':scope > .card-body > ol.menu-sortable');
            if (root) items.push(...serializeList(root, null));
        });
        return items;
    }

    async function saveOrder() {
        const items = collectAll();
        try {
            const res = await fetch(reorderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ _token: csrfToken, items }),
            });
            const data = await res.json();
            showStatus(!!data.ok, data.message || (data.ok ? 'Order saved.' : 'Save failed.'));
        } catch (e) {
            showStatus(false, 'Network error while saving order.');
        }
    }

    function scheduleSave() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveOrder, 250);
    }

    document.querySelectorAll('.menu-position-block').forEach((block) => {
        const position = block.dataset.position;
        block.querySelectorAll('ol.menu-sortable').forEach((el) => {
            new Sortable(el, {
                group: 'menus-' + position,
                handle: '.drag-handle',
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: scheduleSave,
            });
        });
    });
})();
</script>
<?php endif; ?>

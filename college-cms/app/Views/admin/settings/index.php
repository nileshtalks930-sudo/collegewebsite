<?php
/** @var array<string,?string> $settings */
/** @var array<string,string> $groups */
/** @var string|null $success */
/** @var string|null $error */
/** @var bool $canManage */
/** @var string $activeTab */

$v = static function (string $key) use ($settings): string {
    return e((string) ($settings[$key] ?? ''));
};

$tabs = [
    'general' => ['label' => 'General', 'icon' => 'bi-building'],
    'contact' => ['label' => 'Contact', 'icon' => 'bi-geo-alt'],
    'social' => ['label' => 'Social Links', 'icon' => 'bi-share'],
    'smtp' => ['label' => 'SMTP', 'icon' => 'bi-envelope'],
    'analytics' => ['label' => 'Analytics', 'icon' => 'bi-graph-up'],
    'footer' => ['label' => 'Footer', 'icon' => 'bi-layout-text-window-reverse'],
];
if (!isset($tabs[$activeTab])) {
    $activeTab = 'general';
}

$logo = (string) ($settings['logo'] ?? '');
$favicon = (string) ($settings['favicon'] ?? '');
$readonly = !$canManage;
?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= e((string) $success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= e((string) $error) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(url('settings')) ?>" enctype="multipart/form-data" id="settings-form">
    <?= csrf_field() ?>
    <input type="hidden" name="_tab" id="settings-active-tab" value="<?= e($activeTab) ?>">

    <div class="row g-3">
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="h6 mb-0">Settings</h3>
                </div>
                <div class="list-group list-group-flush" id="settings-tabs" role="tablist">
                    <?php foreach ($tabs as $key => $tab): ?>
                        <button type="button"
                                class="list-group-item list-group-item-action settings-tab <?= $activeTab === $key ? 'active' : '' ?>"
                                data-tab="<?= e($key) ?>">
                            <i class="bi <?= e($tab['icon']) ?> me-2"></i><?= e($tab['label']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <!-- General -->
            <div class="card shadow-sm settings-panel <?= $activeTab === 'general' ? '' : 'd-none' ?>" data-panel="general">
                <div class="card-header bg-white"><h3 class="h6 mb-0">General</h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="website_name">Website Name</label>
                        <input type="text" name="website_name" id="website_name" class="form-control" required maxlength="200"
                               value="<?= $v('website_name') ?>" <?= $readonly ? 'readonly' : '' ?>>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="logo">Logo</label>
                            <?php if ($logo !== ''): ?>
                                <div class="mb-2">
                                    <img src="<?= e(upload_url($logo)) ?>" alt="Logo" class="img-fluid border rounded bg-white p-2" style="max-height:80px">
                                </div>
                                <?php if ($canManage): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                                        <label class="form-check-label" for="remove_logo">Remove logo</label>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <input type="file" name="logo" id="logo" class="form-control" accept="image/*,.svg"
                                <?= $readonly ? 'disabled' : '' ?>>
                            <div class="form-text">PNG, JPG, SVG, WebP · max 2MB</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="favicon">Favicon</label>
                            <?php if ($favicon !== ''): ?>
                                <div class="mb-2">
                                    <img src="<?= e(upload_url($favicon)) ?>" alt="Favicon" class="border rounded bg-white p-1" style="height:32px;width:32px;object-fit:contain">
                                </div>
                                <?php if ($canManage): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="remove_favicon" value="1" id="remove_favicon">
                                        <label class="form-check-label" for="remove_favicon">Remove favicon</label>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <input type="file" name="favicon" id="favicon" class="form-control" accept=".ico,image/*"
                                <?= $readonly ? 'disabled' : '' ?>>
                            <div class="form-text">ICO, PNG · max 512KB</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <div class="card shadow-sm settings-panel <?= $activeTab === 'contact' ? '' : 'd-none' ?>" data-panel="contact">
                <div class="card-header bg-white"><h3 class="h6 mb-0">Contact</h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="address">Address</label>
                        <textarea name="address" id="address" class="form-control" rows="3" maxlength="2000"
                            <?= $readonly ? 'readonly' : '' ?>><?= $v('address') ?></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" maxlength="190"
                                   value="<?= $v('email') ?>" <?= $readonly ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control" maxlength="50"
                                   value="<?= $v('phone') ?>" <?= $readonly ? 'readonly' : '' ?>>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="google_map">Google Map</label>
                        <textarea name="google_map" id="google_map" class="form-control font-monospace" rows="5"
                                  placeholder="Paste Google Maps embed iframe HTML or map URL"
                            <?= $readonly ? 'readonly' : '' ?>><?= $v('google_map') ?></textarea>
                        <div class="form-text">Embed iframe code or a maps.google.com URL.</div>
                    </div>
                </div>
            </div>

            <!-- Social -->
            <div class="card shadow-sm settings-panel <?= $activeTab === 'social' ? '' : 'd-none' ?>" data-panel="social">
                <div class="card-header bg-white"><h3 class="h6 mb-0">Social Links</h3></div>
                <div class="card-body">
                    <?php
                    $socialFields = [
                        'social_facebook' => ['Facebook', 'bi-facebook'],
                        'social_twitter' => ['Twitter / X', 'bi-twitter-x'],
                        'social_instagram' => ['Instagram', 'bi-instagram'],
                        'social_youtube' => ['YouTube', 'bi-youtube'],
                        'social_linkedin' => ['LinkedIn', 'bi-linkedin'],
                    ];
                    ?>
                    <div class="row g-3">
                        <?php foreach ($socialFields as $key => [$label, $icon]): ?>
                            <div class="col-md-6">
                                <label class="form-label" for="<?= e($key) ?>">
                                    <i class="bi <?= e($icon) ?> me-1"></i><?= e($label) ?>
                                </label>
                                <input type="url" name="<?= e($key) ?>" id="<?= e($key) ?>" class="form-control" maxlength="500"
                                       placeholder="https://" value="<?= $v($key) ?>" <?= $readonly ? 'readonly' : '' ?>>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- SMTP -->
            <div class="card shadow-sm settings-panel <?= $activeTab === 'smtp' ? '' : 'd-none' ?>" data-panel="smtp">
                <div class="card-header bg-white"><h3 class="h6 mb-0">SMTP Settings</h3></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="smtp_host">SMTP Host</label>
                            <input type="text" name="smtp_host" id="smtp_host" class="form-control" maxlength="255"
                                   placeholder="smtp.example.com" value="<?= $v('smtp_host') ?>" <?= $readonly ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="smtp_port">Port</label>
                            <input type="text" name="smtp_port" id="smtp_port" class="form-control" maxlength="10"
                                   value="<?= $v('smtp_port') ?>" <?= $readonly ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="smtp_username">Username</label>
                            <input type="text" name="smtp_username" id="smtp_username" class="form-control" maxlength="255"
                                   value="<?= $v('smtp_username') ?>" <?= $readonly ? 'readonly' : '' ?> autocomplete="off">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="smtp_password">Password</label>
                            <input type="password" name="smtp_password" id="smtp_password" class="form-control" maxlength="255"
                                   placeholder="<?= !empty($settings['smtp_password']) ? '•••••••• (leave blank to keep)' : '' ?>"
                                   value="" <?= $readonly ? 'readonly' : '' ?> autocomplete="new-password">
                            <div class="form-text">Leave blank to keep the current password.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="smtp_encryption">Encryption</label>
                            <?php $enc = (string) ($settings['smtp_encryption'] ?? 'tls'); ?>
                            <select name="smtp_encryption" id="smtp_encryption" class="form-select" <?= $readonly ? 'disabled' : '' ?>>
                                <option value="tls" <?= $enc === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= $enc === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                <option value="none" <?= $enc === '' || $enc === 'none' ? 'selected' : '' ?>>None</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="smtp_from_email">From Email</label>
                            <input type="email" name="smtp_from_email" id="smtp_from_email" class="form-control" maxlength="190"
                                   value="<?= $v('smtp_from_email') ?>" <?= $readonly ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="smtp_from_name">From Name</label>
                            <input type="text" name="smtp_from_name" id="smtp_from_name" class="form-control" maxlength="150"
                                   value="<?= $v('smtp_from_name') ?>" <?= $readonly ? 'readonly' : '' ?>>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics -->
            <div class="card shadow-sm settings-panel <?= $activeTab === 'analytics' ? '' : 'd-none' ?>" data-panel="analytics">
                <div class="card-header bg-white"><h3 class="h6 mb-0">Analytics Code</h3></div>
                <div class="card-body">
                    <label class="form-label" for="analytics_code">Tracking / Analytics snippet</label>
                    <textarea name="analytics_code" id="analytics_code" class="form-control font-monospace" rows="10"
                              placeholder="Paste Google Analytics, Tag Manager, or other tracking scripts"
                        <?= $readonly ? 'readonly' : '' ?>><?= $v('analytics_code') ?></textarea>
                    <div class="form-text">Rendered in the public site head/footer as provided.</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="card shadow-sm settings-panel <?= $activeTab === 'footer' ? '' : 'd-none' ?>" data-panel="footer">
                <div class="card-header bg-white"><h3 class="h6 mb-0">Footer</h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="footer_text">Footer Text</label>
                        <textarea name="footer_text" id="footer_text" class="form-control" rows="4" maxlength="5000"
                            <?= $readonly ? 'readonly' : '' ?>><?= $v('footer_text') ?></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="copyright">Copyright</label>
                        <input type="text" name="copyright" id="copyright" class="form-control" maxlength="500"
                               value="<?= $v('copyright') ?>" <?= $readonly ? 'readonly' : '' ?>>
                    </div>
                </div>
            </div>

            <?php if ($canManage): ?>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            <?php else: ?>
                <div class="alert alert-light border mt-3 mb-0">You have view-only access to settings.</div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
(() => {
    const tabInput = document.getElementById('settings-active-tab');
    const buttons = document.querySelectorAll('.settings-tab');
    const panels = document.querySelectorAll('.settings-panel');

    function activate(tab) {
        tabInput.value = tab;
        buttons.forEach((btn) => btn.classList.toggle('active', btn.dataset.tab === tab));
        panels.forEach((panel) => panel.classList.toggle('d-none', panel.dataset.panel !== tab));
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    }

    buttons.forEach((btn) => {
        btn.addEventListener('click', () => activate(btn.dataset.tab));
    });
})();
</script>

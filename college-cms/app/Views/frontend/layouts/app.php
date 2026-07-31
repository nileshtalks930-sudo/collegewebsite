<?php
/** @var string $content */
/** @var string $title */
/** @var array<string,?string> $settings */
/** @var list<array<string,mixed>> $menus */

$settings = $settings ?? [];
$siteName = (string) ($settings['website_name'] ?? 'College Website');
$logo = (string) ($settings['logo'] ?? '');
$email = (string) ($settings['email'] ?? '');
$phone = (string) ($settings['phone'] ?? '');
$address = (string) ($settings['address'] ?? '');
$copyright = (string) ($settings['copyright'] ?? ('© ' . date('Y') . ' ' . $siteName));
$footerText = (string) ($settings['footer_text'] ?? '');
$analytics = (string) ($settings['analytics_code'] ?? '');
$favicon = (string) ($settings['favicon'] ?? '');

$social = [
    'facebook' => (string) ($settings['social_facebook'] ?? ''),
    'twitter' => (string) ($settings['social_twitter'] ?? ''),
    'instagram' => (string) ($settings['social_instagram'] ?? ''),
    'youtube' => (string) ($settings['social_youtube'] ?? ''),
    'linkedin' => (string) ($settings['social_linkedin'] ?? ''),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? $siteName) ?></title>
    <?php if ($favicon !== ''): ?>
        <link rel="icon" href="<?= e(upload_url($favicon)) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-serif-4@5.0.18/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= e(public_url('assets/css/site.css')) ?>">
    <?= $analytics ?>
</head>
<body>
<header class="site-header">
    <div class="container py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <a class="brand d-flex align-items-center gap-3 text-decoration-none" href="<?= e(public_url('/')) ?>">
            <?php if ($logo !== ''): ?>
                <img src="<?= e(upload_url($logo)) ?>" alt="<?= e($siteName) ?>" class="brand-logo">
            <?php endif; ?>
            <span class="brand-name"><?= e($siteName) ?></span>
        </a>
        <nav class="site-nav d-none d-lg-flex align-items-center gap-3">
            <?php if ($menus === []): ?>
                <a href="<?= e(public_url('/')) ?>">Home</a>
                <a href="#departments">Departments</a>
                <a href="#pages">Pages</a>
                <a href="#contact">Contact</a>
            <?php else: ?>
                <?php foreach ($menus as $item): ?>
                    <?php if ((int) ($item['status'] ?? 1) !== 1) continue; ?>
                    <a href="<?= e(\App\Models\Menu::resolveHref($item)) ?>"
                       <?= (int) ($item['open_in_new_tab'] ?? 0) === 1 ? 'target="_blank" rel="noopener"' : '' ?>>
                        <?= e((string) $item['name']) ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </nav>
        <a class="btn btn-accent btn-sm" href="<?= e(rtrim((string) (app_config('url') ?: ''), '/') ?: '../admin') ?>/login">Admin Login</a>
    </div>
</header>

<main>
    <?= $content ?>
</main>

<footer class="site-footer" id="contact">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-5">
                <h2 class="h5 text-white mb-3"><?= e($siteName) ?></h2>
                <?php if ($footerText !== ''): ?>
                    <p class="mb-2 opacity-75"><?= nl2br(e($footerText)) ?></p>
                <?php else: ?>
                    <p class="mb-2 opacity-75">Official college website managed with College CMS.</p>
                <?php endif; ?>
                <?php if ($address !== ''): ?>
                    <p class="mb-1 opacity-75"><i class="bi bi-geo-alt me-1"></i><?= nl2br(e($address)) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <h2 class="h6 text-white mb-3">Contact</h2>
                <?php if ($email !== ''): ?>
                    <p class="mb-1"><a class="link-light link-underline-opacity-0" href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p>
                <?php endif; ?>
                <?php if ($phone !== ''): ?>
                    <p class="mb-1 opacity-75"><i class="bi bi-telephone me-1"></i><?= e($phone) ?></p>
                <?php endif; ?>
                <?php if ($email === '' && $phone === ''): ?>
                    <p class="opacity-75 mb-0">Update contact details in Admin → Settings.</p>
                <?php endif; ?>
            </div>
            <div class="col-md-3">
                <h2 class="h6 text-white mb-3">Follow</h2>
                <div class="d-flex gap-3 fs-5">
                    <?php foreach ($social as $network => $url): ?>
                        <?php if ($url === '') continue; ?>
                        <a class="link-light" href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e($network) ?>">
                            <i class="bi bi-<?= e($network === 'twitter' ? 'twitter-x' : $network) ?>"></i>
                        </a>
                    <?php endforeach; ?>
                    <?php if (implode('', $social) === ''): ?>
                        <span class="opacity-50 small">Add social links in Settings</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <hr class="border-light border-opacity-25 my-4">
        <p class="mb-0 small opacity-75"><?= e($copyright) ?></p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
/** @var array<string,?string> $settings */
/** @var list<array<string,mixed>> $departments */
/** @var list<array<string,mixed>> $pages */

$siteName = (string) ($settings['website_name'] ?? 'College Website');
?>
<section class="hero">
    <div class="hero-media" aria-hidden="true"></div>
    <div class="container hero-content">
        <p class="hero-eyebrow">Welcome</p>
        <h1 class="hero-title"><?= e($siteName) ?></h1>
        <p class="hero-lead">Excellence in education, research, and community service.</p>
        <div class="hero-actions">
            <a class="btn btn-accent btn-lg" href="#departments">Explore Departments</a>
            <a class="btn btn-outline-light btn-lg" href="#pages">View Pages</a>
        </div>
    </div>
</section>

<section class="section" id="departments">
    <div class="container">
        <div class="section-head">
            <h2>Departments</h2>
            <p>Academic departments and programmes of the college.</p>
        </div>
        <?php if ($departments === []): ?>
            <div class="empty-state">No departments published yet. Add them from the admin panel.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($departments as $dept): ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="dept-block">
                            <h3><?= e((string) $dept['name']) ?></h3>
                            <?php if (!empty($dept['head_name'])): ?>
                                <p class="meta">Head: <?= e((string) $dept['head_name']) ?></p>
                            <?php endif; ?>
                            <p><?= e(substr(strip_tags((string) ($dept['description'] ?? '')), 0, 140)) ?><?= strlen(strip_tags((string) ($dept['description'] ?? ''))) > 140 ? '…' : '' ?></p>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section-alt" id="pages">
    <div class="container">
        <div class="section-head">
            <h2>Information</h2>
            <p>Important pages and resources.</p>
        </div>
        <?php if ($pages === []): ?>
            <div class="empty-state">No published pages yet.</div>
        <?php else: ?>
            <ul class="page-list">
                <?php foreach ($pages as $page): ?>
                    <li>
                        <a href="<?= e(public_url('page/' . ltrim((string) $page['slug'], '/'))) ?>">
                            <?= e((string) $page['title']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>

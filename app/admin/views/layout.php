<?php
/** @var string $title */
/** @var string $active */
/** @var callable $content */
/** @var array $flashes */
/** @var array|null $admin */
/** @var int $newOrders */
$nav = [
    ['', [
        ['dashboard', 'Boshqaruv paneli', 'dashboard'],
        ['orders', 'Arizalar', 'orders', $newOrders],
    ]],
    ['Kontent', [
        ['points', 'Reklama nuqtalari', 'pin'],
        ['texts', 'Sahifa matnlari', 'texts'],
        ['services', 'Xizmatlar', 'services'],
        ['stats', 'Statistika', 'stats'],
    ]],
    ['Portfolio', [
        ['portfolio', 'Loyihalar', 'portfolio'],
        ['categories', 'Kategoriyalar', 'tag'],
        ['clients', 'Mijozlar', 'clients'],
        ['testimonials', 'Fikrlar', 'quote'],
        ['cases', 'Keyslar', 'case'],
    ]],
    ['Kompaniya', [
        ['team', 'Jamoa', 'team'],
        ['districts', 'Tumanlar', 'map'],
    ]],
    ['Tizim', [
        ['media', 'Media kutubxona', 'media'],
        ['seo', 'SEO', 'seo'],
        ['settings', 'Sozlamalar', 'settings'],
        ['audit', 'Faollik jurnali', 'audit'],
    ]],
];
?><!DOCTYPE html>
<html lang="uz" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · Advertico Admin</title>
<link rel="icon" href="<?= e(url('assets/favicon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('admin/assets/admin.css')) ?>?v=1">
<script src="<?= e(url('admin/assets/theme.js')) ?>?v=1"></script>
</head>
<body class="admin" data-csrf="<?= e(csrf_token()) ?>" data-media-list="<?= e(admin_url('media', ['a' => 'json'])) ?>" data-media-upload="<?= e(admin_url('media', ['a' => 'upload'])) ?>">
<aside class="sidebar" id="sidebar">
    <a class="brand" href="<?= e(admin_url()) ?>">
        <img class="logo-on-dark" src="<?= e(url('assets/logo-dark.png')) ?>" alt="Advertico">
        <img class="logo-on-light" src="<?= e(url('assets/logo-light.png')) ?>" alt="Advertico">
        <span>Admin</span>
    </a>
    <nav class="side-nav" aria-label="Admin bo‘limlari">
        <?php foreach ($nav as [$group, $items]): ?>
            <?php if ($group !== ''): ?><div class="side-group"><?= e($group) ?></div><?php endif; ?>
            <?php foreach ($items as $item): ?>
                <a href="<?= e(admin_url($item[0])) ?>" class="<?= $active === $item[0] ? 'on' : '' ?>"<?= $active === $item[0] ? ' aria-current="page"' : '' ?>>
                    <?= icon($item[2]) ?><span><?= e($item[1]) ?></span>
                    <?php if (!empty($item[3])): ?><b class="count"><?= (int)$item[3] ?></b><?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
    <div class="side-foot">
        <a href="<?= e(url()) ?>" target="_blank" rel="noopener"><?= icon('external') ?><span>Saytni ochish</span></a>
    </div>
</aside>
<div class="backdrop" data-sidebar-close></div>
<div class="main">
    <header class="topbar">
        <button class="icon-btn menu-btn" type="button" data-sidebar-open aria-label="Menyu"><?= icon('menu') ?></button>
        <div class="topbar-title"><?= e($title) ?></div>
        <div class="topbar-actions">
            <button class="icon-btn" type="button" data-theme-toggle aria-label="Mavzuni almashtirish"><?= icon('theme') ?></button>
            <div class="user">
                <span class="avatar"><?= e(mb_strtoupper(mb_substr((string)($admin['name'] ?? 'A'), 0, 1))) ?></span>
                <span class="user-name"><?= e((string)($admin['name'] ?? '')) ?></span>
            </div>
            <form method="post" action="<?= e(admin_url('logout')) ?>">
                <?= csrf_field() ?>
                <button class="icon-btn" type="submit" title="Chiqish" aria-label="Chiqish"><?= icon('logout') ?></button>
            </form>
        </div>
    </header>
    <main class="content">
        <?php foreach ($flashes as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>
        <?php $content(); ?>
    </main>
</div>
<div class="modal" id="media-modal" hidden>
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="media-modal-title">
        <div class="modal-head">
            <h2 id="media-modal-title">Rasm tanlash</h2>
            <label class="btn btn-sm"><?= icon('upload') ?>Yangi rasm yuklash<input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-modal-upload hidden></label>
            <button class="icon-btn" type="button" data-modal-close aria-label="Yopish"><?= icon('x') ?></button>
        </div>
        <div class="modal-status muted" data-modal-status></div>
        <div class="media-grid picker" data-modal-grid></div>
    </div>
</div>
<script src="<?= e(url('admin/assets/admin.js')) ?>?v=1"></script>
</body>
</html>

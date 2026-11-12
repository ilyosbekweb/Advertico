<?php
/** @var string $title */
/** @var callable $content */
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
<body class="auth">
<div class="auth-glow" aria-hidden="true"></div>
<main class="auth-card">
    <a class="auth-brand" href="<?= e(url()) ?>">
        <img class="logo-on-dark" src="<?= e(url('assets/logo-dark.png')) ?>" alt="Advertico">
        <img class="logo-on-light" src="<?= e(url('assets/logo-light.png')) ?>" alt="Advertico">
    </a>
    <?php $content(); ?>
</main>
<script src="<?= e(url('admin/assets/admin.js')) ?>?v=1"></script>
</body>
</html>

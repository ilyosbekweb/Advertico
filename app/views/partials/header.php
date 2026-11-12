<?php
/** @var string $page  index|about|services|portfolio|locations|contact */
/** @var string $dataPage */
/** @var array $seo */
require_once __DIR__ . '/icons.php';
$isHome = $page === 'index';
$title = $seo['title_uz'] ?? 'Advertico';
$description = $seo['description_uz'] ?? '';
$ogImage = Content::mediaPath(isset($seo['og_image_id']) ? (int)$seo['og_image_id'] : null);
$canonical = absolute_url($isHome ? '' : $page . '.html');
$navServices = Content::services();
$phone = setting('phone', '+998 93 307 33 33');
?><!DOCTYPE html>
<html lang="uz" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<script>(function(){var d=document.documentElement;try{d.dataset.theme=localStorage.getItem('advertico-theme')==='light'?'light':'dark';if(localStorage.getItem('al')==='ru'){d.lang='ru';d.classList.add('i18n-pending');setTimeout(function(){d.classList.remove('i18n-pending');},2000);}}catch(e){}})();</script>
<link rel="icon" href="assets/favicon.png">
<title><?= e($title) ?></title>
<?php if ($description !== ''): ?><meta name="description" content="<?= e($description) ?>">
<?php endif; ?><?php if (!empty($seo['noindex'])): ?><meta name="robots" content="noindex, nofollow">
<?php endif; ?><meta name="theme-color" content="#070b09">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Advertico">
<meta property="og:title" content="<?= e($title) ?>">
<?php if ($description !== ''): ?><meta property="og:description" content="<?= e($description) ?>">
<?php endif; ?><meta property="og:url" content="<?= e($canonical) ?>">
<?php if ($ogImage !== ''): ?><meta property="og:image" content="<?= e(absolute_url($ogImage)) ?>">
<?php endif; ?><meta property="og:locale" content="uz_UZ">
<meta property="og:locale:alternate" content="ru_RU">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<?php if ($page === 'locations'): ?>
<link rel="stylesheet" href="assets/vendor/leaflet/leaflet.css">
<link rel="stylesheet" href="assets/vendor/markercluster/MarkerCluster.css">
<link rel="stylesheet" href="assets/css/ad-map.css?v=4">
<?php endif; ?>
<link rel="stylesheet" href="design.css?v=2">
</head>
<body data-page="<?= e($dataPage) ?>">
<?php if ($isHome): ?>
<div id="pre"><div id="pm"><div class="loader-mark"><svg viewBox="0 0 236 404" xmlns="http://www.w3.org/2000/svg"><path class="m1" d="M22 117L236 0v91L82 177z"/><path class="m2" d="M0 270l236-134v268l-73-41V262l-86 52z"/></svg></div><img class="loader-wordmark" src="assets/logo-dark.png" alt="Advertico"><span class="loader-caption"><?= tv('common.loader.caption') ?></span></div></div>
<?php else: ?>
<div id="pre"><div id="pm"><svg viewBox="0 0 236 404" xmlns="http://www.w3.org/2000/svg"><path class="m1" d="M22 117L236 0v91L82 177z"/><path class="m2" d="M0 270l236-134v268l-73-41V262l-86 52z"/></svg></div></div>
<?php endif; ?>
<div id="pb"></div>
<div class="wm" id="wm"><svg viewBox="0 0 236 404" xmlns="http://www.w3.org/2000/svg"><path class="m1" d="M22 117L236 0v91L82 177z"/><path class="m2" d="M0 270l236-134v268l-73-41V262l-86 52z"/></svg></div>

<header class="x-header" id="site-header">
 <div class="x-header-inner">
  <a href="index.html" class="lg" aria-label="Advertico"><img class="lgd" src="assets/logo-dark.png" alt="Advertico" width="217" height="34"><img class="lgl" src="assets/logo-light.png" alt="Advertico" width="217" height="34"></a>
  <div class="lk" role="navigation" aria-label="Asosiy menyu">
   <a data-r="home" href="index.html"><?= T('common.nav.home') ?></a>
   <div class="dd"><button type="button" aria-haspopup="true"><?= T('common.nav.services') ?><?= x_icon('caret', 'x-ico x-caret') ?></button><div class="mega">
<?php foreach ($navServices as $service): ?>
    <a href="services.html#<?= e($service['slug']) ?>"><?= x_service_icon($service['slug']) ?><?= L($service, 'name') ?></a>
<?php endforeach; ?>
   </div></div>
   <a data-r="portfolio" href="portfolio.html"><?= T('common.nav.portfolio') ?></a>
   <a data-r="locations" href="locations.html"><?= T('common.nav.locations') ?></a>
   <a data-r="about" href="about.html"><?= T('common.nav.about') ?></a>
   <a data-r="contact" href="contact.html"><?= T('common.nav.contact') ?></a>
  </div>
  <div class="x-actions tl">
   <button class="pill" id="lang" type="button" aria-label="Change language">RU</button>
   <button class="pill theme-toggle" id="th" type="button" aria-label="Kunduzgi rejimga oʻtish">☀</button>
   <a class="x-btn x-btn-primary" href="contact.html"><?= T('common.nav.cta') ?><?= x_icon('arrow-up') ?></a>
   <button id="bg" type="button" aria-label="Menu" aria-controls="oc"><span></span><span></span><span></span></button>
  </div>
 </div>
</header>
<aside id="oc" class="x-drawer" aria-label="Mobil menyu"><button id="x" type="button" aria-label="Close">✕</button>
 <a href="index.html"><?= T('common.nav.home') ?></a>
 <a href="services.html"><?= T('common.nav.services') ?></a>
 <a href="portfolio.html"><?= T('common.nav.portfolio') ?></a>
 <a href="locations.html"><?= T('common.nav.locations') ?></a>
 <a href="about.html"><?= T('common.nav.about') ?></a>
 <a href="contact.html"><?= T('common.nav.contact') ?></a>
 <div class="x-drawer-foot">
  <a class="x-btn x-btn-primary" href="contact.html"><?= T('common.nav.cta') ?><?= x_icon('arrow-up') ?></a>
  <a class="x-drawer-phone" href="<?= e(tel_href($phone)) ?>"><?= e($phone) ?></a>
 </div>
</aside>

<main id="app">

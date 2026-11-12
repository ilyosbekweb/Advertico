<?php
$heroStats = Content::stats('home_hero');
$counters = Content::stats('home_counters');
$homeServices = array_values(array_filter(Content::services(), static fn($s) => (int)$s['show_on_home'] === 1));
$projects = Content::rows('SELECT i.*, c.slug AS category_slug, c.name_uz AS category_name_uz, c.name_ru AS category_name_ru
    FROM portfolio_items i LEFT JOIN portfolio_categories c ON c.id = i.category_id
    WHERE i.is_published = 1 ORDER BY i.sort_order, i.id LIMIT 9');
$marquee = Content::rows('SELECT name FROM clients WHERE show_in_marquee = 1 ORDER BY sort_order, id');
$phone = setting('phone', '+998 93 307 33 33');
$statIcons = ['calendar', 'billboard', 'briefcase', 'building'];

/** Shahar silueti: har safar bir xil chiqishi uchun qat'iy "tasodif". */
$rand = static function (int $i, int $min, int $max): int {
    return $min + (int)(abs(sin($i * 12.9898) * 43758.5453) * 1000) % ($max - $min + 1);
};
?>
<section class="x-hero" id="home" aria-labelledby="hero-title">
 <div class="x-scene x-in" style="--d:100" aria-hidden="true" data-scene>
  <div class="x-sky"></div>
  <div class="x-stars"></div>
  <svg class="x-city" viewBox="0 0 1200 600" preserveAspectRatio="xMidYMax slice">
   <defs>
    <pattern id="xw" width="16" height="20" patternUnits="userSpaceOnUse"><rect x="4" y="5" width="6" height="8" fill="rgba(255,214,160,.16)"/></pattern>
   </defs>
<?php
    // Uzoq qatlam
    for ($i = 0, $x = 0; $x < 1200; $i++) {
        $w = $rand($i, 40, 90); $h = $rand($i + 50, 140, 330);
        echo '<rect class="far" x="' . $x . '" y="' . (600 - $h) . '" width="' . $w . '" height="' . $h . '"/>';
        $x += $w + $rand($i + 7, 2, 10);
    }
    // Yaqin qatlam (derazalar bilan)
    $towers = [[60, 120, 430], [200, 90, 340], [300, 70, 470], [690, 80, 300], [960, 110, 520], [1080, 90, 380]];
    foreach ($towers as $t => [$tx, $tw, $th]) {
        $ty = 600 - $th;
        echo '<rect class="near" x="' . $tx . '" y="' . $ty . '" width="' . $tw . '" height="' . $th . '"/>';
        echo '<rect x="' . $tx . '" y="' . $ty . '" width="' . $tw . '" height="' . $th . '" fill="url(#xw)"/>';
        for ($k = 0; $k < 14; $k++) {
            $wx = $tx + 4 + 16 * $rand($t * 40 + $k, 0, (int)($tw / 16) - 1);
            $wy = $ty + 5 + 20 * $rand($t * 70 + $k, 0, (int)($th / 20) - 2);
            echo '<rect class="' . ($k % 5 === 0 ? 'win-g' : 'win') . '" x="' . $wx . '" y="' . $wy . '" width="6" height="8"/>';
        }
        echo '<rect class="beacon" x="' . ($tx + $tw / 2 - 2) . '" y="' . ($ty - 6) . '" width="4" height="4" rx="2"/>';
    }
?>
  </svg>
  <div class="x-ground"></div>
  <div class="x-stage" data-stage>
   <div class="x-building">
    <div class="x-face x-top"></div>
    <div class="x-face x-side"><img src="assets/favicon.png" alt=""></div>
    <div class="x-face x-front">
     <div class="x-screen"><canvas data-led></canvas><img src="assets/logo-dark.png" alt=""></div>
     <div class="x-store"><i></i><i></i><i></i><i></i><i class="door"></i><i></i><i></i><i></i><i></i></div>
    </div>
   </div>
  </div>
  <div class="x-beams"><i></i><i></i><i></i></div>
  <span class="x-arc"></span><span class="x-arc x-arc-2"></span>
  <div class="x-fade"></div>
 </div>

 <div class="x-scroll" aria-hidden="true"><span><?= tv('home.hero.scroll') ?></span><i class="x-mouse"></i></div>

 <div class="x-wrap x-hero-main">
  <div class="x-hero-copy">
   <p class="x-eyebrow x-in" style="--d:0"><i></i><?= T('home.hero.eyebrow') ?></p>
   <h1 id="hero-title">
    <?php if (t_has('home.hero.title_1')): ?><span class="x-in" style="--d:120"><?= T('home.hero.title_1') ?></span><?php endif; ?>
    <?php if (t_has('home.hero.title_2')): ?><span class="x-in" style="--d:220"><?= T('home.hero.title_2') ?></span><?php endif; ?>
    <?php if (t_has('home.hero.title_accent')): ?><span class="x-in x-accent" style="--d:320"<?= ta('home.hero.title_accent') ?>><?= tv('home.hero.title_accent') ?></span><?php endif; ?>
   </h1>
   <p class="x-lead x-in" style="--d:440"><?= T('home.hero.description') ?></p>
   <div class="x-hero-actions x-in" style="--d:540">
    <a class="x-btn x-btn-primary" href="contact.html"><?= T('home.hero.button_primary') ?><?= x_icon('arrow') ?></a>
    <a class="x-btn x-btn-ghost" href="portfolio.html"><span class="x-btn-play"><?= x_icon('play') ?></span><?= T('home.hero.button_secondary') ?></a>
   </div>
<?php if ($heroStats): ?>
   <div class="x-stats x-in" style="--d:660">
<?php foreach ($heroStats as $i => $stat): ?>
    <div class="x-stat"><?= x_icon($statIcons[$i % count($statIcons)]) ?><div><strong><?= (int)$stat['value'] ?><?= e($stat['suffix']) ?></strong><small><?= L($stat, 'label') ?></small></div></div>
<?php endforeach; ?>
   </div>
<?php endif; ?>
  </div>
 </div>

<?php if ($homeServices): ?>
 <div class="x-wrap x-services x-in" style="--d:780" id="xizmatlar">
  <div class="x-services-head">
   <h2><?= T('home.services.title') ?></h2>
   <div class="x-track-nav" data-track-nav="svc">
    <button class="x-round" type="button" data-prev aria-label="Oldingi"><?= x_icon('arrow-left') ?></button>
    <button class="x-round" type="button" data-next aria-label="Keyingi"><?= x_icon('arrow') ?></button>
   </div>
  </div>
  <div class="x-svc-track" data-track="svc">
<?php foreach ($homeServices as $i => $service): ?>
   <a class="x-svc<?= $i === 0 ? ' is-featured' : '' ?>" href="services.html#<?= e($service['slug']) ?>" data-service="<?= e($service['slug']) ?>">
    <span class="x-svc-icon"><?= x_service_icon($service['slug']) ?></span>
    <span><strong<?= la($service, 'name') ?>><?= lv($service, 'name') ?></strong><?php if (trim((string)$service['home_price_uz']) !== ''): ?><small<?= la($service, 'home_price') ?>><?= lv($service, 'home_price') ?></small><?php endif; ?></span>
   </a>
<?php endforeach; ?>
  </div>
 </div>
<?php endif; ?>
</section>

<?php if ($projects): ?>
<section class="x-section" id="loyihalar" aria-labelledby="projects-title">
 <div class="x-wrap">
  <div class="x-proj-head">
   <div class="rv">
    <span class="x-label"><?= T('home.portfolio.label') ?></span>
    <h2 class="x-title" id="projects-title"><?= T('home.portfolio.title') ?> <?= T('home.portfolio.accent', 'span', 'class="x-accent"') ?></h2>
   </div>
   <span class="x-divider" aria-hidden="true"></span>
   <p class="x-lead rv"><?= T('home.portfolio.text') ?></p>
   <div class="x-proj-nav" data-track-nav="proj">
    <button class="x-round" type="button" data-prev aria-label="Oldingi loyihalar"><?= x_icon('arrow-left') ?></button>
    <button class="x-round is-primary" type="button" data-next aria-label="Keyingi loyihalar"><?= x_icon('arrow') ?></button>
   </div>
  </div>
  <div class="x-proj-track" data-track="proj">
<?php foreach ($projects as $project):
    $image = Content::media($project['image_id'] !== null ? (int)$project['image_id'] : null); ?>
   <a class="x-proj rv" href="portfolio.html" style="--a:<?= e($project['accent_color']) ?>">
    <?php if ($image): ?>
     <span class="x-proj-media"><img src="<?= e($image['path']) ?>" alt="<?= e($image['alt_uz'] !== '' ? $image['alt_uz'] : $project['title_uz']) ?>" loading="lazy"></span>
    <?php else: ?>
     <span class="x-proj-media x-proj-art" aria-hidden="true"><b><?= e((string)($project['category_name_uz'] ?? 'Advertico')) ?></b></span>
    <?php endif; ?>
    <span class="x-proj-info">
     <span>
      <?php if (!empty($project['category_name_uz'])): ?><span class="x-chip" data-uz="<?= e($project['category_name_uz']) ?>" data-ru="<?= e($project['category_name_ru'] ?: $project['category_name_uz']) ?>"><?= e($project['category_name_uz']) ?></span><?php endif; ?>
      <strong<?= la($project, 'title') ?>><?= lv($project, 'title') ?></strong>
     </span>
     <span class="x-proj-go"><?= x_icon('arrow-up') ?></span>
    </span>
   </a>
<?php endforeach; ?>
  </div>
  <div class="x-proj-more"><a class="x-btn x-btn-ghost" href="portfolio.html"><?= T('home.portfolio.button') ?><?= x_icon('arrow') ?></a></div>
 </div>
</section>
<?php endif; ?>

<?php if ($counters): ?>
<section class="x-section" style="padding-top:0" aria-label="Advertico raqamlarda">
 <div class="x-wrap x-band">
<?php foreach ($counters as $stat): ?>
  <div class="x-band-item rv"><div class="big"><b class="ct" data-v="<?= (int)$stat['value'] ?>">0</b><em><?= e($stat['suffix']) ?></em></div><p><?= L($stat, 'label') ?></p></div>
<?php endforeach; ?>
 </div>
</section>
<?php endif; ?>

<?php if ($marquee): ?>
<div class="x-marquee" aria-label="Mijozlarimiz"><div class="x-marquee-track"><?php for ($loop = 0; $loop < 2; $loop++) { foreach ($marquee as $client) { echo '<span' . ($loop ? ' aria-hidden="true"' : '') . '>' . e($client['name']) . '</span>'; } } ?></div></div>
<?php endif; ?>

<section class="x-section" id="aloqa" aria-labelledby="cta-title">
 <div class="x-wrap">
  <div class="x-cta rv">
   <div>
    <h2 class="x-title" id="cta-title"><?= T('home.cta.title') ?></h2>
    <p class="x-lead"><?= T('home.cta.text') ?></p>
   </div>
   <div class="x-cta-actions">
    <a class="x-btn x-btn-primary" href="contact.html"><?= T('home.cta.button') ?><?= x_icon('arrow') ?></a>
    <a class="x-cta-phone" href="<?= e(tel_href($phone)) ?>"><?= e($phone) ?></a>
   </div>
  </div>
 </div>
</section>

<div class="x-index" role="navigation" aria-label="Bo‘limlar">
 <a href="#home" class="on">01</a><a href="#xizmatlar">02</a><?php if ($projects): ?><a href="#loyihalar">03</a><?php endif; ?><a href="#aloqa"><?= $projects ? '04' : '03' ?></a>
</div>

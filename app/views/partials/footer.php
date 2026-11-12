<?php
/** @var bool $withProjectModal */
/** @var bool $withServiceModal */
/** @var bool $withForm */
/** @var array $siteData */
$copyright = Content::text('common.footer.copyright');
$footerPhone = setting('phone', '+998 93 307 33 33');
$footerInstagram = setting('instagram_url', 'https://www.instagram.com/advertico.uz/');
$footerHandle = setting('instagram_handle', '@advertico.uz');
$footerMap = setting('map_url', 'https://maps.app.goo.gl/nkShhnYp1gjqPGTU6');
?>
</main>
<?php if ($withProjectModal): ?>
<div id="md"><div class="gl c" style="max-width:520px;background:var(--bg)"><h2><span id="project-title"><?= tv('common.project_modal.title') ?></span></h2><p class="mut"><span id="project-desc"<?= ta('common.project_modal.text') ?> data-default-uz="<?= e(Content::text('common.project_modal.text')['uz']) ?>" data-default-ru="<?= e(Content::text('common.project_modal.text')['ru']) ?>"><?= tv('common.project_modal.text') ?></span></p><br><a class="btn" href="contact.html" id="mc"><?= T('common.project_modal.button') ?></a></div></div>
<?php endif; ?>
<?php if ($withServiceModal): ?>
<div id="service-modal" aria-hidden="true"><div class="service-modal-content gl" role="dialog" aria-modal="true" aria-labelledby="service-modal-title"><button class="service-modal-close" type="button" aria-label="Yopish">✕</button><div class="service-modal-preview"><img id="service-catalog-image" alt=""></div><div class="service-modal-info"><p class="service-modal-kicker"><?= T('common.service_modal.kicker') ?></p><h2 id="service-modal-title"></h2><p class="mut" id="service-modal-description"></p><h3 class="service-price-heading"><?= T('common.service_modal.prices') ?></h3><dl id="service-modal-prices"></dl><a class="btn action-btn" href="contact.html"><?= T('common.service_modal.button') ?><span aria-hidden="true">↗</span></a></div></div></div>
<?php endif; ?>
<footer class="x-footer">
 <div class="x-wrap">
  <div class="x-footer-grid">
   <div>
    <a href="index.html" class="lg" aria-label="Advertico"><img class="lgd" src="assets/logo-dark.png" alt="Advertico" width="204" height="32" loading="lazy"><img class="lgl" src="assets/logo-light.png" alt="Advertico" width="204" height="32" loading="lazy"></a>
    <?php if (t_has('common.footer.about')): ?><p><?= T('common.footer.about') ?></p><?php endif; ?>
   </div>
   <div>
    <h2><?= T('common.footer.nav_title') ?></h2>
    <ul>
     <li><a href="index.html"><?= T('common.nav.home') ?></a></li>
     <li><a href="portfolio.html"><?= T('common.nav.portfolio') ?></a></li>
     <li><a href="locations.html"><?= T('common.nav.locations') ?></a></li>
     <li><a href="about.html"><?= T('common.nav.about') ?></a></li>
     <li><a href="contact.html"><?= T('common.nav.contact') ?></a></li>
    </ul>
   </div>
   <div>
    <h2><?= T('common.footer.services_title') ?></h2>
    <ul>
<?php foreach (array_slice(Content::services(), 0, 6) as $service): ?>
     <li><a href="services.html#<?= e($service['slug']) ?>"><?= L($service, 'name') ?></a></li>
<?php endforeach; ?>
    </ul>
   </div>
   <div class="x-footer-contact">
    <h2><?= T('common.footer.contact_title') ?></h2>
    <ul>
     <li><a href="<?= e(tel_href($footerPhone)) ?>"><?= x_icon('phone') ?><?= e($footerPhone) ?></a></li>
     <li><a href="<?= e($footerInstagram) ?>" target="_blank" rel="noopener noreferrer"><?= x_icon('instagram') ?><?= e($footerHandle) ?></a></li>
     <li><a href="<?= e($footerMap) ?>" target="_blank" rel="noopener noreferrer"><?= x_icon('pin') ?><?= T('contact.office_title') ?></a></li>
    </ul>
   </div>
  </div>
  <div class="x-footer-bottom">
   <span><?= $copyright['uz'] === $copyright['ru'] ? e((string)preg_replace('/[\s·]+$/u', '', $copyright['uz'])) : pair_html($copyright) ?></span>
   <?= T('common.footer.tagline') ?>
  </div>
 </div>
</footer>
<script type="application/json" id="site-data"><?= json_for_html($siteData) ?></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="script.js?v=2"></script>
<?php if ($withForm): ?>
<script src="backend.js"></script>
<?php endif; ?>
<?php if ($page === 'locations'): ?>
<script src="assets/vendor/leaflet/leaflet.js"></script>
<script src="assets/vendor/markercluster/leaflet.markercluster.js"></script>
<script src="assets/js/ad-map.js?v=3"></script>
<?php endif; ?>
</body>
</html>

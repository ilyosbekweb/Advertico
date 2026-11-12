<?php
$listDistricts = Content::districts('sort_order');
$mapUrl = setting('map_url', 'https://maps.app.goo.gl/nkShhnYp1gjqPGTU6');

// JS uchun ikki tilli matnlar va tumanlar (dinamik qismlar uchun).
$i18nKeys = [
    'region' => 'locations.panel_title', 'back' => 'locations.map.back', 'hint' => 'locations.map.hint',
    'all' => 'locations.map.filter_all', 'count' => 'locations.map.count', 'empty' => 'locations.map.empty',
    'noResults' => 'locations.map.no_results', 'type' => 'locations.detail.type', 'district' => 'locations.detail.district',
    'dimensions' => 'locations.detail.dimensions', 'availability' => 'locations.detail.availability',
    'sides' => 'locations.detail.sides', 'lighting' => 'locations.detail.lighting', 'yes' => 'locations.detail.yes',
    'no' => 'locations.detail.no', 'noPhotos' => 'locations.detail.no_photos', 'cta' => 'locations.detail.cta',
    'close' => 'locations.detail.close', 'districtDefault' => 'locations.district_default',
    'st_available' => 'locations.availability.available', 'st_reserved' => 'locations.availability.reserved',
    'st_occupied' => 'locations.availability.occupied', 'st_maintenance' => 'locations.availability.maintenance',
];
$i18n = [];
foreach ($i18nKeys as $name => $key) {
    $i18n[$name] = Content::text($key);
}
$districtData = [];
foreach ($listDistricts as $d) {
    $districtData[] = ['slug' => $d['slug'], 'number' => $d['number'], 'name' => Content::pair($d, 'name'), 'short' => Content::pair($d, 'short'), 'description' => Content::pair($d, 'description')];
}
$mapConfig = [
    'api'       => 'api/locations.php',
    'geojson'   => 'assets/geo/jizzakh-districts.geojson',
    'tiles'     => ['url' => setting('map_tile_url', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'), 'attribution' => setting('map_tile_attribution', '&copy; OpenStreetMap contributors')],
    'satellite' => setting('map_satellite_url') !== '' ? ['url' => setting('map_satellite_url'), 'attribution' => setting('map_satellite_attribution')] : null,
    'boundaryAttribution' => setting('map_boundary_attribution', '&copy; geoBoundaries / OCHA ROCCA (CC BY 3.0 IGO)'),
    'i18n'      => $i18n,
    'districts' => $districtData,
];
?>
<section id="locations" class="x-section adm-section"><div class="x-wrap">
 <div class="locations-heading">
  <div><p class="locations-eyebrow"><?= T('locations.eyebrow') ?></p><h1><?= T('locations.title') ?></h1><p class="mut locations-intro"><?= T('locations.intro') ?></p></div>
  <div class="locations-badge gl"><span class="locations-badge-mark">✳</span><span><b><?= tv('locations.badge_number') ?> <?= T('locations.badge_unit') ?></b><small><?= T('locations.badge_sub') ?></small></span></div>
 </div>

 <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <defs>
   <linearGradient id="adm-face" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#b8ff5d"/><stop offset=".45" stop-color="#00e879"/><stop offset="1" stop-color="#2f9a55"/></linearGradient>
   <linearGradient id="adm-area-fill" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#00e879" stop-opacity=".42"/><stop offset=".6" stop-color="#5CBC79" stop-opacity=".2"/><stop offset="1" stop-color="#b8ff5d" stop-opacity=".28"/></linearGradient>
   <symbol id="adm-mark" viewBox="0 0 236 404"><path d="M22 117L236 0v91L82 177z"/><path d="M0 270l236-134v268l-73-41V262l-86 52z"/></symbol>
  </defs>
 </svg>

 <div class="adm" data-ad-map>
  <div class="adm-map-card">
   <div class="adm-toolbar">
    <button type="button" class="adm-back" data-adm-back hidden><?= x_icon('arrow-left') ?><span class="adm-back-text"><?= T('locations.map.back') ?></span></button>
    <div class="adm-crumbs" role="navigation" aria-label="Breadcrumb">
     <button type="button" class="adm-crumb" data-adm-to-region><?= T('locations.panel_title') ?></button>
     <span class="adm-crumb-sep" data-adm-crumb-sep hidden aria-hidden="true">›</span>
     <span class="adm-crumb is-current" data-adm-crumb-district hidden aria-current="page"></span>
    </div>
   </div>
   <div class="adm-map" data-adm-map role="region" aria-label="<?= tv('locations.map_title') ?>"></div>
   <p class="adm-hint" data-adm-hint><?= T('locations.map.hint') ?></p>
   <div class="adm-loading" data-adm-loading><span></span></div>
   <article class="adm-detail" data-adm-detail hidden aria-live="polite"></article>
  </div>

  <aside class="adm-side">
   <div class="adm-side-head">
    <p class="locations-eyebrow"><?= T('locations.panel_eyebrow') ?></p>
    <h2 data-adm-title><?= T('locations.panel_title') ?></h2>
    <p class="mut adm-side-desc" data-adm-desc><?= T('locations.panel_hint') ?></p>
   </div>
   <div class="adm-filters" data-adm-filters>
    <label class="adm-search"><?= x_icon('pin', 'x-ico') ?><span class="x-sr"><?= T('locations.map.search') ?></span><input type="search" data-adm-search placeholder="<?= tv('locations.map.search') ?>" data-placeholder-uz="<?= e(Content::text('locations.map.search')['uz']) ?>" data-placeholder-ru="<?= e(Content::text('locations.map.search')['ru']) ?>" autocomplete="off"></label>
    <div class="adm-selects">
     <label><span><?= T('locations.map.filter_type') ?></span><select data-adm-type><option value=""<?= ta('locations.map.filter_all') ?>><?= tv('locations.map.filter_all') ?></option></select></label>
     <label><span><?= T('locations.map.filter_availability') ?></span><select data-adm-availability>
      <option value=""<?= ta('locations.map.filter_all') ?>><?= tv('locations.map.filter_all') ?></option>
      <?php foreach (AdLocations::AVAILABILITY as $status): ?><option value="<?= e($status) ?>"<?= ta('locations.availability.' . $status) ?>><?= tv('locations.availability.' . $status) ?></option><?php endforeach; ?>
     </select></label>
    </div>
   </div>
   <div class="adm-list" data-adm-list>
    <div class="district-list adm-districts" aria-label="Jizzax viloyati tumanlari">
<?php foreach ($listDistricts as $d): ?>
     <button type="button" data-district="<?= e($d['slug']) ?>"><span><?= e($d['number']) ?></span><?= L($d, 'short') ?><b data-adm-count="<?= e($d['slug']) ?>">↗</b></button>
<?php endforeach; ?>
    </div>
   </div>
   <a class="x-btn x-btn-primary adm-side-cta" href="contact.html"><?= T('locations.panel_button') ?><?= x_icon('arrow-up') ?></a>
  </aside>
 </div>

 <a class="office-location gl" href="<?= e($mapUrl) ?>" target="_blank" rel="noopener noreferrer"><span class="contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span><span class="contact-card-copy"><small><?= T('locations.office_label') ?></small><strong><?= T('locations.office_title') ?></strong><span class="contact-link-label"><?= T('locations.office_link') ?> <span aria-hidden="true">↗</span></span></span></a>
</div></section>
<script type="application/json" id="adm-config"><?= json_for_html($mapConfig) ?></script>

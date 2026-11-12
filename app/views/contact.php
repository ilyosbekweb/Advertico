<?php
$services = Content::services();
$phone = setting('phone', '+998 93 307 33 33');
$instagramUrl = setting('instagram_url', 'https://www.instagram.com/advertico.uz/');
$instagramHandle = setting('instagram_handle', '@advertico.uz');
$mapUrl = setting('map_url', 'https://maps.app.goo.gl/nkShhnYp1gjqPGTU6');
// Xaritadan kelgan reklama joyi (?location=ID) — faqat nashr qilingan bo'lsa ko'rsatiladi.
$selectedLocation = isset($_GET['location']) && ctype_digit((string)$_GET['location']) ? AdLocations::findPublished((int)$_GET['location']) : null;
$selectedServiceSlug = '';
if ($selectedLocation && $selectedLocation['service_slug']) {
    $selectedServiceSlug = (string)$selectedLocation['service_slug'];
}
?>
<section id="contact"><div class="w g" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr))">
  <form class="gl c" id="contact-form">
   <label aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden">Website<input name="website" tabindex="-1" autocomplete="off"></label><h2><?= T('contact.form_title') ?></h2>
<?php if ($selectedLocation): ?>
   <div class="x-selected-location"><span class="x-selected-pin" aria-hidden="true"><svg viewBox="0 0 236 404"><path d="M22 117L236 0v91L82 177z"/><path d="M0 270l236-134v268l-73-41V262l-86 52z"/></svg></span><span><small><?= T('contact.selected_location') ?></small><strong<?= la($selectedLocation, 'name') ?>><?= lv($selectedLocation, 'name') ?></strong><em<?= la($selectedLocation, 'district_name') ?>><?= lv($selectedLocation, 'district_name') ?></em></span><input type="hidden" name="location_id" value="<?= (int)$selectedLocation['id'] ?>"></div>
<?php endif; ?>
   <label><?= T('contact.label_name') ?><input id="fn" name="name" autocomplete="name" maxlength="120" required></label><label><?= T('contact.label_phone') ?><input id="fp" name="phone" type="tel" placeholder="<?= tv('contact.phone_placeholder') ?>" autocomplete="tel" maxlength="40" required></label>
   <label><?= T('contact.label_service') ?><select id="fs" name="service"><?php foreach ($services as $service): ?><option value="<?= e($service['slug']) ?>"<?= $service['slug'] === $selectedServiceSlug ? ' selected' : '' ?><?= la($service, 'name') ?>><?= lv($service, 'name') ?></option><?php endforeach; ?></select></label>
   <label><?= T('contact.label_message') ?><textarea id="fm" name="message" rows="3" maxlength="3000"></textarea></label>
   <div id="form-status" class="mut" role="status" aria-live="polite" style="margin-top:12px"></div>
   <br><button class="btn action-btn" id="sd" type="submit"><?= T('contact.submit') ?></button>
  </form>
  <div class="contact-details">
   <a class="contact-card gl" href="<?= e($mapUrl) ?>" target="_blank" rel="noopener noreferrer">
    <span class="contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span><span class="contact-card-copy"><small><?= T('contact.office_label') ?></small><strong><?= T('contact.office_title') ?></strong><span class="contact-link-label"><?= T('contact.office_link') ?> <span aria-hidden="true">↗</span></span></span>
   </a>
   <a class="contact-card gl" href="<?= e(tel_href($phone)) ?>">
    <span class="contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 3H4a1 1 0 0 0-1 1c0 9.4 7.6 17 17 17a1 1 0 0 0 1-1v-3l-5-2-2 3a14 14 0 0 1-9-9l3-2-1-4Z"/></svg></span><span class="contact-card-copy"><small><?= T('contact.phone_label') ?></small><strong><?= e($phone) ?></strong><span class="contact-link-label"><?= T('contact.phone_link') ?> <span aria-hidden="true">↗</span></span></span>
   </a>
   <a class="contact-card gl" href="<?= e($instagramUrl) ?>" target="_blank" rel="noopener noreferrer">
    <span class="contact-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle class="icon-dot" cx="17.5" cy="6.5" r=".8"/></svg></span><span class="contact-card-copy"><small><?= T('contact.instagram_label') ?></small><strong><?= e($instagramHandle) ?></strong><span class="contact-link-label"><?= T('contact.instagram_link') ?> <span aria-hidden="true">↗</span></span></span>
   </a>
  </div>
 </div></section>

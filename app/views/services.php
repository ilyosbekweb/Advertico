<?php $services = Content::services(); ?>
<section id="services"><div class="w">
 <h1><?= T('services.title') ?></h1>
 <ul class="service-list">
<?php foreach ($services as $service): ?>
  <li id="<?= e($service['slug']) ?>"><button type="button" class="service-card" data-service="<?= e($service['slug']) ?>"><span class="service-card-number"><?= e($service['number']) ?></span><?= L($service, 'name') ?><span class="service-card-price"<?= la($service, 'list_price') ?>><?= lv($service, 'list_price') ?></span></button></li>
<?php endforeach; ?>
 </ul>
</div></section>

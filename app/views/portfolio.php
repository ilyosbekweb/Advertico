<?php
$categories = Content::rows('SELECT * FROM portfolio_categories ORDER BY sort_order, id');
$items = Content::rows('SELECT i.*, c.slug AS category_slug FROM portfolio_items i LEFT JOIN portfolio_categories c ON c.id = i.category_id WHERE i.is_published = 1 ORDER BY i.sort_order, i.id');
$clients = Content::rows('SELECT * FROM clients WHERE show_in_grid = 1 ORDER BY sort_order, id');
$testimonials = Content::rows('SELECT * FROM testimonials WHERE is_published = 1 ORDER BY sort_order, id');
$cases = Content::rows('SELECT * FROM case_studies WHERE is_published = 1 ORDER BY sort_order, id');
?>
<section id="portfolio"><div class="w"><h1 style="font-size:clamp(2rem,5vw,3.6rem)"><?= T('portfolio.title') ?></h1><div class="tabs" id="ft"><button data-f="all" class="on"><?= T('portfolio.filter_all') ?></button><?php foreach ($categories as $category): ?><button data-f="<?= e($category['slug']) ?>"><?= L($category, 'name') ?></button><?php endforeach; ?></div><div class="g g3" id="pg">
  <?php foreach ($items as $item):
      $image = Content::media($item['image_id'] !== null ? (int)$item['image_id'] : null);
      $style = '--a:' . $item['accent_color'];
      if ($image) {
          $style .= ";background:linear-gradient(180deg,transparent 45%,rgba(0,0,0,.72)),url('" . str_replace(["'", '"', '\\', ')'], '', $image['path']) . "') center/cover";
      }
      $desc = Content::pair($item, 'description');
  ?><div class="tile" data-category="<?= e((string)$item['category_slug']) ?>" style="<?= e($style) ?>"<?php if ($desc['uz'] !== ''): ?> data-desc-uz="<?= e($desc['uz']) ?>" data-desc-ru="<?= e($desc['ru']) ?>"<?php endif; ?>><?= L($item, 'title') ?></div><?php endforeach; ?>

 </div></div></section>
<section id="clients" class="combined-clients"><div class="w"><h2><?= T('portfolio.clients_title') ?></h2>
  <div class="client-logo-grid">
<?php foreach ($clients as $client):
    $logo = Content::media($client['logo_id'] !== null ? (int)$client['logo_id'] : null); ?>
   <div class="client-logo-card gl rv"><?php
    if ($logo) {
        echo '<img src="' . e($logo['path']) . '" alt="' . e($client['name']) . '" loading="lazy" style="max-width:100%;max-height:88px;object-fit:contain">';
    } elseif (($svg = safe_svg((string)$client['logo_svg'])) !== '') {
        echo $svg;
    } else {
        echo '<strong>' . e($client['name']) . '</strong>';
    }
   ?></div>
<?php endforeach; ?>
  </div>
<?php if ($testimonials): ?>
  <h2><?= T('portfolio.testimonials_title') ?></h2><div class="gl c" id="tv">
<?php foreach ($testimonials as $index => $testimonial): $rating = max(1, min(5, (int)$testimonial['rating'])); ?>
   <div class="testimonial<?= $index === 0 ? ' active' : '' ?>"><div class="ac" style="font-size:1.3rem"><?= str_repeat('★', $rating) ?> <span class="pill"><?= $rating ?>.0</span></div><p style="font-size:1.2rem;margin:12px 0">“<?= L($testimonial, 'text') ?>”</p><b><?= e($testimonial['author']) ?></b><?php if ($testimonial['company'] !== ''): ?> <span class="mut">· <?= e($testimonial['company']) ?></span><?php endif; ?></div>
<?php endforeach; ?>
  </div><div style="display:flex;gap:10px;margin-top:14px"><button class="pill" id="pv" aria-label="Previous testimonial">←</button><button class="pill" id="nx" aria-label="Next testimonial">→</button></div>
<?php endif; ?>
<?php if ($cases): ?>
  <h2 style="margin-top:48px"><?= T('portfolio.cases_title') ?></h2><div class="g g3">
<?php foreach ($cases as $case): ?>
   <div class="c gl rv"><div class="n"><?= L($case, 'metric') ?></div><h3><?= e($case['client_name']) ?></h3><p class="mut"><?= L($case, 'text') ?></p></div>
<?php endforeach; ?>
  </div><?php endif; ?><?php if (t_has('portfolio.note')): ?><p class="note"><?= T('portfolio.note') ?></p><?php endif; ?>

 </div></section>

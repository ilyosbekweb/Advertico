<?php
$counters = Content::stats('about_counters');
$team = Content::rows('SELECT * FROM team_members WHERE is_active = 1 ORDER BY sort_order, id');
?>
<section id="about"><div class="w"><h1 style="font-size:clamp(2rem,5vw,3.6rem)"><?= T('about.title') ?></h1><div style="max-width:900px;margin:18px 0 36px">
<?php for ($i = 1; $i <= 5; $i++): if (t_has('about.story.p' . $i)): ?>
 <p class="mut rv"><?= T('about.story.p' . $i) ?></p>
<?php endif; endfor; ?>
 </div><div class="g g3">
<?php foreach (['mission', 'vision', 'values'] as $block): ?>
  <div class="c gl rv"><div class="n"><?= tv("about.$block.icon") ?></div><h3><?= T("about.$block.title") ?></h3><p class="mut"><?= T("about.$block.text") ?></p></div>
<?php endforeach; ?>
 </div></div></section>
 <section><div class="w g g4">
<?php foreach ($counters as $stat): ?>
  <div class="c gl rv"><div class="big"><b class="ct" data-v="<?= (int)$stat['value'] ?>">0</b><?= e($stat['suffix']) ?></div><p class="mut"><?= L($stat, 'label') ?></p></div>
<?php endforeach; ?>
 </div></section>
 <section><div class="w"><h2 class="rv"><?= T('about.team.title') ?></h2><div class="g g4">
<?php foreach ($team as $member):
    $photo = Content::media($member['photo_id'] !== null ? (int)$member['photo_id'] : null); ?>
<?php if ($photo): ?>
  <div class="c gl rv"><div class="tile team-photo" style="--a:<?= e($member['accent_color']) ?>"><img src="<?= e($photo['path']) ?>" alt="<?= e($photo['alt_uz'] !== '' ? $photo['alt_uz'] : $member['name']) ?>" loading="lazy"></div><h3 style="margin-top:12px"><?= L($member, 'role') ?></h3><p class="mut"><?= e($member['name']) ?></p></div>
<?php else: ?>
  <div class="c gl rv"><div class="tile" style="--a:<?= e($member['accent_color']) ?>;aspect-ratio:1;cursor:default;font-size:2rem;align-items:center;justify-content:center"><?= e($member['placeholder']) ?></div><h3 style="margin-top:12px"><?= L($member, 'role') ?></h3><p class="mut"><?= e($member['name']) ?></p></div>
<?php endif; ?>
<?php endforeach; ?>
 </div><?php if (t_has('about.team.note')): ?><p class="note"><?= T('about.team.note') ?></p><?php endif; ?></div></section>

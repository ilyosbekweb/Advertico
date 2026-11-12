<?php
declare(strict_types=1);

$pages = [
    'common'    => ['Umumiy (menyu, footer, oynalar)', ''],
    'home'      => ['Bosh sahifa', 'index.html'],
    'about'     => ['Biz haqimizda', 'about.html'],
    'services'  => ['Xizmatlar', 'services.html'],
    'portfolio' => ['Ishlar va mijozlar', 'portfolio.html'],
    'locations' => ['Lokatsiyalar', 'locations.html'],
    'contact'   => ['Aloqa', 'contact.html'],
];
$page = input('page', 'common');
if (!array_key_exists($page, $pages)) {
    $page = 'common';
}

$stmt = db()->prepare('SELECT * FROM texts WHERE page = :p ORDER BY sort_order, `key`');
$stmt->execute([':p' => $page]);
$texts = $stmt->fetchAll();

if (is_post()) {
    csrf_check();
    $posted = (array)($_POST['t'] ?? []);
    $update = db()->prepare('UPDATE texts SET uz = :uz, ru = :ru WHERE `key` = :k AND page = :p');
    $changed = 0;
    $errors = 0;
    db()->beginTransaction();
    foreach ($texts as $text) {
        if (!isset($posted[$text['key']]) || !is_array($posted[$text['key']])) {
            continue;
        }
        $uz = trim((string)($posted[$text['key']]['uz'] ?? ''));
        $ru = trim((string)($posted[$text['key']]['ru'] ?? ''));
        if (mb_strlen($uz) > 5000 || mb_strlen($ru) > 5000) {
            $errors++;
            continue;
        }
        if ($uz !== $text['uz'] || $ru !== $text['ru']) {
            $update->execute([':uz' => $uz, ':ru' => $ru, ':k' => $text['key'], ':p' => $page]);
            $changed++;
        }
    }
    db()->commit();
    if ($changed) {
        audit('update', 'texts', null, ['page' => $page, 'changed' => $changed]);
    }
    flash($errors ? 'error' : 'success', $errors ? "{$errors} ta matn juda uzun bo‘lgani uchun saqlanmadi." : ($changed ? "{$changed} ta matn saqlandi." : 'O‘zgarish yo‘q.'));
    redirect_to('texts', ['page' => $page]);
}

$sections = [];
foreach ($texts as $text) {
    $sections[$text['section'] !== '' ? $text['section'] : 'Boshqa'][] = $text;
}

render_admin('Sahifa matnlari', 'texts', static function () use ($pages, $page, $sections) { ?>
    <div class="page-head">
        <div>
            <h1>Sahifa matnlari</h1>
            <p class="muted">Saytdagi barcha statik matnlar. RU maydoni bo‘sh qoldirilsa, o‘zbekcha matn ko‘rsatiladi. Ro‘yxatlar (xizmatlar, portfolio, jamoa…) o‘z bo‘limlarida tahrirlanadi.</p>
        </div>
        <?php if ($pages[$page][1] !== ''): ?>
            <a class="btn btn-ghost" href="<?= e(url($pages[$page][1])) ?>" target="_blank" rel="noopener"><?= icon('external') ?>Sahifani ochish</a>
        <?php endif; ?>
    </div>
    <div class="tabs">
        <?php foreach ($pages as $key => [$label]): ?>
            <a class="tab<?= $key === $page ? ' on' : '' ?>" href="<?= e(admin_url('texts', ['page' => $key])) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </div>
    <form method="post" action="<?= e(admin_url('texts', ['page' => $page])) ?>" class="form" data-dirty-guard>
        <?= csrf_field() ?>
        <?php foreach ($sections as $section => $items): ?>
            <section class="card text-section">
                <h2><?= e($section) ?></h2>
                <?php foreach ($items as $text):
                    $key = $text['key'];
                    $isArea = $text['type'] === 'textarea'; ?>
                    <div class="field full">
                        <span class="label"><?= e($text['label']) ?></span>
                        <div class="i18n">
                            <?php foreach (['uz' => 'UZ', 'ru' => 'RU'] as $lang => $tag): ?>
                                <label class="i18n-row"><span class="lang"><?= $tag ?></span>
                                    <?php if ($isArea): ?>
                                        <textarea name="t[<?= e($key) ?>][<?= $lang ?>]" rows="3" maxlength="5000" data-autosize><?= e($text[$lang]) ?></textarea>
                                    <?php else: ?>
                                        <input type="text" name="t[<?= e($key) ?>][<?= $lang ?>]" value="<?= e($text[$lang]) ?>" maxlength="5000">
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
        <div class="sticky-actions">
            <span class="muted small" data-dirty-note hidden>Saqlanmagan o‘zgarishlar bor</span>
            <button class="btn" type="submit"><?= icon('check') ?>Saqlash</button>
        </div>
    </form>
<?php });

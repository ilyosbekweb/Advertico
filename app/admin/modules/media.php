<?php
declare(strict_types=1);

$action = input('a', 'list');

/* ---------------------------------------------------------------- JSON ro'yxat (tanlash oynasi uchun) */
if ($action === 'json') {
    $items = [];
    foreach (db()->query('SELECT id, path, original_name, width, height FROM media ORDER BY id DESC LIMIT 500') as $m) {
        $items[] = ['id' => (int)$m['id'], 'url' => media_url($m['path']), 'name' => $m['original_name'], 'width' => (int)$m['width'], 'height' => (int)$m['height']];
    }
    json_response(200, ['success' => true, 'items' => $items]);
}

/* ---------------------------------------------------------------- Yuklash (AJAX) */
if ($action === 'upload' && is_post()) {
    csrf_check();
    $files = $_FILES['file'] ?? null;
    if (!$files) {
        json_response(422, ['success' => false, 'message' => 'Fayl tanlanmagan yoki hajmi server chegarasidan katta.']);
    }
    // Bir yoki bir nechta fayl
    $list = [];
    if (is_array($files['name'])) {
        foreach ($files['name'] as $i => $n) {
            $list[] = ['name' => $n, 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
        }
    } else {
        $list[] = $files;
    }
    $saved = [];
    $errors = [];
    foreach (array_slice($list, 0, 20) as $file) {
        try {
            $saved[] = Uploader::store($file);
        } catch (Throwable $e) {
            $errors[] = basename((string)$file['name']) . ': ' . ($e instanceof RuntimeException ? $e->getMessage() : 'Server xatosi.');
            if (!$e instanceof RuntimeException) {
                error_log('Advertico upload error: ' . $e->getMessage());
            }
        }
    }
    json_response($saved ? 200 : 422, ['success' => (bool)$saved, 'items' => $saved, 'errors' => $errors, 'message' => implode("\n", $errors)]);
}

/* ---------------------------------------------------------------- Alt matnni saqlash / o'chirish */
if (is_post() && in_array($action, ['save', 'delete'], true)) {
    csrf_check();
    $media = media_by_id((int)input('id'));
    if (!$media) {
        flash('error', 'Fayl topilmadi.');
        redirect_to('media');
    }
    if ($action === 'save') {
        db()->prepare('UPDATE media SET alt_uz = :u, alt_ru = :r WHERE id = :id')
            ->execute([':u' => mb_substr(input('alt_uz'), 0, 255), ':r' => mb_substr(input('alt_ru'), 0, 255), ':id' => $media['id']]);
        audit('update', 'media', (int)$media['id']);
        flash('success', 'Saqlandi.');
        redirect_to('media', ['a' => 'edit', 'id' => $media['id']]);
    }
    $usages = Uploader::usages((int)$media['id']);
    if ($usages) {
        flash('error', 'Bu rasm ishlatilmoqda: ' . implode(', ', $usages) . '. Avval u yerdan olib tashlang.');
        redirect_to('media', ['a' => 'edit', 'id' => $media['id']]);
    }
    if ((int)$media['is_system'] === 1) {
        flash('error', 'Bu rasm sayt bilan birga keladi va o‘chirib bo‘lmaydi.');
        redirect_to('media', ['a' => 'edit', 'id' => $media['id']]);
    }
    db()->prepare('DELETE FROM media WHERE id = :id')->execute([':id' => $media['id']]);
    // Faqat uploads/ ichidagi fayl va uning variantlari diskdan o'chiriladi.
    Uploader::deleteFiles((string)$media['path']);
    audit('delete', 'media', (int)$media['id'], ['file' => $media['original_name']]);
    flash('success', 'Rasm o‘chirildi.');
    redirect_to('media');
}

/* ---------------------------------------------------------------- Tahrirlash */
if ($action === 'edit') {
    $media = media_by_id((int)input('id'));
    if (!$media) {
        flash('error', 'Fayl topilmadi.');
        redirect_to('media');
    }
    $usages = Uploader::usages((int)$media['id']);
    render_admin('Rasm — Media kutubxona', 'media', static function () use ($media, $usages) { ?>
        <div class="page-head">
            <div>
                <a class="back" href="<?= e(admin_url('media')) ?>">← Media kutubxona</a>
                <h1><?= e($media['original_name']) ?></h1>
            </div>
        </div>
        <div class="grid-2">
            <div class="card media-preview"><img src="<?= e(media_url($media['path'])) ?>" alt=""></div>
            <div class="stack">
                <section class="card">
                    <dl class="details">
                        <div><dt>O‘lcham</dt><dd><?= (int)$media['width'] ?> × <?= (int)$media['height'] ?> px</dd></div>
                        <div><dt>Hajm</dt><dd><?= e(format_bytes((int)$media['size'])) ?></dd></div>
                        <div><dt>Turi</dt><dd><?= e($media['mime']) ?></dd></div>
                        <div><dt>Yuklangan</dt><dd><?= e(format_date($media['created_at'])) ?></dd></div>
                        <div class="full"><dt>Manzil</dt><dd><code><?= e($media['path']) ?></code></dd></div>
                        <div class="full"><dt>Ishlatilgan joylar</dt><dd><?= $usages ? e(implode(', ', $usages)) : '<span class="muted">Hech qayerda</span>' ?></dd></div>
                    </dl>
                </section>
                <form method="post" action="<?= e(admin_url('media', ['a' => 'save', 'id' => $media['id']])) ?>" class="card form">
                    <?= csrf_field() ?>
                    <div class="field full"><span class="label">Alt matn (ko‘zi ojizlar va SEO uchun)</span>
                        <div class="i18n">
                            <label class="i18n-row"><span class="lang">UZ</span><input type="text" name="alt_uz" value="<?= e($media['alt_uz']) ?>" maxlength="255"></label>
                            <label class="i18n-row"><span class="lang">RU</span><input type="text" name="alt_ru" value="<?= e($media['alt_ru']) ?>" maxlength="255"></label>
                        </div>
                    </div>
                    <div class="form-actions"><button class="btn" type="submit"><?= icon('check') ?>Saqlash</button></div>
                </form>
                <?php if ((int)$media['is_system'] !== 1): ?>
                <form method="post" action="<?= e(admin_url('media', ['a' => 'delete', 'id' => $media['id']])) ?>" data-confirm="Rasm butunlay o‘chirilsinmi?">
                    <?= csrf_field() ?>
                    <button class="btn btn-danger" type="submit"<?= $usages ? ' disabled title="Rasm ishlatilmoqda"' : '' ?>><?= icon('trash') ?>O‘chirish</button>
                </form>
                <?php else: ?>
                    <p class="muted small">Bu rasm sayt bilan birga keladi (assets/), shuning uchun o‘chirilmaydi.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php });
    return;
}

/* ---------------------------------------------------------------- Ro'yxat */
$items = db()->query('SELECT * FROM media ORDER BY id DESC')->fetchAll();
$totalSize = array_sum(array_map(static fn($m) => (int)$m['size'], $items));

render_admin('Media kutubxona', 'media', static function () use ($items, $totalSize) { ?>
    <div class="page-head">
        <div>
            <h1>Media kutubxona</h1>
            <p class="muted"><?= count($items) ?> ta rasm · <?= e(format_bytes($totalSize)) ?>. JPG, PNG, WEBP yoki GIF, har biri 8 MB gacha.</p>
        </div>
    </div>
    <label class="dropzone card" data-dropzone>
        <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple hidden data-dropzone-input>
        <?= icon('upload') ?>
        <strong>Rasmlarni shu yerga tashlang yoki tanlash uchun bosing</strong>
        <span class="muted small" data-dropzone-status>Bir vaqtda 20 tagacha rasm yuklash mumkin.</span>
    </label>
    <?php if (!$items): ?>
        <div class="card empty">Hozircha rasmlar yo‘q.</div>
    <?php else: ?>
        <div class="media-grid">
            <?php foreach ($items as $m): ?>
                <a class="media-item" href="<?= e(admin_url('media', ['a' => 'edit', 'id' => $m['id']])) ?>">
                    <span class="media-thumb"><img src="<?= e(media_url($m['path'])) ?>" alt="<?= e($m['alt_uz']) ?>" loading="lazy"></span>
                    <span class="media-name"><?= e($m['original_name']) ?></span>
                    <span class="media-meta"><?= (int)$m['width'] ?>×<?= (int)$m['height'] ?> · <?= e(format_bytes((int)$m['size'])) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php });

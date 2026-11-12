<?php
declare(strict_types=1);

/*
 * "Reklama nuqtalari" — xaritadagi reklama joylari.
 * Qoida: nashr qilish uchun nom, tuman, koordinatalar va reklama turi majburiy.
 * Shartlar bajarilmasa yozuv qoralama bo'lib qoladi va public xaritada ko'rinmaydi.
 */

$action = input('a', 'list');
$availabilityLabels = ['available' => 'Bo‘sh', 'reserved' => 'Bron qilingan', 'occupied' => 'Band', 'maintenance' => 'Ta’mirda'];
$availabilityColors = ['available' => 'green', 'reserved' => 'amber', 'occupied' => 'red', 'maintenance' => 'gray'];
$districts = db()->query('SELECT id, slug, name_uz FROM districts ORDER BY sort_order, id')->fetchAll();
$districtById = [];
foreach ($districts as $d) {
    $districtById[(int)$d['id']] = $d;
}
$services = crud_options('SELECT id, name_uz FROM services ORDER BY sort_order, id');

function points_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM ad_locations WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function points_photo_ids(int $id): array
{
    $stmt = db()->prepare('SELECT media_id FROM ad_location_photos WHERE location_id = :id ORDER BY sort_order, media_id');
    $stmt->execute([':id' => $id]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

/** Kasr sonni o'qish ("40,1234" ham qabul qilinadi). null — bo'sh, false — noto'g'ri. */
function points_decimal(string $raw): float|false|null
{
    $raw = str_replace([',', ' '], ['.', ''], trim($raw));
    if ($raw === '') {
        return null;
    }
    return is_numeric($raw) ? (float)$raw : false;
}

function points_status_badge(string $status): string
{
    return $status === 'published' ? '<span class="badge badge-green">Nashr qilingan</span>' : '<span class="badge badge-gray">Qoralama</span>';
}

/* ================================================================ O'chirish */
if ($action === 'delete' && is_post()) {
    csrf_check();
    $point = points_find((int)input('id'));
    if ($point) {
        // Fotolar media kutubxonada qoladi (boshqa joyda ham ishlatilishi mumkin).
        db()->prepare('DELETE FROM ad_locations WHERE id = :id')->execute([':id' => $point['id']]);
        audit('delete', 'points', (int)$point['id'], ['name' => $point['name_uz']]);
        flash('success', '“' . $point['name_uz'] . '” o‘chirildi.');
    }
    redirect_to('points');
}

/* ================================================================ Tezkor nashr / nashrdan olish (ro'yxatdan) */
if ($action === 'toggle' && is_post()) {
    csrf_check();
    $point = points_find((int)input('id'));
    if ($point) {
        if ($point['status'] === 'published') {
            db()->prepare("UPDATE ad_locations SET status = 'draft' WHERE id = :id")->execute([':id' => $point['id']]);
            audit('unpublish', 'points', (int)$point['id']);
            flash('success', '“' . $point['name_uz'] . '” nashrdan olindi — xaritada ko‘rinmaydi.');
        } else {
            $problems = AdLocations::publishProblems($point);
            $slug = $districtById[(int)$point['district_id']]['slug'] ?? '';
            if (!$problems && $slug !== '' && Geo::districtContains($slug, (float)$point['lat'], (float)$point['lng']) === false) {
                $problems[] = 'Nuqta tanlangan tuman chegarasidan tashqarida — tahrirlash sahifasida tasdiqlang';
            }
            if ($problems) {
                flash('error', 'Nashr qilib bo‘lmaydi: ' . implode('; ', $problems) . '.');
            } else {
                db()->prepare("UPDATE ad_locations SET status = 'published', published_at = COALESCE(published_at, NOW()) WHERE id = :id")->execute([':id' => $point['id']]);
                audit('publish', 'points', (int)$point['id']);
                flash('success', '“' . $point['name_uz'] . '” nashr qilindi.');
            }
        }
    }
    redirect_to('points', array_filter(['district' => input('district'), 'status' => input('status_filter')]));
}

/* ================================================================ Yaratish / tahrirlash */
if ($action === 'new' || $action === 'edit') {
    $id = $action === 'edit' ? (int)input('id') : 0;
    $point = $id ? points_find($id) : null;
    if ($action === 'edit' && !$point) {
        flash('error', 'Reklama nuqtasi topilmadi.');
        redirect_to('points');
    }
    $row = $point ?? ['status' => 'draft', 'availability' => 'available', 'lighting' => 0, 'district_id' => input('district') !== '' ? (int)input('district') : null];
    $photoIds = $id ? points_photo_ids($id) : [];
    $errors = [];
    $geoWarning = null;

    if (is_post()) {
        csrf_check();
        $intent = input('intent', 'save');
        $data = [];
        foreach (['name', 'address'] as $field) {
            foreach (['uz', 'ru'] as $lang) {
                $value = trim((string)preg_replace('/\s+/u', ' ', (string)($_POST["{$field}_{$lang}"] ?? '')));
                $max = $field === 'name' ? 200 : 255;
                if (mb_strlen($value) > $max) {
                    $errors["{$field}_{$lang}"] = "Ko‘pi bilan {$max} ta belgi.";
                }
                $data["{$field}_{$lang}"] = $value;
            }
        }
        if ($data['name_uz'] === '') {
            $errors['name_uz'] = 'Joy nomini kiriting (qoralama uchun ham kerak).';
        }
        foreach (['uz', 'ru'] as $lang) {
            $value = trim((string)($_POST["description_{$lang}"] ?? ''));
            if (mb_strlen($value) > 3000) {
                $errors["description_{$lang}"] = 'Ko‘pi bilan 3000 ta belgi.';
            }
            $data["description_{$lang}"] = $value !== '' ? $value : null;
        }

        $districtId = (int)input('district_id');
        $data['district_id'] = isset($districtById[$districtId]) ? $districtId : null;
        $serviceId = input('service_id');
        $data['service_id'] = ($serviceId !== '' && isset($services[$serviceId])) ? (int)$serviceId : null;

        $lat = points_decimal((string)($_POST['lat'] ?? ''));
        $lng = points_decimal((string)($_POST['lng'] ?? ''));
        if ($lat === false || ($lat !== null && ($lat < -90 || $lat > 90))) {
            $errors['lat'] = 'Kenglik noto‘g‘ri (masalan: 40.115812).';
        }
        if ($lng === false || ($lng !== null && ($lng < -180 || $lng > 180))) {
            $errors['lng'] = 'Uzunlik noto‘g‘ri (masalan: 67.842175).';
        }
        if (!isset($errors['lat'], $errors['lng']) && ($lat === null) !== ($lng === null)) {
            $errors[$lat === null ? 'lat' : 'lng'] = 'Kenglik va uzunlik birga kiritilishi kerak.';
        }
        $data['lat'] = is_float($lat) ? round($lat, 6) : null;
        $data['lng'] = is_float($lng) ? round($lng, 6) : null;
        if ($data['lat'] !== null && $data['lng'] !== null && !isset($errors['lat']) && !Geo::inRegionBox($data['lat'], $data['lng'])) {
            $errors['lat'] = 'Koordinatalar Jizzax viloyati hududidan tashqarida. Kenglik va uzunlik joyini almashtirib yubormadingizmi?';
        }

        foreach (['width_m' => 'Eni', 'height_m' => 'Bo‘yi'] as $field => $label) {
            $value = points_decimal((string)($_POST[$field] ?? ''));
            if ($value === false || ($value !== null && ($value <= 0 || $value > 500))) {
                $errors[$field] = "{$label} 0 dan katta va 500 m dan kichik bo‘lishi kerak.";
            }
            $data[$field] = is_float($value) ? round($value, 2) : null;
        }
        $sides = input('sides');
        if ($sides !== '' && (!ctype_digit($sides) || (int)$sides < 1 || (int)$sides > 4)) {
            $errors['sides'] = '1 dan 4 gacha.';
        }
        $data['sides'] = $sides !== '' && ctype_digit($sides) ? (int)$sides : null;
        $data['lighting'] = !empty($_POST['lighting']) ? 1 : 0;
        $availability = input('availability', 'available');
        $data['availability'] = in_array($availability, AdLocations::AVAILABILITY, true) ? $availability : 'available';

        // Fotolar: faqat mavjud media ID'lari, tartib saqlanadi.
        $postedPhotos = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['photos'] ?? [])))));
        $postedPhotos = array_slice($postedPhotos, 0, 40);
        if ($postedPhotos) {
            $placeholders = implode(',', array_fill(0, count($postedPhotos), '?'));
            $stmt = db()->prepare("SELECT id FROM media WHERE id IN ({$placeholders})");
            $stmt->execute($postedPhotos);
            $existing = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
            $postedPhotos = array_values(array_filter($postedPhotos, static fn($pid) => in_array($pid, $existing, true)));
        }
        $cover = (int)input('cover');
        $data['cover_media_id'] = in_array($cover, $postedPhotos, true) ? $cover : ($postedPhotos[0] ?? null);
        $photoIds = $postedPhotos;

        // Holat
        $wantPublished = $intent === 'publish' || ($intent === 'save' && ($point['status'] ?? 'draft') === 'published');
        $status = 'draft';
        $publishProblems = [];
        if ($wantPublished && !$errors) {
            $publishProblems = AdLocations::publishProblems($data);
            if (!$publishProblems && $data['district_id']) {
                $slug = $districtById[$data['district_id']]['slug'];
                $inside = Geo::districtContains($slug, $data['lat'], $data['lng']);
                if ($inside === false && empty($_POST['force_geo'])) {
                    $detected = Geo::detectDistrict($data['lat'], $data['lng']);
                    $detectedName = '';
                    foreach ($districts as $d) {
                        if ($d['slug'] === $detected) {
                            $detectedName = $d['name_uz'];
                        }
                    }
                    $geoWarning = 'Nuqta “' . $districtById[$data['district_id']]['name_uz'] . '” chegarasidan tashqarida'
                        . ($detectedName !== '' ? ' (xaritada “' . $detectedName . '” hududiga tushadi)' : '')
                        . '. Chegara ma’lumotlari ~100–500 m xatoga ega bo‘lishi mumkin. Joy aniq to‘g‘ri bo‘lsa, quyidagi belgini qo‘yib qayta nashr qiling.';
                    $publishProblems[] = 'Tuman chegarasi tekshiruvi';
                }
            }
            $status = $publishProblems ? 'draft' : 'published';
        }

        if (!$errors) {
            $data['status'] = $status;
            $pdo = db();
            $pdo->beginTransaction();
            try {
                if ($id) {
                    $set = implode(', ', array_map(static fn($c) => "`{$c}` = :{$c}", array_keys($data)));
                    $pdo->prepare("UPDATE ad_locations SET {$set}" . ($status === 'published' ? ', published_at = COALESCE(published_at, NOW())' : '') . ' WHERE id = :__id')
                        ->execute(array_merge(crud_params($data), [':__id' => $id]));
                } else {
                    $columns = array_keys($data);
                    $pdo->prepare('INSERT INTO ad_locations (`' . implode('`, `', $columns) . '`' . ($status === 'published' ? ', published_at' : '') . ') VALUES (:' . implode(', :', $columns) . ($status === 'published' ? ', NOW()' : '') . ')')
                        ->execute(crud_params($data));
                    $id = (int)$pdo->lastInsertId();
                }
                $pdo->prepare('DELETE FROM ad_location_photos WHERE location_id = :id')->execute([':id' => $id]);
                $insertPhoto = $pdo->prepare('INSERT INTO ad_location_photos (location_id, media_id, sort_order) VALUES (:l, :m, :s)');
                foreach ($photoIds as $position => $mediaId) {
                    $insertPhoto->execute([':l' => $id, ':m' => $mediaId, ':s' => ($position + 1) * 10]);
                }
                // Fotosurat yozuvi o'zgarganda ham public kesh yangilanishi uchun updated_at ni yangilaymiz.
                $pdo->prepare('UPDATE ad_locations SET updated_at = NOW() WHERE id = :id')->execute([':id' => $id]);
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
            audit($point ? 'update' : 'create', 'points', $id, ['name' => $data['name_uz'], 'status' => $status]);

            if ($wantPublished && $publishProblems) {
                if ($geoWarning !== null) {
                    $_SESSION['points_geo_warning'] = $geoWarning;
                }
                flash('error', 'Qoralama sifatida saqlandi, lekin nashr qilinmadi: ' . implode('; ', $publishProblems) . '.');
            } elseif ($status === 'published') {
                flash('success', 'Saqlandi va nashr qilindi — joy xaritada ko‘rinadi.');
            } else {
                flash('success', $intent === 'unpublish' ? 'Nashrdan olindi — joy xaritada ko‘rinmaydi.' : 'Qoralama sifatida saqlandi.');
            }
            redirect_to('points', ['a' => 'edit', 'id' => $id]);
        }
        $row = array_merge($row, $data, ['status' => $point['status'] ?? 'draft']);
    }

    $geoWarning = $geoWarning ?? ($_SESSION['points_geo_warning'] ?? null);
    unset($_SESSION['points_geo_warning']);
    $photos = [];
    if ($photoIds) {
        $placeholders = implode(',', array_fill(0, count($photoIds), '?'));
        $stmt = db()->prepare("SELECT id, path, original_name, width, height FROM media WHERE id IN ({$placeholders})");
        $stmt->execute($photoIds);
        $byId = [];
        foreach ($stmt->fetchAll() as $m) {
            $byId[(int)$m['id']] = $m;
        }
        foreach ($photoIds as $pid) {
            if (isset($byId[$pid])) {
                $photos[] = $byId[$pid];
            }
        }
    }
    $title = $id ? (string)$row['name_uz'] : 'Yangi reklama nuqtasi';
    $problems = $id ? AdLocations::publishProblems($row) : [];

    render_admin($title . ' — Reklama nuqtalari', 'points', static function () use ($row, $id, $errors, $photos, $districts, $services, $availabilityLabels, $title, $problems, $geoWarning) {
        $err = static fn(string $k) => isset($errors[$k]) ? '<small class="error">' . e($errors[$k]) . '</small>' : '';
        $val = static fn(string $k) => e((string)($row[$k] ?? ''));
        $isPublished = ($row['status'] ?? 'draft') === 'published';
        ?>
        <link rel="stylesheet" href="<?= e(url('assets/vendor/leaflet/leaflet.css')) ?>">
        <div class="page-head">
            <div>
                <a class="back" href="<?= e(admin_url('points')) ?>">← Reklama nuqtalari</a>
                <h1><?= e($title) ?> <?= $id ? points_status_badge((string)$row['status']) : '' ?></h1>
                <?php if ($id && !$isPublished && $problems): ?><p class="muted">Nashr qilish uchun: <?= e(implode(', ', $problems)) ?>.</p><?php endif; ?>
            </div>
            <?php if ($id): ?><a class="btn btn-ghost" href="<?= e(admin_url('points', ['a' => 'preview', 'id' => $id])) ?>"><?= icon('eye') ?>Oldindan ko‘rish</a><?php endif; ?>
        </div>
        <?php if ($errors): ?><div class="alert alert-error">Xatolarni tuzating: <?= count($errors) ?> ta maydon.</div><?php endif; ?>
        <?php if ($geoWarning): ?><div class="alert alert-error"><?= e($geoWarning) ?></div><?php endif; ?>

        <form method="post" action="<?= e(admin_url('points', $id ? ['a' => 'edit', 'id' => $id] : ['a' => 'new'])) ?>" class="form points-form" novalidate data-points-form
              data-geojson="<?= e(url('assets/geo/jizzakh-districts.geojson')) ?>"
              data-tile-url="<?= e(setting('map_tile_url', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png')) ?>"
              data-tile-attribution="<?= e(setting('map_tile_attribution', '&copy; OpenStreetMap contributors')) ?>"
              data-satellite-url="<?= e(setting('map_satellite_url')) ?>"
              data-satellite-attribution="<?= e(setting('map_satellite_attribution')) ?>">
            <?= csrf_field() ?>
            <div class="grid-2 wide-left">
                <div class="stack">
                    <section class="card">
                        <h2>Asosiy ma’lumotlar</h2>
                        <div class="form-grid" style="margin-top:14px">
                            <div class="field full"><span class="label">Joy nomi <i>*</i></span>
                                <div class="i18n">
                                    <label class="i18n-row<?= isset($errors['name_uz']) ? ' has-error' : '' ?>"><span class="lang">UZ</span><input type="text" name="name_uz" value="<?= $val('name_uz') ?>" maxlength="200" required placeholder="Masalan: Billboard — Sharof Rashidov ko‘chasi"></label><?= $err('name_uz') ?>
                                    <label class="i18n-row"><span class="lang">RU</span><input type="text" name="name_ru" value="<?= $val('name_ru') ?>" maxlength="200"></label>
                                </div>
                            </div>
                            <div class="field full"><span class="label">Manzil / mo‘ljal</span>
                                <div class="i18n">
                                    <label class="i18n-row"><span class="lang">UZ</span><input type="text" name="address_uz" value="<?= $val('address_uz') ?>" maxlength="255"></label>
                                    <label class="i18n-row"><span class="lang">RU</span><input type="text" name="address_ru" value="<?= $val('address_ru') ?>" maxlength="255"></label>
                                </div>
                            </div>
                            <label class="field half<?= isset($errors['service_id']) ? ' has-error' : '' ?>"><span class="label">Reklama turi <i>*</i></span>
                                <select name="service_id"><option value="">— Tanlang —</option><?php foreach ($services as $sid => $sname): ?><option value="<?= e($sid) ?>"<?= (string)($row['service_id'] ?? '') === (string)$sid ? ' selected' : '' ?>><?= e($sname) ?></option><?php endforeach; ?></select>
                                <small class="help">Turlar “Xizmatlar” bo‘limidan olinadi.</small></label>
                            <label class="field half"><span class="label">Bandlik holati</span>
                                <select name="availability"><?php foreach ($availabilityLabels as $k => $l): ?><option value="<?= e($k) ?>"<?= ($row['availability'] ?? 'available') === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
                            <label class="field half<?= isset($errors['width_m']) ? ' has-error' : '' ?>"><span class="label">Eni (m)</span><input type="text" inputmode="decimal" name="width_m" value="<?= $val('width_m') !== '' ? e(rtrim(rtrim((string)$row['width_m'], '0'), '.')) : '' ?>" placeholder="6"><?= $err('width_m') ?></label>
                            <label class="field half<?= isset($errors['height_m']) ? ' has-error' : '' ?>"><span class="label">Bo‘yi (m)</span><input type="text" inputmode="decimal" name="height_m" value="<?= $val('height_m') !== '' ? e(rtrim(rtrim((string)$row['height_m'], '0'), '.')) : '' ?>" placeholder="3"><?= $err('height_m') ?></label>
                            <label class="field half<?= isset($errors['sides']) ? ' has-error' : '' ?>"><span class="label">Tomonlar soni</span><select name="sides"><option value="">—</option><?php for ($s = 1; $s <= 4; $s++): ?><option value="<?= $s ?>"<?= (string)($row['sides'] ?? '') === (string)$s ? ' selected' : '' ?>><?= $s ?></option><?php endfor; ?></select><?= $err('sides') ?></label>
                            <label class="field half switch-row" style="align-self:end"><input type="checkbox" class="switch" name="lighting" value="1"<?= (int)($row['lighting'] ?? 0) === 1 ? ' checked' : '' ?>><span>Yoritilgan</span></label>
                            <div class="field full"><span class="label">Tavsif</span>
                                <div class="i18n">
                                    <label class="i18n-row<?= isset($errors['description_uz']) ? ' has-error' : '' ?>"><span class="lang">UZ</span><textarea name="description_uz" rows="3" maxlength="3000"><?= $val('description_uz') ?></textarea></label>
                                    <label class="i18n-row"><span class="lang">RU</span><textarea name="description_ru" rows="3" maxlength="3000"><?= $val('description_ru') ?></textarea></label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="card">
                        <div class="card-head"><h2>Fotosuratlar</h2><span class="muted small" data-photo-count><?= count($photos) ?> ta</span></div>
                        <p class="muted small">Sudrab tartibni o‘zgartiring. ★ — muqova (xaritada birinchi ko‘rinadi). Qoralama uchun foto shart emas.</p>
                        <div class="photo-grid" data-photo-grid>
                            <?php foreach ($photos as $p): ?>
                                <div class="photo-item<?= (int)($row['cover_media_id'] ?? 0) === (int)$p['id'] ? ' is-cover' : '' ?>" draggable="true" data-id="<?= (int)$p['id'] ?>">
                                    <img src="<?= e(media_url(media_variant((string)$p['path'], 'thumb'))) ?>" alt="" loading="lazy">
                                    <input type="hidden" name="photos[]" value="<?= (int)$p['id'] ?>">
                                    <label class="photo-cover" title="Muqova qilish"><input type="radio" name="cover" value="<?= (int)$p['id'] ?>"<?= (int)($row['cover_media_id'] ?? 0) === (int)$p['id'] ? ' checked' : '' ?>><span>★</span></label>
                                    <button type="button" class="photo-remove" data-photo-remove title="Olib tashlash" aria-label="Olib tashlash">✕</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="photo-actions">
                            <label class="btn btn-sm"><?= icon('upload') ?>Fotosurat yuklash<input type="file" accept="image/jpeg,image/png,image/webp" multiple hidden data-photo-upload></label>
                            <button type="button" class="btn btn-ghost btn-sm" data-photo-library><?= icon('media') ?>Kutubxonadan tanlash</button>
                            <span class="muted small" data-photo-status></span>
                        </div>
                    </section>
                </div>

                <div class="stack">
                    <section class="card">
                        <h2>Joylashuv</h2>
                        <p class="muted small" style="margin-bottom:12px">Xaritani bosing yoki koordinatalarni qo‘lda kiriting. Tuman avtomatik aniqlanadi.</p>
                        <div class="point-map" data-point-map></div>
                        <div class="form-grid" style="margin-top:14px">
                            <label class="field half<?= isset($errors['lat']) ? ' has-error' : '' ?>"><span class="label">Kenglik (lat) <i>*</i></span><input type="text" inputmode="decimal" name="lat" value="<?= $val('lat') ?>" placeholder="40.115812" data-lat><?= $err('lat') ?></label>
                            <label class="field half<?= isset($errors['lng']) ? ' has-error' : '' ?>"><span class="label">Uzunlik (lng) <i>*</i></span><input type="text" inputmode="decimal" name="lng" value="<?= $val('lng') ?>" placeholder="67.842175" data-lng><?= $err('lng') ?></label>
                            <label class="field full"><span class="label">Tuman <i>*</i></span>
                                <select name="district_id" data-district-select><option value="">— Tanlang —</option><?php foreach ($districts as $d): ?><option value="<?= (int)$d['id'] ?>" data-slug="<?= e($d['slug']) ?>"<?= (int)($row['district_id'] ?? 0) === (int)$d['id'] ? ' selected' : '' ?>><?= e($d['name_uz']) ?></option><?php endforeach; ?></select>
                                <small class="help" data-district-hint></small></label>
                            <?php if ($geoWarning): ?>
                            <label class="field full switch-row"><input type="checkbox" class="switch" name="force_geo" value="1"><span>Joy to‘g‘ri — chegara tekshiruviga qaramay nashr qilish</span></label>
                            <?php endif; ?>
                        </div>
                    </section>
                    <section class="card">
                        <h2>Holat</h2>
                        <p class="muted small" style="margin:6px 0 14px"><?= $isPublished ? 'Joy xaritada ko‘rinmoqda.' : 'Qoralama — xaritada ko‘rinmaydi. Nashr uchun: nom, tuman, koordinatalar va reklama turi.' ?></p>
                        <div class="stack-sm">
                            <?php if ($isPublished): ?>
                                <button class="btn btn-block" type="submit" name="intent" value="save"><?= icon('check') ?>Saqlash</button>
                                <button class="btn btn-ghost btn-block" type="submit" name="intent" value="unpublish">Nashrdan olish</button>
                            <?php else: ?>
                                <button class="btn btn-block" type="submit" name="intent" value="publish"><?= icon('check') ?>Saqlash va nashr qilish</button>
                                <button class="btn btn-ghost btn-block" type="submit" name="intent" value="draft">Qoralama sifatida saqlash</button>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
        </form>
        <?php if ($id): ?>
        <form method="post" action="<?= e(admin_url('points', ['a' => 'delete', 'id' => $id])) ?>" data-confirm="“<?= e((string)$row['name_uz']) ?>” butunlay o‘chirilsinmi? Fotosuratlar media kutubxonada qoladi.">
            <?= csrf_field() ?>
            <button class="btn btn-danger" type="submit"><?= icon('trash') ?>Reklama nuqtasini o‘chirish</button>
        </form>
        <?php endif; ?>

        <div class="modal" data-photo-modal hidden>
            <div class="modal-box" role="dialog" aria-modal="true" aria-label="Kutubxonadan tanlash">
                <div class="modal-head"><h2>Kutubxonadan tanlash</h2><button class="btn btn-sm" type="button" data-photo-modal-done>Qo‘shish</button><button class="icon-btn" type="button" data-photo-modal-close aria-label="Yopish"><?= icon('x') ?></button></div>
                <div class="media-grid picker" data-photo-modal-grid></div>
            </div>
        </div>
        <script src="<?= e(url('assets/vendor/leaflet/leaflet.js')) ?>"></script>
        <script src="<?= e(url('admin/assets/points.js')) ?>?v=1"></script>
        <?php
    });
    return;
}

/* ================================================================ Oldindan ko'rish */
if ($action === 'preview') {
    $point = points_find((int)input('id'));
    if (!$point) {
        flash('error', 'Reklama nuqtasi topilmadi.');
        redirect_to('points');
    }
    $photos = [];
    foreach (AdLocations::photosFor([(int)$point['id']])[(int)$point['id']] ?? [] as $p) {
        $photos[] = $p;
    }
    $cover = (int)$point['cover_media_id'];
    usort($photos, static fn($a, $b) => ((int)$b['id'] === $cover) <=> ((int)$a['id'] === $cover));
    $district = $districtById[(int)$point['district_id']]['name_uz'] ?? '—';
    $districtSlug = $districtById[(int)$point['district_id']]['slug'] ?? '';
    $service = $services[(string)$point['service_id']] ?? '—';
    render_admin('Oldindan ko‘rish — ' . $point['name_uz'], 'points', static function () use ($point, $photos, $district, $districtSlug, $service, $availabilityLabels, $availabilityColors) { ?>
        <link rel="stylesheet" href="<?= e(url('assets/vendor/leaflet/leaflet.css')) ?>">
        <div class="page-head">
            <div>
                <a class="back" href="<?= e(admin_url('points', ['a' => 'edit', 'id' => $point['id']])) ?>">← Tahrirlash</a>
                <h1><?= e($point['name_uz']) ?> <?= points_status_badge((string)$point['status']) ?></h1>
                <p class="muted">Saytdagi joy paneli shu ma’lumotlarni ko‘rsatadi. <?= $point['status'] === 'published' ? '<a class="link" href="' . e(url('locations.html')) . '#' . e($districtSlug) . '/' . (int)$point['id'] . '" target="_blank" rel="noopener">Saytda ochish ↗</a>' : 'Qoralama saytda ko‘rinmaydi.' ?></p>
            </div>
        </div>
        <div class="grid-2">
            <section class="card">
                <?php if ($photos): ?>
                    <div class="preview-main"><img src="<?= e(media_url(media_variant((string)$photos[0]['path'], 'large'))) ?>" alt="" data-preview-main></div>
                    <div class="preview-thumbs"><?php foreach ($photos as $i => $p): ?><button type="button" data-preview-src="<?= e(media_url(media_variant((string)$p['path'], 'large'))) ?>"<?= $i === 0 ? ' class="on"' : '' ?>><img src="<?= e(media_url(media_variant((string)$p['path'], 'thumb'))) ?>" alt=""></button><?php endforeach; ?></div>
                <?php else: ?>
                    <div class="empty">Fotosuratlar yo‘q — saytda “fotosuratlar tez orada qo‘shiladi” yozuvi ko‘rinadi.</div>
                <?php endif; ?>
                <dl class="details" style="margin-top:18px">
                    <div><dt>Tuman</dt><dd><?= e($district) ?></dd></div>
                    <div><dt>Turi</dt><dd><?= e($service) ?></dd></div>
                    <div><dt>O‘lcham</dt><dd><?= $point['width_m'] !== null && $point['height_m'] !== null ? e(rtrim(rtrim((string)$point['width_m'], '0'), '.') . ' × ' . rtrim(rtrim((string)$point['height_m'], '0'), '.') . ' m') : '—' ?></dd></div>
                    <div><dt>Holati</dt><dd><span class="badge badge-<?= e($availabilityColors[$point['availability']] ?? 'gray') ?>"><?= e($availabilityLabels[$point['availability']] ?? $point['availability']) ?></span></dd></div>
                    <div class="full"><dt>Manzil</dt><dd><?= e($point['address_uz'] ?: '—') ?></dd></div>
                    <div class="full"><dt>Tavsif</dt><dd class="pre"><?= e((string)($point['description_uz'] ?? '') ?: '—') ?></dd></div>
                </dl>
            </section>
            <section class="card">
                <h2>Xaritada</h2>
                <?php if ($point['lat'] !== null): ?>
                    <div class="point-map" data-preview-map data-lat="<?= e((string)$point['lat']) ?>" data-lng="<?= e((string)$point['lng']) ?>"
                         data-tile-url="<?= e(setting('map_tile_url', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png')) ?>" data-tile-attribution="<?= e(setting('map_tile_attribution', '&copy; OpenStreetMap contributors')) ?>" style="margin-top:12px"></div>
                    <p class="muted small" style="margin-top:8px"><?= e((string)$point['lat']) ?>, <?= e((string)$point['lng']) ?></p>
                <?php else: ?>
                    <div class="empty">Koordinatalar kiritilmagan.</div>
                <?php endif; ?>
            </section>
        </div>
        <script src="<?= e(url('assets/vendor/leaflet/leaflet.js')) ?>"></script>
        <script src="<?= e(url('admin/assets/points.js')) ?>?v=1"></script>
    <?php });
    return;
}

/* ================================================================ Ro'yxat */
$where = [];
$params = [];
$filterDistrict = input('district');
if ($filterDistrict !== '' && ctype_digit($filterDistrict)) {
    $where[] = 'l.district_id = :d';
    $params[':d'] = (int)$filterDistrict;
}
$filterStatus = input('status');
if (in_array($filterStatus, ['draft', 'published'], true)) {
    $where[] = 'l.status = :s';
    $params[':s'] = $filterStatus;
}
if ($filterStatus === 'nocoords') {
    $where[] = '(l.lat IS NULL OR l.lng IS NULL)';
}
$q = input('q');
if ($q !== '') {
    $where[] = '(l.name_uz LIKE :q1 OR l.name_ru LIKE :q2 OR l.address_uz LIKE :q3)';
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $params += [':q1' => $like, ':q2' => $like, ':q3' => $like];
}
$stmt = db()->prepare('SELECT l.*, d.name_uz AS district_name, s.name_uz AS service_name, m.path AS cover_path,
        (SELECT COUNT(*) FROM ad_location_photos p WHERE p.location_id = l.id) AS photo_count
    FROM ad_locations l
    LEFT JOIN districts d ON d.id = l.district_id
    LEFT JOIN services s ON s.id = l.service_id
    LEFT JOIN media m ON m.id = l.cover_media_id ' . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . '
    ORDER BY l.updated_at DESC, l.id DESC');
$stmt->execute($params);
$rows = $stmt->fetchAll();
$counts = db()->query("SELECT COUNT(*) AS total, SUM(status = 'published') AS published, SUM(lat IS NULL OR lng IS NULL) AS nocoords FROM ad_locations")->fetch();

render_admin('Reklama nuqtalari', 'points', static function () use ($rows, $counts, $districts, $filterDistrict, $filterStatus, $q, $availabilityLabels, $availabilityColors) {
    $filters = array_filter(['district' => $filterDistrict, 'status_filter' => $filterStatus]);
    ?>
    <div class="page-head">
        <div>
            <h1>Reklama nuqtalari</h1>
            <p class="muted">Xaritadagi reklama joylari. Jami: <?= (int)$counts['total'] ?> · nashr qilingan: <?= (int)$counts['published'] ?> · koordinatasiz: <?= (int)$counts['nocoords'] ?>. Nashr qilingan joylar saytdagi xaritada avtomatik paydo bo‘ladi.</p>
        </div>
        <a class="btn" href="<?= e(admin_url('points', ['a' => 'new'] + ($filterDistrict !== '' ? ['district' => $filterDistrict] : []))) ?>"><?= icon('plus') ?>Yangi nuqta</a>
    </div>
    <form class="filters card" method="get" action="<?= e(url('admin/')) ?>" style="grid-template-columns:2fr 1.3fr 1.3fr auto">
        <input type="hidden" name="r" value="points">
        <label class="field"><span class="label">Qidiruv</span><input type="search" name="q" value="<?= e($q) ?>" placeholder="Nomi yoki manzili"></label>
        <label class="field"><span class="label">Tuman</span><select name="district"><option value="">Barchasi</option><?php foreach ($districts as $d): ?><option value="<?= (int)$d['id'] ?>"<?= $filterDistrict === (string)$d['id'] ? ' selected' : '' ?>><?= e($d['name_uz']) ?></option><?php endforeach; ?></select></label>
        <label class="field"><span class="label">Holat</span><select name="status"><option value="">Barchasi</option><option value="published"<?= $filterStatus === 'published' ? ' selected' : '' ?>>Nashr qilingan</option><option value="draft"<?= $filterStatus === 'draft' ? ' selected' : '' ?>>Qoralama</option><option value="nocoords"<?= $filterStatus === 'nocoords' ? ' selected' : '' ?>>Koordinatasiz</option></select></label>
        <div class="filter-actions"><button class="btn btn-sm" type="submit">Filtrlash</button><?php if ($q !== '' || $filterDistrict !== '' || $filterStatus !== ''): ?><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('points')) ?>">Tozalash</a><?php endif; ?></div>
    </form>
    <div class="card table-card">
        <?php if (!$rows): ?>
            <div class="empty">
                <p>Hali reklama nuqtalari qo‘shilmagan.</p>
                <p class="muted small" style="margin:8px 0 16px">Har bir joyni koordinatalari va fotosuratlari bilan qo‘shing — nashr qilingach saytdagi xaritada Advertico belgisi bilan ko‘rinadi.</p>
                <a class="btn" href="<?= e(admin_url('points', ['a' => 'new'])) ?>"><?= icon('plus') ?>Birinchi nuqtani qo‘shish</a>
            </div>
        <?php else: ?>
        <table class="table">
            <thead><tr><th class="col-thumb">Foto</th><th>Nomi</th><th>Tuman</th><th>Turi</th><th>Koordinata</th><th>Bandlik</th><th>Holat</th><th class="col-actions"><span class="sr">Amallar</span></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): $link = admin_url('points', ['a' => 'edit', 'id' => $row['id']]); ?>
                <tr data-href="<?= e($link) ?>">
                    <td class="col-thumb"><?php if ($row['cover_path']): ?><img src="<?= e(media_url(media_variant((string)$row['cover_path'], 'thumb'))) ?>" alt="" loading="lazy"><?php else: ?><span class="thumb-empty"></span><?php endif; ?></td>
                    <td class="strong"><a href="<?= e($link) ?>"><?= e($row['name_uz']) ?></a><div class="muted small"><?= (int)$row['photo_count'] ?> ta foto<?= $row['address_uz'] ? ' · ' . e(str_limit((string)$row['address_uz'], 50)) : '' ?></div></td>
                    <td><?= e($row['district_name'] ?? '—') ?></td>
                    <td><?= e($row['service_name'] ?? '—') ?></td>
                    <td class="small nowrap"><?= $row['lat'] !== null ? e(number_format((float)$row['lat'], 5) . ', ' . number_format((float)$row['lng'], 5)) : '<span class="badge badge-red">Yo‘q</span>' ?></td>
                    <td><span class="badge badge-<?= e($availabilityColors[$row['availability']] ?? 'gray') ?>"><?= e($availabilityLabels[$row['availability']] ?? $row['availability']) ?></span></td>
                    <td><?= points_status_badge((string)$row['status']) ?></td>
                    <td class="col-actions" style="width:150px">
                        <form method="post" action="<?= e(admin_url('points', ['a' => 'toggle', 'id' => $row['id']] + $filters)) ?>">
                            <?= csrf_field() ?>
                            <button class="icon-btn" type="submit" title="<?= $row['status'] === 'published' ? 'Nashrdan olish' : 'Nashr qilish' ?>"><?= icon($row['status'] === 'published' ? 'x' : 'check') ?></button>
                        </form>
                        <a class="icon-btn" href="<?= e(admin_url('points', ['a' => 'preview', 'id' => $row['id']])) ?>" title="Oldindan ko‘rish"><?= icon('eye') ?></a>
                        <a class="icon-btn" href="<?= e($link) ?>" title="Tahrirlash"><?= icon('edit') ?></a>
                        <form method="post" action="<?= e(admin_url('points', ['a' => 'delete', 'id' => $row['id']])) ?>" data-confirm="“<?= e($row['name_uz']) ?>” butunlay o‘chirilsinmi?">
                            <?= csrf_field() ?>
                            <button class="icon-btn danger" type="submit" title="O‘chirish"><?= icon('trash') ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
<?php });

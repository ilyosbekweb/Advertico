<?php
declare(strict_types=1);

$action = input('a');
$admin = Auth::user();

if (is_post()) {
    csrf_check();
    switch ($action) {
        case 'contacts':
            $errors = [];
            $phone = input('phone');
            if (!preg_match('/^[0-9+()\-\s]{7,40}$/', $phone)) {
                $errors[] = 'Telefon raqami noto‘g‘ri.';
            }
            foreach (['instagram_url' => 'Instagram havolasi', 'map_url' => 'Xarita havolasi'] as $key => $label) {
                $value = input($key);
                if ($value !== '' && (!filter_var($value, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $value))) {
                    $errors[] = "{$label} https:// bilan boshlanadigan to‘liq manzil bo‘lishi kerak.";
                }
            }
            if ($errors) {
                flash('error', implode(' ', $errors));
                redirect_to('settings');
            }
            set_setting('phone', $phone);
            set_setting('instagram_url', input('instagram_url'));
            set_setting('instagram_handle', mb_substr(input('instagram_handle'), 0, 80));
            set_setting('map_url', input('map_url'));
            audit('update', 'settings', null, ['section' => 'contacts']);
            flash('success', 'Kontaktlar saqlandi.');
            break;

        case 'telegram':
            $chatId = input('telegram_chat_id');
            if ($chatId !== '' && !preg_match('/^(-?\d{1,20}|@[A-Za-z0-9_]{5,64})$/', $chatId)) {
                flash('error', 'Chat ID raqam (masalan, -1001234567890) yoki @kanal_nomi bo‘lishi kerak.');
                redirect_to('settings');
            }
            set_setting('telegram_chat_id', $chatId);
            set_setting('telegram_enabled', !empty($_POST['telegram_enabled']) ? '1' : '0');
            $limit = (int)input('rate_limit_per_hour', '5');
            set_setting('rate_limit_per_hour', (string)max(1, min(100, $limit)));
            audit('update', 'settings', null, ['section' => 'telegram']);
            flash('success', 'Saqlandi.');
            break;

        case 'map':
            $values = [];
            foreach (['map_tile_url' => true, 'map_satellite_url' => false] as $key => $required) {
                $value = input($key);
                if ($value === '' && !$required) {
                    $values[$key] = '';
                    continue;
                }
                if (!preg_match('#^https://[a-z0-9.{}-]+/#i', $value) || !str_contains($value, '{z}') || !str_contains($value, '{x}') || !str_contains($value, '{y}')) {
                    flash('error', 'Tile manzili https:// bilan boshlanishi va {z}, {x}, {y} qismlarini o‘z ichiga olishi kerak.');
                    redirect_to('settings');
                }
                $values[$key] = mb_substr($value, 0, 500);
            }
            foreach ($values as $key => $value) {
                set_setting($key, $value);
            }
            // Atribusiya Leaflet'da HTML sifatida chiqadi — shuning uchun faqat oddiy matn saqlanadi (&copy; kabi belgilar ishlaydi).
            foreach (['map_tile_attribution', 'map_satellite_attribution'] as $key) {
                set_setting($key, mb_substr(trim(strip_tags(input($key))), 0, 300));
            }
            audit('update', 'settings', null, ['section' => 'map']);
            flash('success', 'Xarita sozlamalari saqlandi.');
            break;

        case 'telegram_test':
            $error = null;
            $ok = Telegram::send("✅ <b>Advertico</b>\nAdmin paneldan sinov xabari. Telegram to‘g‘ri sozlangan.", $error);
            flash($ok ? 'success' : 'error', $ok ? 'Sinov xabari yuborildi. Telegram guruhingizni tekshiring.' : 'Yuborilmadi: ' . $error);
            break;

        case 'account':
            $name = input('name');
            $email = mb_strtolower(input('email'));
            $current = (string)($_POST['current_password'] ?? '');
            $new = (string)($_POST['new_password'] ?? '');
            $stmt = db()->prepare('SELECT password_hash FROM admins WHERE id = :id');
            $stmt->execute([':id' => $admin['id']]);
            $hash = (string)$stmt->fetchColumn();
            if (!password_verify($current, $hash)) {
                flash('error', 'Joriy parol noto‘g‘ri. O‘zgarishlar saqlanmadi.');
                audit('password_fail', 'admin', (int)$admin['id']);
                redirect_to('settings');
            }
            if ($name === '' || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'Ism va emailni to‘g‘ri kiriting.');
                redirect_to('settings');
            }
            $params = [':n' => $name, ':e' => $email, ':id' => $admin['id']];
            $sql = 'UPDATE admins SET name = :n, email = :e';
            if ($new !== '') {
                if ($problem = Auth::passwordProblem($new)) {
                    flash('error', $problem);
                    redirect_to('settings');
                }
                if ($new !== (string)($_POST['new_password_confirm'] ?? '')) {
                    flash('error', 'Yangi parollar mos kelmadi.');
                    redirect_to('settings');
                }
                $sql .= ', password_hash = :h';
                $params[':h'] = Auth::hash($new);
            }
            db()->prepare($sql . ' WHERE id = :id')->execute($params);
            if ($new !== '') {
                session_regenerate_id(true);
            }
            audit($new !== '' ? 'password_change' : 'update', 'admin', (int)$admin['id']);
            flash('success', $new !== '' ? 'Hisob va parol yangilandi.' : 'Hisob ma’lumotlari yangilandi.');
            break;
    }
    redirect_to('settings');
}

$tokenSet = Telegram::token() !== '';

render_admin('Sozlamalar', 'settings', static function () use ($admin, $tokenSet) { ?>
    <div class="page-head"><div><h1>Sozlamalar</h1><p class="muted">Kontaktlar, Telegram xabarnomalari va hisobingiz.</p></div></div>
    <div class="grid-2">
        <form method="post" action="<?= e(admin_url('settings', ['a' => 'contacts'])) ?>" class="card form">
            <?= csrf_field() ?>
            <h2>Kontaktlar</h2>
            <p class="muted small">Saytdagi telefon, Instagram va ofis xaritasi havolalari.</p>
            <div class="form-grid">
                <label class="field full"><span class="label">Telefon</span><input type="tel" name="phone" value="<?= e(setting('phone')) ?>" maxlength="40" required></label>
                <label class="field full"><span class="label">Instagram havolasi</span><input type="url" name="instagram_url" value="<?= e(setting('instagram_url')) ?>" maxlength="255"></label>
                <label class="field full"><span class="label">Instagram nomi (ko‘rinadigan)</span><input type="text" name="instagram_handle" value="<?= e(setting('instagram_handle')) ?>" maxlength="80"></label>
                <label class="field full"><span class="label">Ofis xaritasi havolasi (Google Maps)</span><input type="url" name="map_url" value="<?= e(setting('map_url')) ?>" maxlength="500"></label>
            </div>
            <div class="form-actions"><button class="btn" type="submit"><?= icon('check') ?>Saqlash</button></div>
        </form>

        <div class="stack">
            <form method="post" action="<?= e(admin_url('settings', ['a' => 'telegram'])) ?>" class="card form">
                <?= csrf_field() ?>
                <h2>Telegram xabarnomalari</h2>
                <ul class="checks compact">
                    <li class="<?= $tokenSet ? 'ok' : 'warn' ?>"><span class="check-dot"></span><div><b>Bot token</b><small><?= $tokenSet ? 'app/config.php da o‘rnatilgan (xavfsizlik uchun bu yerda ko‘rsatilmaydi)' : 'O‘rnatilmagan — app/config.php → telegram.bot_token' ?></small></div></li>
                </ul>
                <div class="form-grid">
                    <label class="field full"><span class="label">Chat ID</span><input type="text" name="telegram_chat_id" value="<?= e(setting('telegram_chat_id')) ?>" placeholder="-1001234567890" maxlength="70" spellcheck="false"><small class="help">Guruh ID’si odatda “-100” bilan boshlanadi. Botni guruhga qo‘shing va @userinfobot yoki getUpdates orqali ID’ni oling.</small></label>
                    <label class="field full switch-row"><input type="checkbox" class="switch" name="telegram_enabled" value="1"<?= setting('telegram_enabled', '1') === '1' ? ' checked' : '' ?>><span>Yangi arizalarni Telegram’ga yuborish</span></label>
                    <label class="field half"><span class="label">Bir IP’dan soatiga arizalar chegarasi</span><input type="number" name="rate_limit_per_hour" value="<?= e(setting('rate_limit_per_hour', '5')) ?>" min="1" max="100"><small class="help">Spamdan himoya.</small></label>
                </div>
                <div class="form-actions"><button class="btn" type="submit"><?= icon('check') ?>Saqlash</button></div>
            </form>
            <form method="post" action="<?= e(admin_url('settings', ['a' => 'telegram_test'])) ?>" class="card form inline-card">
                <?= csrf_field() ?>
                <div><b>Sinov xabari</b><p class="muted small">Sozlamalar to‘g‘riligini tekshirish uchun.</p></div>
                <button class="btn btn-ghost" type="submit"<?= Telegram::isConfigured() ? '' : ' disabled' ?>><?= icon('send') ?>Yuborish</button>
            </form>
        </div>
    </div>

    <form method="post" action="<?= e(admin_url('settings', ['a' => 'map'])) ?>" class="card form">
        <?= csrf_field() ?>
        <h2>Reklama xaritasi</h2>
        <p class="muted small">Xarita foni (tile) provayderi. <b>OpenStreetMap standart tile’lari faqat sinov uchun</b> — production’da tijorat shartlari mos keladigan provayder (masalan, MapTiler yoki Stadia Maps) kalitini kiriting va uning atribusiyasini yozing.</p>
        <div class="form-grid">
            <label class="field full"><span class="label">Xarita tile manzili</span><input type="url" name="map_tile_url" value="<?= e(setting('map_tile_url')) ?>" maxlength="500" spellcheck="false" required><small class="help">Masalan: https://api.maptiler.com/maps/dataviz-dark/{z}/{x}/{y}.png?key=KALIT</small></label>
            <label class="field full"><span class="label">Atribusiya (manba yozuvi)</span><input type="text" name="map_tile_attribution" value="<?= e(setting('map_tile_attribution')) ?>" maxlength="300"></label>
            <label class="field full"><span class="label">Sun’iy yo‘ldosh tile manzili (ixtiyoriy)</span><input type="url" name="map_satellite_url" value="<?= e(setting('map_satellite_url')) ?>" maxlength="500" spellcheck="false"><small class="help">Kiritilsa, xaritada “Sun’iy yo‘ldosh” rejimi paydo bo‘ladi.</small></label>
            <label class="field full"><span class="label">Sun’iy yo‘ldosh atribusiyasi</span><input type="text" name="map_satellite_attribution" value="<?= e(setting('map_satellite_attribution')) ?>" maxlength="300"></label>
        </div>
        <div class="form-actions"><button class="btn" type="submit"><?= icon('check') ?>Saqlash</button></div>
    </form>

    <form method="post" action="<?= e(admin_url('settings', ['a' => 'account'])) ?>" class="card form" autocomplete="off">
        <?= csrf_field() ?>
        <h2>Hisob</h2>
        <p class="muted small">Har qanday o‘zgarish uchun joriy parol talab qilinadi. Parolni o‘zgartirmaslik uchun yangi parol maydonlarini bo‘sh qoldiring.</p>
        <div class="form-grid">
            <label class="field half"><span class="label">Ism</span><input type="text" name="name" value="<?= e((string)$admin['name']) ?>" maxlength="120" required></label>
            <label class="field half"><span class="label">Email (login)</span><input type="email" name="email" value="<?= e((string)$admin['email']) ?>" maxlength="190" autocomplete="username" required></label>
            <label class="field half"><span class="label">Yangi parol</span><input type="password" name="new_password" minlength="10" autocomplete="new-password"><small class="help">Kamida 10 belgi, harf va raqam.</small></label>
            <label class="field half"><span class="label">Yangi parolni takrorlang</span><input type="password" name="new_password_confirm" minlength="10" autocomplete="new-password"></label>
            <label class="field half"><span class="label">Joriy parol <i>*</i></span><input type="password" name="current_password" autocomplete="current-password" required></label>
        </div>
        <div class="form-actions"><button class="btn" type="submit"><?= icon('check') ?>Hisobni yangilash</button></div>
        <p class="muted small">Oxirgi kirish: <?= e(format_date($admin['last_login_at'])) ?> · IP: <?= e((string)($admin['last_login_ip'] ?? '—')) ?></p>
    </form>
<?php });

<?php
declare(strict_types=1);

/*
 * Birinchi (yagona) administratorni yaratish.
 * - faqat bazada hali admin bo'lmasa ishlaydi;
 * - app/config.php dagi setup_key talab qilinadi (begona odam serverga birinchi bo'lib kira olmasligi uchun).
 */

require dirname(__DIR__) . '/app/admin/bootstrap.php';

try {
    $exists = Auth::adminExists();
} catch (Throwable $e) {
    http_response_code(500);
    exit('Ma’lumotlar bazasiga ulanib bo‘lmadi yoki jadvallar yaratilmagan. database/schema.sql va database/seed.sql fayllarini import qiling.');
}
if ($exists) {
    redirect_to('login');
}

$setupKey = (string)config('setup_key', '');
$errors = [];
$values = ['name' => '', 'email' => ''];

if (is_post() && strlen($setupKey) >= 24) {
    csrf_check();
    $values = ['name' => input('name'), 'email' => mb_strtolower(input('email'))];
    $password = (string)($_POST['password'] ?? '');

    // Kalitni taxmin qilishga qarshi: login urinishlari jadvalidan foydalanamiz.
    if (Auth::lockedMinutes('setup') > 0) {
        $errors[] = 'Juda ko‘p noto‘g‘ri urinish. Birozdan keyin qayta urinib ko‘ring.';
    } elseif (!hash_equals($setupKey, (string)($_POST['setup_key'] ?? ''))) {
        db()->prepare('INSERT INTO login_attempts (email, ip, success) VALUES (:e, :ip, 0)')->execute([':e' => 'setup', ':ip' => client_ip()]);
        $errors[] = 'O‘rnatish kaliti noto‘g‘ri.';
    }
    if ($values['name'] === '' || mb_strlen($values['name']) > 120) {
        $errors[] = 'Ismni kiriting.';
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($values['email']) > 190) {
        $errors[] = 'To‘g‘ri email kiriting.';
    }
    if ($problem = Auth::passwordProblem($password)) {
        $errors[] = $problem;
    } elseif ($password !== (string)($_POST['password_confirm'] ?? '')) {
        $errors[] = 'Parollar mos kelmadi.';
    }

    if (!$errors) {
        // Poyga holatidan himoya: jadval bo'sh bo'lgandagina qo'shamiz.
        $stmt = db()->prepare('INSERT INTO admins (name, email, password_hash) SELECT :n, :e, :h FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM admins)');
        $stmt->execute([':n' => $values['name'], ':e' => $values['email'], ':h' => Auth::hash($password)]);
        if ($stmt->rowCount() === 1) {
            audit('setup', 'admin', (int)db()->lastInsertId());
            flash('success', 'Administrator yaratildi. Endi tizimga kiring.');
        }
        redirect_to('login');
    }
}

$title = 'O‘rnatish';
$content = static function () use ($errors, $values, $setupKey) { ?>
    <h1>Administratorni yaratish</h1>
    <?php if (strlen($setupKey) < 24): ?>
        <div class="alert alert-error">
            O‘rnatish yopiq. <code>app/config.php</code> faylida kamida 24 belgidan iborat <code>setup_key</code> qiymatini belgilang va sahifani yangilang.
        </div>
    <?php else: ?>
        <p class="muted">Bu sahifa faqat bir marta ishlaydi. Admin yaratilgach, avtomatik yopiladi.</p>
        <?php foreach ($errors as $error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endforeach; ?>
        <form method="post" class="form" autocomplete="off">
            <?= csrf_field() ?>
            <label class="field full"><span class="label">O‘rnatish kaliti</span><input type="password" name="setup_key" required><small class="help">app/config.php → setup_key</small></label>
            <label class="field full"><span class="label">Ism</span><input type="text" name="name" value="<?= e($values['name']) ?>" maxlength="120" required></label>
            <label class="field full"><span class="label">Email</span><input type="email" name="email" value="<?= e($values['email']) ?>" maxlength="190" autocomplete="username" required></label>
            <label class="field full"><span class="label">Parol</span><input type="password" name="password" minlength="10" autocomplete="new-password" required><small class="help">Kamida 10 belgi, harf va raqam bo‘lsin.</small></label>
            <label class="field full"><span class="label">Parolni takrorlang</span><input type="password" name="password_confirm" minlength="10" autocomplete="new-password" required></label>
            <button class="btn btn-block" type="submit">Yaratish</button>
        </form>
    <?php endif; ?>
<?php };
require APP_PATH . '/admin/views/auth.php';

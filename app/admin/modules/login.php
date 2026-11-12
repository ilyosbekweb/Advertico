<?php
declare(strict_types=1);

if (Auth::check()) {
    redirect_to('dashboard');
}

$error = '';
$email = '';
if (is_post()) {
    csrf_check();
    $email = mb_strtolower(input('email'));
    $password = (string)($_POST['password'] ?? '');
    $locked = Auth::lockedMinutes($email);
    if ($locked > 0) {
        $error = "Juda ko‘p noto‘g‘ri urinish. {$locked} daqiqadan keyin qayta urinib ko‘ring.";
    } elseif ($email === '' || $password === '') {
        $error = 'Email va parolni kiriting.';
    } elseif (Auth::attempt($email, $password)) {
        redirect_to('dashboard');
    } else {
        $error = 'Email yoki parol noto‘g‘ri.';
    }
}

$flashes = take_flashes();
if (isset($_GET['expired']) && !$flashes && $error === '') {
    $flashes[] = ['type' => 'info', 'message' => 'Sessiya tugadi. Qaytadan kiring.'];
}
$title = 'Kirish';
$content = static function () use ($error, $email, $flashes) { ?>
    <h1>Admin panelga kirish</h1>
    <p class="muted">Davom etish uchun hisobingizga kiring.</p>
    <?php foreach ($flashes as $flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endforeach; ?>
    <?php if ($error !== ''): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(admin_url('login')) ?>" class="form">
        <?= csrf_field() ?>
        <label class="field full"><span class="label">Email</span><input type="email" name="email" value="<?= e($email) ?>" autocomplete="username" required autofocus></label>
        <label class="field full"><span class="label">Parol</span><input type="password" name="password" autocomplete="current-password" required></label>
        <button class="btn btn-block" type="submit">Kirish</button>
    </form>
<?php };
require APP_PATH . '/admin/views/auth.php';

<?php
declare(strict_types=1);

/*
 * Admin panel front controller: /admin/?r=<bo'lim>&a=<amal>
 */

require dirname(__DIR__) . '/app/admin/bootstrap.php';

$route = preg_replace('/[^a-z_]/', '', (string)($_GET['r'] ?? 'dashboard')) ?: 'dashboard';

// Hali administrator yaratilmagan bo'lsa — o'rnatish sahifasiga.
if (!Auth::adminExists()) {
    header('Location: ' . url('admin/setup.php'));
    exit;
}

if ($route === 'login') {
    require APP_PATH . '/admin/modules/login.php';
    exit;
}

if (!Auth::check()) {
    if (is_ajax()) {
        json_response(401, ['success' => false, 'message' => 'Sessiya tugagan. Qaytadan kiring.']);
    }
    redirect_to('login');
}

if ($route === 'logout') {
    if (is_post()) {
        csrf_check();
        audit('logout', 'admin', (int)$_SESSION['admin_id']);
        Auth::logout();
    }
    redirect_to('login');
}

$modules = ['dashboard', 'orders', 'points', 'texts', 'media', 'settings', 'audit'];

if (in_array($route, $modules, true)) {
    require APP_PATH . '/admin/modules/' . $route . '.php';
} elseif (array_key_exists($route, crud_entities())) {
    crud_handle($route);
} else {
    http_response_code(404);
    render_admin('Topilmadi', '', static function () {
        echo '<div class="card empty"><h1>404</h1><p class="muted">Bunday bo‘lim mavjud emas.</p><a class="btn" href="' . e(admin_url()) . '">Boshqaruv paneliga qaytish</a></div>';
    });
}

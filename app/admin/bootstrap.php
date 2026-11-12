<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';
require __DIR__ . '/Auth.php';
require __DIR__ . '/Uploader.php';
require __DIR__ . '/crud.php';

const ADMIN_IDLE_TIMEOUT = 1800; // 30 daqiqa harakatsizlikdan keyin chiqarib yuboriladi

/* ---------- Xavfsizlik headerlari ---------- */
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store, no-cache, must-revalidate');
/** Xarita tile serverlari (Sozlamalar) — admin xaritasida rasmlar yuklanishi uchun CSP'ga qo'shiladi. */
function admin_tile_hosts(): string
{
    $hosts = [];
    try {
        foreach (['map_tile_url', 'map_satellite_url'] as $key) {
            $url = setting($key);
            $parts = parse_url(str_replace('{s}.', 'SUBDOMAIN.', $url));
            if (($parts['scheme'] ?? '') === 'https' && !empty($parts['host']) && preg_match('/^[a-z0-9.-]+$/i', $parts['host'])) {
                $hosts[] = 'https://' . str_replace('SUBDOMAIN.', '*.', $parts['host']);
            }
        }
    } catch (Throwable $e) {
        return '';
    }
    return $hosts ? ' ' . implode(' ', array_unique($hosts)) : '';
}
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:" . admin_tile_hosts() . "; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'; object-src 'none'");
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

/* ---------- Sessiya ---------- */
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
session_name('advertico_admin');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => url('admin/'),
    'secure'   => is_https(),
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = (string)($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($sent === '' || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        if (is_ajax()) {
            json_response(403,['success' => false, 'message' => 'Sessiya eskirgan. Sahifani yangilang.']);
        }
        exit('Xavfsizlik tokeni noto‘g‘ri yoki eskirgan. Orqaga qaytib, sahifani yangilang.');
    }
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function is_ajax(): bool
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch'
        || str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
}

/* ---------- Navigatsiya va flash ---------- */
function admin_url(string $route = 'dashboard', array $params = []): string
{
    $query = http_build_query(array_merge(['r' => $route], $params));
    return url('admin/') . '?' . $query;
}

function redirect_to(string $route = 'dashboard', array $params = []): never
{
    header('Location: ' . admin_url($route, $params));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/** Admin sahifalarda media faylning URL manzili. */
function media_url(?string $path): string
{
    return $path ? url($path) : '';
}

function media_by_id(?int $id): ?array
{
    if (!$id) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM media WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function input(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

function format_date(?string $date, bool $withTime = true): string
{
    if (!$date) {
        return '—';
    }
    $ts = strtotime($date);
    return $ts ? date($withTime ? 'd.m.Y H:i' : 'd.m.Y', $ts) : '—';
}

function format_bytes(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1, ',', ' ') . ' MB';
    }
    return number_format(max(1, $bytes / 1024), 0, ',', ' ') . ' KB';
}

/** Arizalar statuslari: kalit => [nomi, rang klassi] */
function order_statuses(): array
{
    return [
        'new'         => ['Yangi', 'blue'],
        'contacted'   => ['Bog‘lanildi', 'violet'],
        'in_progress' => ['Jarayonda', 'amber'],
        'won'         => ['Shartnoma', 'green'],
        'lost'        => ['Rad etildi', 'gray'],
        'spam'        => ['Spam', 'red'],
    ];
}

function status_badge(string $status): string
{
    $statuses = order_statuses();
    [$label, $color] = $statuses[$status] ?? [$status, 'gray'];
    return '<span class="badge badge-' . e($color) . '">' . e($label) . '</span>';
}

/** Admin sahifani umumiy layout ichida chiqarish. */
function render_admin(string $title, string $active, callable $content): void
{
    $flashes = take_flashes();
    $admin = Auth::user();
    $newOrders = 0;
    try {
        $newOrders = (int)db()->query("SELECT COUNT(*) FROM applications WHERE status = 'new'")->fetchColumn();
    } catch (Throwable $e) {
        $newOrders = 0;
    }
    require __DIR__ . '/views/layout.php';
}

function icon(string $name): string
{
    static $icons = [
        'dashboard' => '<path d="M3 13h8V3H3zM13 21h8V11h-8zM3 21h8v-6H3zM13 3v6h8V3z"/>',
        'orders'    => '<path d="M4 4h16v16H4z"/><path d="M8 9h8M8 13h8M8 17h5"/>',
        'texts'     => '<path d="M4 7V4h16v3M9 20h6M12 4v16"/>',
        'services'  => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
        'stats'     => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'portfolio' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'tag'       => '<path d="M20 12 12 20 3 11V3h8z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
        'clients'   => '<path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>',
        'quote'     => '<path d="M7 7h4v4c0 3-2 5-4 6M14 7h4v4c0 3-2 5-4 6"/>',
        'case'      => '<path d="M3 17l6-6 4 4 8-8M15 7h6v6"/>',
        'team'      => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M21.5 20a6.5 6.5 0 0 0-4-6"/>',
        'map'       => '<path d="M9 4 3 6v14l6-2 6 2 6-2V4l-6 2zM9 4v14M15 6v14"/>',
        'pin'       => '<path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'eye'       => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'media'     => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5-9 9"/>',
        'seo'       => '<circle cx="11" cy="11" r="7"/><path d="m21 21-5-5"/>',
        'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'audit'     => '<path d="M12 8v4l3 2"/><circle cx="12" cy="12" r="9"/>',
        'external'  => '<path d="M14 4h6v6M20 4l-9 9M18 14v6H4V6h6"/>',
        'logout'    => '<path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h11"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'edit'      => '<path d="M4 20h4L19 9l-4-4L4 16z"/>',
        'trash'     => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
        'grip'      => '<circle cx="9" cy="6" r="1.2"/><circle cx="15" cy="6" r="1.2"/><circle cx="9" cy="12" r="1.2"/><circle cx="15" cy="12" r="1.2"/><circle cx="9" cy="18" r="1.2"/><circle cx="15" cy="18" r="1.2"/>',
        'phone'     => '<path d="M6.6 3.8 9 3.3l2 4.8-2 1.5a15 15 0 0 0 5.4 5.4l1.5-2 4.8 2-.5 2.4a2 2 0 0 1-2 1.6A15.8 15.8 0 0 1 5 5.8a2 2 0 0 1 1.6-2Z"/>',
        'download'  => '<path d="M12 4v11M7 10l5 5 5-5M4 20h16"/>',
        'upload'    => '<path d="M12 20V9M7 14l5-5 5 5M4 4h16"/>',
        'send'      => '<path d="M21 3 10 14M21 3l-7 18-4-7-7-4z"/>',
        'menu'      => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'theme'     => '<path d="M21 13A9 9 0 1 1 11 3a7 7 0 0 0 10 10z"/>',
        'check'     => '<path d="m5 12 5 5 9-10"/>',
        'x'         => '<path d="M6 6l12 12M18 6 6 18"/>',
    ];
    return '<svg class="ico" viewBox="0 0 24 24" aria-hidden="true">' . ($icons[$name] ?? '') . '</svg>';
}

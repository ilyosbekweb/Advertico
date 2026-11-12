<?php
declare(strict_types=1);

/** Konfiguratsiyadan qiymat olish: config('db.host'). */
function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['__config'] ?? [];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

/** Yagona PDO ulanishi. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $db = config('db');
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $db['host'], $db['name'], $db['charset'] ?? 'utf8mb4');
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $pdo->exec("SET time_zone = '" . (new DateTime())->format('P') . "'");
    return $pdo;
}

/** HTML uchun xavfsiz chiqarish. */
function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Sayt ildiziga nisbatan absolyut URL yo'li: url('admin/') => /Advertico/admin/ */
function url(string $path = ''): string
{
    $base = '/' . trim((string)config('base_path', '/'), '/') . '/';
    $base = $base === '//' ? '/' : $base;
    return $base . ltrim($path, '/');
}

/** To'liq manzil (canonical, sitemap uchun). */
function absolute_url(string $path = ''): string
{
    return rtrim((string)config('site_url', ''), '/') . '/' . ltrim($path, '/');
}

function client_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

function json_response(int $status, array $data): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** JSON qiymatni HTML <script type="application/json"> ichiga xavfsiz joylash. */
function json_for_html(mixed $data): string
{
    return json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?: 'null';
}

function str_limit(string $value, int $limit): string
{
    return mb_strlen($value) > $limit ? mb_substr($value, 0, $limit - 1) . '…' : $value;
}

/** Sozlamalar jadvalidan qiymat (bitta so'rovda hammasi keshlanadi). */
function setting(string $key, string $default = ''): string
{
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        try {
            foreach (db()->query('SELECT `key`, `value` FROM settings') as $row) {
                $settings[$row['key']] = (string)$row['value'];
            }
        } catch (Throwable $e) {
            error_log('Advertico settings error: ' . $e->getMessage());
        }
    }
    return array_key_exists($key, $settings) && $settings[$key] !== '' ? $settings[$key] : $default;
}

function set_setting(string $key, string $value): void
{
    $stmt = db()->prepare('INSERT INTO settings (`key`, `value`) VALUES (:k, :v) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
    $stmt->execute([':k' => $key, ':v' => $value]);
}

/** Admin harakatlari jurnali. */
function audit(string $action, string $entity = '', ?int $entityId = null, array $details = []): void
{
    try {
        $stmt = db()->prepare('INSERT INTO audit_log (admin_id, action, entity, entity_id, details, ip) VALUES (:a, :ac, :en, :id, :d, :ip)');
        $stmt->execute([
            ':a'  => $_SESSION['admin_id'] ?? null,
            ':ac' => $action,
            ':en' => $entity,
            ':id' => $entityId,
            ':d'  => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            ':ip' => client_ip(),
        ]);
    } catch (Throwable $e) {
        error_log('Advertico audit error: ' . $e->getMessage());
    }
}

/** Rasm o'lchamlari: kichik nusxa (galereya pastidagi rasmlar) va katta nusxa (asosiy ko'rinish). */
const MEDIA_VARIANTS = ['thumb' => 480, 'large' => 1600];

/** Variant fayl yo'li (mavjud bo'lmasligi mumkin). */
function media_variant_path(string $path, string $size): string
{
    return (string)preg_replace('/\.[a-z0-9]+$/i', '', $path) . '-' . $size . '.webp';
}

/** Variant mavjud bo'lsa uning yo'li, aks holda original rasm. */
function media_variant(string $path, string $size): string
{
    if (!isset(MEDIA_VARIANTS[$size]) || !str_starts_with($path, 'uploads/')) {
        return $path;
    }
    $variant = media_variant_path($path, $size);
    return is_file(ROOT_PATH . '/' . $variant) ? $variant : $path;
}

/** Telefon raqamini tel: havolasi uchun tozalash. */
function tel_href(string $phone): string
{
    return 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
}

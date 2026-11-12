<?php
declare(strict_types=1);

/*
 * Public xarita uchun reklama joylari (faqat nashr qilinganlari).
 * Kesh: ETag ma'lumotlar versiyasidan hisoblanadi — admin har qanday o'zgarish kiritganda
 * brauzer keyingi so'rovda yangi ma'lumotni oladi (no-cache + 304).
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    http_response_code(405);
    header('Allow: GET');
    echo '{"success":false}';
    exit;
}

try {
    require dirname(__DIR__) . '/app/bootstrap.php';

    $etag = '"' . AdLocations::version() . '"';
    header('ETag: ' . $etag);
    header('Cache-Control: no-cache');
    if (trim((string)($_SERVER['HTTP_IF_NONE_MATCH'] ?? '')) === $etag) {
        http_response_code(304);
        exit;
    }

    $types = [];
    foreach (db()->query('SELECT slug, name_uz, name_ru FROM services WHERE is_active = 1 ORDER BY sort_order, id') as $s) {
        $types[] = ['slug' => $s['slug'], 'name' => ['uz' => $s['name_uz'], 'ru' => $s['name_ru'] !== '' ? $s['name_ru'] : $s['name_uz']]];
    }

    echo json_encode([
        'success'   => true,
        'locations' => AdLocations::publicList(),
        'types'     => $types,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('Advertico locations API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'locations' => [], 'types' => []]);
}

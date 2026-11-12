<?php
declare(strict_types=1);

/*
 * Public sahifalar renderi. .htaccess /about.html kabi so'rovlarni shu faylga yo'naltiradi,
 * shuning uchun URL'lar va script.js dagi sahifa almashish animatsiyasi o'zgarmaydi.
 *
 * Ishonchlilik: baza ishlamay qolsa, oxirgi muvaffaqiyatli render (app/cache) yoki
 * asl statik sahifa (app/fallback) ko'rsatiladi — sayt hech qachon bo'sh qolmaydi.
 */

const PAGES = [
    'index'     => ['data' => 'home',      'project' => true,  'service' => true,  'form' => false],
    'about'     => ['data' => 'about',     'project' => true,  'service' => false, 'form' => false],
    'services'  => ['data' => 'services',  'project' => false, 'service' => true,  'form' => false],
    'portfolio' => ['data' => 'portfolio', 'project' => true,  'service' => false, 'form' => false],
    'locations' => ['data' => 'locations', 'project' => true,  'service' => false, 'form' => false],
    'contact'   => ['data' => 'contact',   'project' => true,  'service' => false, 'form' => true],
];

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

$page = (string)($_GET['page'] ?? 'index');

if ($page === 'sitemap' || $page === 'robots') {
    require __DIR__ . '/app/bootstrap.php';
    require __DIR__ . '/app/views/' . $page . '.php';
    exit;
}

if (!isset(PAGES[$page])) {
    http_response_code(404);
    $page = 'index';
}

$cacheFile = __DIR__ . '/app/cache/' . $page . '.html';
$fallbackFile = __DIR__ . '/app/fallback/' . $page . '.html';

ob_start();
try {
    require __DIR__ . '/app/bootstrap.php';
    Content::load();

    $meta = PAGES[$page];
    $dataPage = $meta['data'];
    $seo = Content::seo($dataPage);
    $withProjectModal = $meta['project'];
    $withServiceModal = $meta['service'];
    $withForm = $meta['form'];

    // script.js va backend.js uchun ma'lumotlar (#site-data).
    $siteData = [
        'seo' => [
            'title'       => Content::pair($seo, 'title'),
            'description' => Content::pair($seo, 'description'),
        ],
    ];
    if ($withServiceModal) {
        $siteData['services'] = Content::serviceCatalog();
    }
    if ($page === 'locations') {
        $siteData['districts'] = Content::districtMap();
        $siteData['districtDefault'] = Content::text('locations.district_default');
    }
    if ($withForm) {
        $siteData['form'] = [
            'sending' => Content::text('contact.msg_sending'),
            'success' => Content::text('contact.msg_success'),
            'error'   => Content::text('contact.msg_error'),
            'network' => Content::text('contact.msg_network'),
            'submit'  => Content::text('contact.submit'),
        ];
    }

    require __DIR__ . '/app/views/partials/header.php';
    require __DIR__ . '/app/views/' . $page . '.php';
    require __DIR__ . '/app/views/partials/footer.php';

    $html = (string)ob_get_clean();

    // Oxirgi muvaffaqiyatli nusxani 5 daqiqada bir marta yangilab boramiz.
    // Parametrli so'rovlar (masalan, contact.html?location=5) zaxira keshiga yozilmaydi.
    if (count($_GET) === 1 && (!is_file($cacheFile) || filemtime($cacheFile) < time() - 300)) {
        $tmp = $cacheFile . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if (@file_put_contents($tmp, $html) !== false) {
            @rename($tmp, $cacheFile);
        }
    }

    header('Content-Type: text/html; charset=utf-8');
    echo $html;
} catch (Throwable $e) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    error_log('Advertico render error (' . $page . '): ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    header('Content-Type: text/html; charset=utf-8');
    if (is_file($cacheFile)) {
        readfile($cacheFile);
    } elseif (is_file($fallbackFile)) {
        readfile($fallbackFile);
    } else {
        http_response_code(503);
        echo 'Sayt vaqtincha ishlamayapti. Iltimos, birozdan keyin qayta urinib ko‘ring.';
    }
}

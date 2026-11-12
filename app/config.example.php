<?php
/**
 * ADVERTICO konfiguratsiyasi.
 * Ushbu faylni app/config.php nomi bilan nusxalang va qiymatlarni to'ldiring.
 * app/config.php hech qachon Git'ga qo'shilmaydi (.gitignore).
 */
return [
    // Ishlab chiqarish (production) serverida doim false bo'lsin.
    'debug' => false,

    // Sayt joylashgan papka. Domen ildizida bo'lsa '/', XAMPP'da masalan '/Advertico/'.
    'base_path' => '/',

    // To'liq manzil (canonical, sitemap, Open Graph uchun). Oxirida / qo'ymang.
    'site_url' => 'https://advertico.uz',

    'timezone' => 'Asia/Tashkent',

    'db' => [
        'host'    => 'localhost',
        'name'    => 'advertico',
        'user'    => 'advertico_user',
        'pass'    => 'CHANGE_ME',
        'charset' => 'utf8mb4',
    ],

    'telegram' => [
        // @BotFather bergan token. Faqat shu yerda saqlanadi, admin panelda ko'rsatilmaydi.
        'bot_token' => '',
        // Chat ID admin paneldagi Sozlamalar bo'limidan kiritiladi.
        // Bu yerdagi qiymat faqat zaxira (fallback) sifatida ishlatiladi.
        'chat_id'   => '',
    ],

    // Birinchi administratorni yaratish uchun bir martalik kalit (kamida 24 belgi).
    // /admin/setup.php sahifasida so'raladi. Admin yaratilgach, sahifa avtomatik yopiladi.
    'setup_key' => '',
];

ADVERTICO — PHP + MySQL + Telegram + Admin Panel

TALABLAR
- PHP 8.1+ (8.2+ tavsiya), kengaytmalar: pdo_mysql, curl, fileinfo, mbstring (gd — ixtiyoriy, tavsiya etiladi)
- MySQL 8 / MariaDB 10.4+
- Apache: mod_rewrite yoqilgan, AllowOverride All (.htaccess ishlashi shart)

TUZILISH
- index.html, about.html ... — URL'lar o'zgarmagan. .htaccess ularni render.php ga yo'naltiradi,
  sahifa kontenti esa bazadan olinadi (app/views/ shablonlari).
- admin/            — Admin panel (/admin/)
- api/contact.php   — Aloqa formasi: bazaga saqlaydi + Telegram'ga yuboradi
- app/              — Yadro, konfiguratsiya, shablonlar (brauzerdan yopiq)
- app/fallback/     — Asl statik sahifalar: baza ishlamay qolsa ko'rsatiladi
- database/         — schema.sql, seed.sql, migrations/ (brauzerdan yopiq)
- uploads/          — Admin yuklagan rasmlar (PHP bajarilishi taqiqlangan)

O'RNATISH
1. Baza: phpMyAdmin'da bo'sh baza yarating (utf8mb4_unicode_ci), so'ng import qiling:
   database/schema.sql  →  database/seed.sql  →  database/migrations/003_redesign_texts.sql
   →  database/migrations/004_ad_locations.sql  (003 va 004 qayta ishga tushirilsa ham xavfsiz)
   (Serverda eski arizalar bo'lsa — avval database/migrations/001_*.sql faylini o'qing.)
2. Konfiguratsiya: app/config.example.php → app/config.php nusxa oling va to'ldiring:
   - debug => false (production'da MAJBURIY)
   - base_path => '/' (domen ildizida) yoki '/papka/'
   - site_url => 'https://advertico.uz'
   - db => ma'lumotlar bazasi
   - telegram.bot_token => @BotFather bergan token
   - setup_key => kamida 24 belgili tasodifiy satr
3. Administrator: https://sayt/admin/setup.php → setup_key, ism, email, parol.
   Admin yaratilgach, setup sahifasi avtomatik yopiladi.
4. Telegram: botni guruhga qo'shing, Admin → Sozlamalar → Chat ID kiriting → "Sinov xabari".
5. HTTPS/SSL yoqing. uploads/ va app/cache/ papkalari PHP tomonidan yozilishi mumkin bo'lsin.

ADMIN PANEL
- Boshqaruv paneli: arizalar statistikasi, 30 kunlik grafik, tizim holati
- Arizalar: holatlar, izohlar, tarix, qidiruv/filtr, CSV eksport, Telegram'ga qayta yuborish
- Sahifa matnlari: saytdagi barcha statik matnlar (UZ/RU)
- Xizmatlar, Statistika, Portfolio, Kategoriyalar, Mijozlar, Fikrlar, Keyslar, Jamoa, Tumanlar
- Reklama nuqtalari: xaritadagi joylar (koordinata, tuman, fotolar, nashr/qoralama). Nashr qilinganlari
  locations.html xaritasida Advertico belgisi bilan avtomatik chiqadi (api/locations.php).
- Media kutubxona, SEO (title, description, OG rasm, noindex; sitemap.xml avtomatik), Sozlamalar
- Faollik jurnali

XAVFSIZLIK
- app/config.php ni hech qachon Git'ga qo'shmang (.gitignore'da).
- Parollar Argon2id bilan saqlanadi; 5 ta xato urinishdan so'ng 15 daqiqa blok.
- Barcha formalar CSRF token bilan himoyalangan; sessiya 30 daqiqa harakatsizlikda tugaydi.
- Aloqa formasi: bitta IP'dan soatiga 5 ta ariza (Sozlamalarda o'zgartiriladi).

LOKAL ISHGA TUSHIRISH (XAMPP)
Apache va MySQL'ni yoqing, loyiha htdocs/Advertico ichida bo'lsin, base_path => '/Advertico/'.
http://localhost/Advertico/ — sayt,  http://localhost/Advertico/admin/ — admin panel.

XARITA
- Tuman chegaralari: assets/geo/jizzakh-districts.geojson — geoBoundaries gbOpen UZB ADM2 (OCHA ROCCA),
  CC BY 3.0 IGO. Manba yozuvi xaritada ko‘rsatiladi. Chegaralar ~60 m soddalashtirilgan va rasmiy chegaradan
  bir oz farq qilishi mumkin (masalan, Jizzax shahri poligoni). Admin chegara tekshiruvini tasdiq bilan chetlab o‘ta oladi.
- Xarita foni (tile): Admin → Sozlamalar → Reklama xaritasi. Standart OpenStreetMap tile’lari FAQAT sinov uchun —
  production’da tijorat shartlari mos provayder (MapTiler, Stadia Maps va h.k.) kalitini kiriting.
- Leaflet 1.9.4 va Leaflet.markercluster 1.5.3 assets/vendor/ ichida (cdnjs SRI bilan tekshirilgan).
- PHP GD kengaytmasi tavsiya etiladi: yuklangan fotolardan thumb (480px) va large (1600px) WebP variantlari yaratiladi.

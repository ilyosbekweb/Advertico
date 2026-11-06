ADVERTICO — PHP + MySQL + Telegram backend

TALABLAR
- PHP 8.0+ (PHP 8.2 tavsiya qilinadi)
- MySQL/MariaDB
- PHP PDO MySQL va cURL kengaytmalari yoqilgan bo‘lishi kerak
- Sayt PHP ishlaydigan hostingda joylashishi kerak. Oddiy static hosting yetarli emas.

1. MYSQL BAZA
- phpMyAdmin'ni oching.
- database.sql faylini import qiling.
- Hostingda alohida DB/user ochgan bo‘lsangiz, config.php ichidagi db qiymatlarini moslang.

2. TELEGRAM BOT
- Telegram'da @BotFather orqali bot yarating.
- Bot tokenini oling.
- Botga o‘zingiz yoki admin guruhingizdan /start yuboring.
- Chat ID ni aniqlang (masalan, @userinfobot orqali yoki Telegram Bot API getUpdates orqali).
- config.php ichidagi bot_token va chat_id qiymatlarini kiriting.
- Guruhga yuborish uchun botni guruhga qo‘shing va guruh chat ID sini kiriting.

3. SERVERGA JOYLASH
- Barcha loyiha fayllarini PHP qo‘llaydigan hostingning public_html papkasiga yuklang.
- config.php ichidagi DB ma'lumotlarini to‘ldiring.
- HTTPS/SSL yoqing.
- Saytni contact.html orqali tekshiring.

4. ISHLASHI
- Mijoz contact.html formasini yuboradi.
- api/contact.php ma'lumotni tekshiradi va MySQL applications jadvaliga saqlaydi.
- Keyin Telegram botga xabar yuboradi.
- Telegram vaqtincha ishlamasa ham murojaat bazada saqlanadi.

XAVFSIZLIK
- config.php faylini ommaviy GitHub repozitoriyga qo‘ymang.
- Bot tokenini hech qachon frontend JS/HTML ichiga yozmang.
- Ishga tushirishdan oldin hostingda PHP xatolarini foydalanuvchiga ko‘rsatishni o‘chiring.
- Admin panel hali qo‘shilmagan; murojaatlar hozircha MySQL/phpMyAdmin orqali ko‘riladi.

ESLATMA
Saytni file:// orqali ochib backend ishlamaydi. XAMPP'da Apache va MySQL'ni yoqib,
loyihani htdocs ichiga joylang va http://localhost/posledni/contact.html orqali oching.

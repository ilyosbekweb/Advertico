-- Eski arizalarni yangi jadvalga ko'chirish (001 dan keyin, schema.sql + seed.sql import qilingach).
-- Eski arizalar "Bog'lanildi" holatida ko'chiriladi: ular allaqachon Telegram orqali ko'rilgan.

INSERT INTO applications (id, name, phone, service, service_id, message, status, ip_address, telegram_sent, created_at)
SELECT l.id, l.name, l.phone, COALESCE(l.service, ''),
       (SELECT s.id FROM services s WHERE s.name_uz = l.service OR s.name_ru = l.service LIMIT 1),
       l.message, 'contacted', l.ip_address, 1, l.created_at
FROM applications_legacy l;

-- Hammasi to'g'ri ko'chganini tekshirgach, eski jadvalni o'chirishingiz mumkin:
-- DROP TABLE applications_legacy;

-- Yangi dizayn (2026-11) uchun matn kalitlari.
-- Xavfsiz: faqat yangi qatorlar qo'shiladi (INSERT IGNORE), mavjud matnlar o'zgartirilmaydi.
-- Qayta ishga tushirish mumkin (idempotent). seed.sql dan keyin import qiling.

SET NAMES utf8mb4;

INSERT IGNORE INTO texts (`key`, page, section, label, type, uz, ru, sort_order) VALUES
('common.nav.cta', 'common', 'Menyu', 'Menyu: o‘ngdagi tugma', 'text', 'Ariza qoldirish', 'Оставить заявку', 65),
('common.footer.about', 'common', 'Footer', 'Footer: kompaniya haqida qisqacha', 'textarea', 'Jizzax viloyatidagi birinchi va eng yirik tashqi reklama operatori. LED ekranlar, billboardlar va transport reklamasi — g‘oyadan o‘rnatishgacha.', 'Первый и крупнейший оператор наружной рекламы в Джизакской области. LED-экраны, билборды и реклама на транспорте — от идеи до монтажа.', 95),
('common.footer.nav_title', 'common', 'Footer', 'Footer: sahifalar sarlavhasi', 'text', 'Sahifalar', 'Страницы', 96),
('common.footer.services_title', 'common', 'Footer', 'Footer: xizmatlar sarlavhasi', 'text', 'Xizmatlar', 'Услуги', 97),
('common.footer.contact_title', 'common', 'Footer', 'Footer: aloqa sarlavhasi', 'text', 'Aloqa', 'Контакты', 98),
('home.hero.title_1', 'home', 'Hero', 'Hero: sarlavha 1-qator', 'text', 'Brendingizni', 'Сделайте ваш бренд', 161),
('home.hero.title_2', 'home', 'Hero', 'Hero: sarlavha 2-qator', 'text', 'shahar miqyosida', 'заметным', 162),
('home.hero.title_accent', 'home', 'Hero', 'Hero: sarlavha 3-qator (rangli)', 'text', 'ko‘rinadigan qiling', 'на весь город', 163),
('home.hero.scroll', 'home', 'Hero', 'Hero: pastga aylantirish yozuvi', 'text', 'SCROLL', '', 225),
('home.portfolio.label', 'home', 'Loyihalar bloki', 'Loyihalar: ustki yozuv', 'text', 'BIZNING ISHLARIMIZ', 'НАШИ РАБОТЫ', 284),
('home.portfolio.title', 'home', 'Loyihalar bloki', 'Loyihalar: sarlavha', 'text', 'So‘nggi', 'Последние', 285),
('home.portfolio.accent', 'home', 'Loyihalar bloki', 'Loyihalar: sarlavha (rangli qism)', 'text', 'loyihalar', 'проекты', 286),
('home.portfolio.text', 'home', 'Loyihalar bloki', 'Loyihalar: tavsif', 'textarea', 'Turli sohalardagi brendlar uchun yaratgan reklama yechimlarimiz.', 'Рекламные решения, которые мы создали для брендов из разных отраслей.', 287),
('home.portfolio.button', 'home', 'Loyihalar bloki', 'Loyihalar: barcha loyihalar tugmasi', 'text', 'Barcha loyihalar', 'Все проекты', 288);

-- Yangi dizaynda ishlatilmaydigan kalitlar: faqat admin yorlig'i belgilanadi, matn o'zgarmaydi.
UPDATE texts SET label = CONCAT('(Eski dizayn, saytda ko‘rinmaydi) ', label)
WHERE `key` IN ('home.hero.line1', 'home.hero.line2', 'home.art.letter', 'home.art.line1', 'home.art.line2', 'home.art.line3', 'home.art.index')
  AND label NOT LIKE '(Eski dizayn%';

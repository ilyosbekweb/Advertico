-- Reklama nuqtalari (xarita) — 2026-11.
-- Xavfsiz va qayta ishga tushirsa bo'ladi: faqat yangi jadval/ustun/qator qo'shadi, hech narsa o'chirilmaydi.
-- 003_redesign_texts.sql dan keyin import qiling.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS ad_locations (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    district_id     INT UNSIGNED NULL,
    service_id      INT UNSIGNED NULL,                 -- reklama turi = xizmatlar ro'yxati
    name_uz         VARCHAR(200) NOT NULL,
    name_ru         VARCHAR(200) NOT NULL DEFAULT '',
    address_uz      VARCHAR(255) NOT NULL DEFAULT '',
    address_ru      VARCHAR(255) NOT NULL DEFAULT '',
    description_uz  TEXT NULL,
    description_ru  TEXT NULL,
    lat             DECIMAL(9,6) NULL,                 -- WGS84
    lng             DECIMAL(9,6) NULL,
    width_m         DECIMAL(6,2) NULL,
    height_m        DECIMAL(6,2) NULL,
    sides           TINYINT UNSIGNED NULL,
    lighting        TINYINT(1) NOT NULL DEFAULT 0,
    availability    VARCHAR(20) NOT NULL DEFAULT 'available', -- available | reserved | occupied | maintenance
    status          VARCHAR(20) NOT NULL DEFAULT 'draft',     -- draft | published
    cover_media_id  INT UNSIGNED NULL,
    published_at    DATETIME NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_adloc_public (status, district_id),
    KEY idx_adloc_updated (updated_at),
    CONSTRAINT fk_adloc_district FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL,
    CONSTRAINT fk_adloc_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    CONSTRAINT fk_adloc_cover FOREIGN KEY (cover_media_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ad_location_photos (
    location_id INT UNSIGNED NOT NULL,
    media_id    INT UNSIGNED NOT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    PRIMARY KEY (location_id, media_id),
    KEY idx_adphoto_media (media_id),
    CONSTRAINT fk_adphoto_location FOREIGN KEY (location_id) REFERENCES ad_locations(id) ON DELETE CASCADE,
    CONSTRAINT fk_adphoto_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Arizani reklama joyiga bog'lash (ustun faqat yo'q bo'lsa qo'shiladi).
SET @has_col := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'applications' AND COLUMN_NAME = 'location_id');
SET @ddl := IF(@has_col = 0,
    'ALTER TABLE applications ADD COLUMN location_id INT UNSIGNED NULL AFTER service_id, ADD KEY idx_app_location (location_id), ADD CONSTRAINT fk_app_location FOREIGN KEY (location_id) REFERENCES ad_locations(id) ON DELETE SET NULL',
    'SELECT 1');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Xarita sozlamalari (Admin → Sozlamalar). OSM tile'lari faqat ishlab chiqish uchun:
-- production'dan oldin tijorat shartlari mos keladigan provayderga almashtiring.
INSERT IGNORE INTO settings (`key`, `value`) VALUES
('map_tile_url', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
('map_tile_attribution', '&copy; OpenStreetMap contributors'),
('map_satellite_url', ''),
('map_satellite_attribution', ''),
('map_boundary_attribution', '&copy; geoBoundaries / OCHA ROCCA (CC BY 3.0 IGO)');

INSERT IGNORE INTO texts (`key`, page, section, label, type, uz, ru, sort_order) VALUES
('locations.map.back', 'locations', 'Reklama xaritasi', 'Viloyatga qaytish tugmasi', 'text', 'Viloyatga qaytish', 'К области', 600),
('locations.map.hint', 'locations', 'Reklama xaritasi', 'Viloyat ko‘rinishidagi yo‘riqnoma', 'text', 'Tumanni tanlang — undagi reklama joylari xaritada ko‘rinadi.', 'Выберите район — его рекламные места появятся на карте.', 601),
('locations.map.filter_type', 'locations', 'Reklama xaritasi', 'Filtr: reklama turi', 'text', 'Reklama turi', 'Тип рекламы', 602),
('locations.map.filter_availability', 'locations', 'Reklama xaritasi', 'Filtr: holati', 'text', 'Holati', 'Статус', 603),
('locations.map.filter_all', 'locations', 'Reklama xaritasi', 'Filtr: barchasi', 'text', 'Barchasi', 'Все', 604),
('locations.map.search', 'locations', 'Reklama xaritasi', 'Qidiruv maydoni', 'text', 'Joy nomi yoki manzil…', 'Название или адрес…', 605),
('locations.map.count', 'locations', 'Reklama xaritasi', 'Joylar soni yozuvi (raqamdan keyin)', 'text', 'ta reklama joyi', 'рекламных мест', 606),
('locations.map.empty', 'locations', 'Reklama xaritasi', 'Tumanda joy yo‘q', 'text', 'Bu tumanda hozircha reklama joylari qo‘shilmagan. Biz bilan bog‘laning — mos variant topamiz.', 'В этом районе пока нет добавленных рекламных мест. Свяжитесь с нами — подберём вариант.', 607),
('locations.map.no_results', 'locations', 'Reklama xaritasi', 'Filtr bo‘yicha natija yo‘q', 'text', 'Filtrga mos joy topilmadi.', 'По фильтру ничего не найдено.', 608),
('locations.detail.type', 'locations', 'Joy paneli', 'Maydon: turi', 'text', 'Turi', 'Тип', 610),
('locations.detail.district', 'locations', 'Joy paneli', 'Maydon: tuman', 'text', 'Tuman', 'Район', 611),
('locations.detail.dimensions', 'locations', 'Joy paneli', 'Maydon: o‘lcham', 'text', 'O‘lcham', 'Размер', 612),
('locations.detail.availability', 'locations', 'Joy paneli', 'Maydon: holati', 'text', 'Holati', 'Статус', 613),
('locations.detail.sides', 'locations', 'Joy paneli', 'Maydon: tomonlar', 'text', 'Tomonlar', 'Стороны', 614),
('locations.detail.lighting', 'locations', 'Joy paneli', 'Maydon: yoritish', 'text', 'Yoritish', 'Подсветка', 615),
('locations.detail.yes', 'locations', 'Joy paneli', 'Ha', 'text', 'Bor', 'Есть', 616),
('locations.detail.no', 'locations', 'Joy paneli', 'Yo‘q', 'text', 'Yo‘q', 'Нет', 617),
('locations.detail.no_photos', 'locations', 'Joy paneli', 'Fotosurat yo‘q', 'text', 'Bu joy uchun fotosuratlar tez orada qo‘shiladi.', 'Фотографии этого места скоро появятся.', 618),
('locations.detail.cta', 'locations', 'Joy paneli', 'Ariza tugmasi', 'text', 'Ariza qoldirish', 'Оставить заявку', 619),
('locations.detail.close', 'locations', 'Joy paneli', 'Yopish', 'text', 'Yopish', 'Закрыть', 620),
('locations.availability.available', 'locations', 'Bandlik holatlari', 'Holat: bo‘sh', 'text', 'Bo‘sh', 'Свободно', 630),
('locations.availability.reserved', 'locations', 'Bandlik holatlari', 'Holat: bron qilingan', 'text', 'Bron qilingan', 'Забронировано', 631),
('locations.availability.occupied', 'locations', 'Bandlik holatlari', 'Holat: band', 'text', 'Band', 'Занято', 632),
('locations.availability.maintenance', 'locations', 'Bandlik holatlari', 'Holat: ta’mirda', 'text', 'Ta’mirda', 'На обслуживании', 633),
('contact.selected_location', 'contact', 'Forma', 'Tanlangan reklama joyi yorlig‘i', 'text', 'Tanlangan joy', 'Выбранное место', 125);

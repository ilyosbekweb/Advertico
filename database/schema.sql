-- ADVERTICO — ma'lumotlar bazasi sxemasi
-- MySQL 8.0+ / MariaDB 10.4+, utf8mb4.
-- Import tartibi: 1) schema.sql  2) seed.sql
-- Eslatma: bu fayl mavjud jadvallarni O'CHIRADI. Ishlab turgan bazani yangilash uchun
-- database/migrations/ papkasidagi fayllardan foydalaning.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_log, application_events, applications, login_attempts, admins,
    services, portfolio_items, portfolio_categories, clients, testimonials, case_studies,
    team_members, stats, districts, seo_pages, texts, settings, media;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------- Admin
CREATE TABLE admins (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120) NOT NULL,
    email           VARCHAR(190) NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    last_login_at   DATETIME NULL,
    last_login_ip   VARCHAR(45) NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email       VARCHAR(190) NOT NULL DEFAULT '',
    ip          VARCHAR(45) NOT NULL,
    success     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_login_ip (ip, created_at),
    KEY idx_login_email (email, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_log (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id    INT UNSIGNED NULL,
    action      VARCHAR(60) NOT NULL,
    entity      VARCHAR(60) NOT NULL DEFAULT '',
    entity_id   INT UNSIGNED NULL,
    details     TEXT NULL,
    ip          VARCHAR(45) NOT NULL DEFAULT '',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_audit_time (created_at),
    CONSTRAINT fk_audit_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Media
CREATE TABLE media (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    path            VARCHAR(255) NOT NULL,           -- sayt ildiziga nisbatan: uploads/2026/11/x.webp
    original_name   VARCHAR(255) NOT NULL DEFAULT '',
    mime            VARCHAR(80) NOT NULL,
    size            INT UNSIGNED NOT NULL DEFAULT 0,
    width           INT UNSIGNED NULL,
    height          INT UNSIGNED NULL,
    alt_uz          VARCHAR(255) NOT NULL DEFAULT '',
    alt_ru          VARCHAR(255) NOT NULL DEFAULT '',
    is_system       TINYINT(1) NOT NULL DEFAULT 0,  -- loyiha bilan kelgan fayl: diskdan o'chirilmaydi
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_media_path (path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Kontent
CREATE TABLE texts (
    `key`       VARCHAR(120) NOT NULL PRIMARY KEY,  -- masalan: home.hero.description
    page        VARCHAR(40) NOT NULL,               -- admin guruhlash: common, home, about ...
    section     VARCHAR(80) NOT NULL DEFAULT '',
    label       VARCHAR(190) NOT NULL,
    type        VARCHAR(20) NOT NULL DEFAULT 'text', -- text | textarea
    uz          TEXT NOT NULL,
    ru          TEXT NOT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    updated_at  DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_texts_page (page, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    `key`       VARCHAR(80) NOT NULL PRIMARY KEY,
    `value`     TEXT NOT NULL,
    updated_at  DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE seo_pages (
    page_key        VARCHAR(40) NOT NULL PRIMARY KEY,
    label           VARCHAR(80) NOT NULL,
    title_uz        VARCHAR(255) NOT NULL DEFAULT '',
    title_ru        VARCHAR(255) NOT NULL DEFAULT '',
    description_uz  VARCHAR(500) NOT NULL DEFAULT '',
    description_ru  VARCHAR(500) NOT NULL DEFAULT '',
    og_image_id     INT UNSIGNED NULL,
    noindex         TINYINT(1) NOT NULL DEFAULT 0,
    updated_at      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_seo_image FOREIGN KEY (og_image_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug            VARCHAR(60) NOT NULL,            -- URL hash va data-service: billboard, led ...
    number          VARCHAR(10) NOT NULL DEFAULT '',
    name_uz         VARCHAR(160) NOT NULL,           -- qisqa nom: menyu, kartalar, forma
    name_ru         VARCHAR(160) NOT NULL DEFAULT '',
    title_uz        VARCHAR(200) NOT NULL,           -- katalog oynasidagi sarlavha
    title_ru        VARCHAR(200) NOT NULL DEFAULT '',
    description_uz  TEXT NOT NULL,
    description_ru  TEXT NOT NULL,
    home_price_uz   VARCHAR(160) NOT NULL DEFAULT '',  -- bosh sahifa kartasidagi narx
    home_price_ru   VARCHAR(160) NOT NULL DEFAULT '',
    list_price_uz   VARCHAR(160) NOT NULL DEFAULT '',  -- xizmatlar sahifasidagi narx
    list_price_ru   VARCHAR(160) NOT NULL DEFAULT '',
    prices          LONGTEXT NOT NULL,                 -- JSON: [{label_uz,label_ru,value_uz,value_ru}]
    image_id        INT UNSIGNED NULL,
    show_on_home    TINYINT(1) NOT NULL DEFAULT 1,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    sort_order      INT NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_services_slug (slug),
    CONSTRAINT fk_services_image FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE portfolio_categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(40) NOT NULL,
    name_uz     VARCHAR(120) NOT NULL,
    name_ru     VARCHAR(120) NOT NULL DEFAULT '',
    sort_order  INT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_pcat_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE portfolio_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     INT UNSIGNED NULL,
    title_uz        VARCHAR(200) NOT NULL,
    title_ru        VARCHAR(200) NOT NULL DEFAULT '',
    description_uz  TEXT NOT NULL,
    description_ru  TEXT NOT NULL,
    accent_color    VARCHAR(9) NOT NULL DEFAULT '#16a34a',
    image_id        INT UNSIGNED NULL,
    is_published    TINYINT(1) NOT NULL DEFAULT 1,
    sort_order      INT NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pitem_cat FOREIGN KEY (category_id) REFERENCES portfolio_categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_pitem_image FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clients (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120) NOT NULL,
    logo_id         INT UNSIGNED NULL,       -- yuklangan logotip rasmi
    logo_svg        TEXT NULL,               -- yoki inline SVG (mavjud logotiplar)
    show_in_grid    TINYINT(1) NOT NULL DEFAULT 1,   -- "Mijozlar va hamkorlar" bloki
    show_in_marquee TINYINT(1) NOT NULL DEFAULT 0,   -- bosh sahifadagi yuguruvchi qator
    sort_order      INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_clients_logo FOREIGN KEY (logo_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE testimonials (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author          VARCHAR(120) NOT NULL,
    company         VARCHAR(120) NOT NULL DEFAULT '',
    text_uz         TEXT NOT NULL,
    text_ru         TEXT NOT NULL,
    rating          TINYINT UNSIGNED NOT NULL DEFAULT 5,
    is_published    TINYINT(1) NOT NULL DEFAULT 1,
    sort_order      INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE case_studies (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    metric_uz       VARCHAR(40) NOT NULL,
    metric_ru       VARCHAR(40) NOT NULL DEFAULT '',
    client_name     VARCHAR(120) NOT NULL,
    text_uz         TEXT NOT NULL,
    text_ru         TEXT NOT NULL,
    is_published    TINYINT(1) NOT NULL DEFAULT 1,
    sort_order      INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE team_members (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120) NOT NULL,
    role_uz         VARCHAR(160) NOT NULL,
    role_ru         VARCHAR(160) NOT NULL DEFAULT '',
    photo_id        INT UNSIGNED NULL,
    placeholder     VARCHAR(10) NOT NULL DEFAULT '',   -- rasm bo'lmasa ko'rsatiladigan belgi
    accent_color    VARCHAR(9) NOT NULL DEFAULT '#16a34a',
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    sort_order      INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_team_photo FOREIGN KEY (photo_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stats (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_key   VARCHAR(40) NOT NULL,     -- home_hero | home_counters | about_counters
    value       INT NOT NULL DEFAULT 0,
    suffix      VARCHAR(10) NOT NULL DEFAULT '',
    label_uz    VARCHAR(160) NOT NULL,
    label_ru    VARCHAR(160) NOT NULL DEFAULT '',
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    sort_order  INT NOT NULL DEFAULT 0,
    KEY idx_stats_group (group_key, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE districts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug            VARCHAR(60) NOT NULL,
    number          VARCHAR(4) NOT NULL,
    name_uz         VARCHAR(120) NOT NULL,     -- to'liq: "Arnasoy tumani"
    name_ru         VARCHAR(120) NOT NULL DEFAULT '',
    short_uz        VARCHAR(80) NOT NULL,      -- xarita va ro'yxat: "Arnasoy"
    short_ru        VARCHAR(80) NOT NULL DEFAULT '',
    description_uz  TEXT NOT NULL,             -- bo'sh bo'lsa umumiy matn ko'rsatiladi
    description_ru  TEXT NOT NULL,
    marker_x        SMALLINT NOT NULL DEFAULT 0,
    marker_y        SMALLINT NOT NULL DEFAULT 0,
    label_x         SMALLINT NOT NULL DEFAULT 0,
    label_y         SMALLINT NOT NULL DEFAULT 0,
    label_left      TINYINT(1) NOT NULL DEFAULT 0,
    is_city         TINYINT(1) NOT NULL DEFAULT 0,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    sort_order      INT NOT NULL DEFAULT 0,    -- ro'yxatdagi tartib
    map_order       INT NOT NULL DEFAULT 0,    -- xaritada chizilish tartibi
    UNIQUE KEY uq_district_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------- Arizalar
CREATE TABLE applications (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120) NOT NULL,
    phone           VARCHAR(40) NOT NULL,
    service         VARCHAR(160) NOT NULL DEFAULT '',   -- yuborilgan paytdagi xizmat nomi
    service_id      INT UNSIGNED NULL,
    message         TEXT NULL,
    status          VARCHAR(20) NOT NULL DEFAULT 'new',  -- new | contacted | in_progress | won | lost | spam
    lang            VARCHAR(2) NOT NULL DEFAULT 'uz',
    ip_address      VARCHAR(45) NULL,
    user_agent      VARCHAR(255) NULL,
    telegram_sent   TINYINT(1) NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_app_status (status, created_at),
    KEY idx_app_created (created_at),
    KEY idx_app_ip (ip_address, created_at),
    CONSTRAINT fk_app_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE application_events (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id  INT UNSIGNED NOT NULL,
    admin_id        INT UNSIGNED NULL,
    type            VARCHAR(20) NOT NULL,      -- status | note | telegram
    from_status     VARCHAR(20) NULL,
    to_status       VARCHAR(20) NULL,
    note            TEXT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_events_app (application_id, created_at),
    CONSTRAINT fk_events_app FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    CONSTRAINT fk_events_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

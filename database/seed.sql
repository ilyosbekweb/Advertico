-- ADVERTICO — boshlang'ich kontent (asl saytdan ko'chirilgan).
-- schema.sql dan keyin import qiling.

SET NAMES utf8mb4;

INSERT INTO media (`id`, `path`, `original_name`, `mime`, `size`, `width`, `height`, `alt_uz`, `alt_ru`, `is_system`) VALUES
(1, 'assets/billboard-catalog.png', 'billboard-catalog.png', 'image/png', 192302, 606, 756, 'Billboard 6×3 m', 'Билборд 6×3 м', 1),
(2, 'assets/led-catalog.png', 'led-catalog.png', 'image/png', 309498, 606, 756, 'LED monitor', 'LED-экран', 1),
(3, 'assets/cityboard-catalog.png', 'cityboard-catalog.png', 'image/png', 206401, 606, 756, 'Cityboard 3,5×2,5 m', 'Ситиборд 3,5×2,5 м', 1),
(4, 'assets/roof-catalog.png', 'roof-catalog.png', 'image/png', 380974, 606, 756, 'Tom va fasad brendlash', 'Брендирование крыши и фасада', 1),
(5, 'assets/letters-catalog.png', 'letters-catalog.png', 'image/png', 360445, 606, 756, 'Katta hajmli harflar', 'Крупноформатные буквы', 1),
(6, 'assets/bus-stops-catalog.png', 'bus-stops-catalog.png', 'image/png', 279230, 606, 756, 'Avtobus bekatlarini to‘liq brendlash', 'Полное брендирование автобусных остановок', 1),
(7, 'assets/bus-branding-catalog.png', 'bus-branding-catalog.png', 'image/png', 320277, 606, 756, 'Avtobuslar uchun tashqi reklama', 'Наружная реклама на автобусах', 1),
(8, 'assets/underground-catalog.png', 'underground-catalog.png', 'image/png', 286318, 606, 756, 'Yerosti yo‘li tashqi reklamasi', 'Реклама в подземном переходе', 1),
(9, 'assets/team/dizayner.jpg', 'dizayner.jpg', 'image/jpeg', 34089, 640, 640, 'Advertico dizayneri', 'Дизайнер Advertico', 1),
(10, 'assets/team/montajor.jpg', 'montajor.jpg', 'image/jpeg', 47455, 640, 640, 'Advertico montaj ustasi', 'Монтажник Advertico', 1),
(11, 'assets/team/menejer.jpg', 'menejer.jpg', 'image/jpeg', 114701, 640, 640, 'Shahriyor Yorbekov, Advertico marketologi', 'Шахриёр Ёрбеков, маркетолог Advertico', 1);

INSERT INTO texts (`key`, `page`, `section`, `label`, `type`, `uz`, `ru`, `sort_order`) VALUES
('common.nav.home', 'common', 'Menyu', 'Menyu: Bosh sahifa', 'text', 'Bosh sahifa', 'Главная', 10),
('common.nav.about', 'common', 'Menyu', 'Menyu: Biz haqimizda', 'text', 'Biz haqimizda', 'О нас', 20),
('common.nav.services', 'common', 'Menyu', 'Menyu: Xizmatlar', 'text', 'Xizmatlar', 'Услуги', 30),
('common.nav.portfolio', 'common', 'Menyu', 'Menyu: Portfolio', 'text', 'Ishlar va mijozlar', 'Портфолио и клиенты', 40),
('common.nav.locations', 'common', 'Menyu', 'Menyu: Lokatsiyalar', 'text', 'Lokatsiyalar', 'Локации', 50),
('common.nav.contact', 'common', 'Menyu', 'Menyu: Aloqa', 'text', 'Aloqa', 'Контакты', 60),
('common.loader.caption', 'common', 'Yuklanish ekrani', 'Yuklanish ekranidagi yozuv (bosh sahifa)', 'text', 'JIZZAX · OUTDOOR MEDIA', '', 70),
('common.footer.copyright', 'common', 'Footer', 'Footer: mualliflik qatori', 'text', '© 2026 Advertico · Jizzax, Oʻzbekiston · ', '', 80),
('common.footer.tagline', 'common', 'Footer', 'Footer: shior', 'text', 'Tashqi reklama agentligi', 'Агентство наружной рекламы', 90),
('common.project_modal.title', 'common', 'Loyiha oynasi', 'Loyiha oynasi: standart sarlavha', 'text', 'Loyiha', '', 100),
('common.project_modal.text', 'common', 'Loyiha oynasi', 'Loyiha oynasi: standart tavsif', 'textarea', 'Loyiha tafsilotlari — haqiqiy foto va maʼlumotlar bilan toʻldiring.', 'Детали проекта — дополните реальными фото и данными.', 110),
('common.project_modal.button', 'common', 'Loyiha oynasi', 'Loyiha oynasi: tugma', 'text', 'Shunday loyiha kerak', 'Хочу такой проект', 120),
('common.service_modal.kicker', 'common', 'Xizmat oynasi', 'Xizmat oynasi: ustki yozuv', 'text', 'ADVERTICO · XIZMATLAR KATALOGI', 'ADVERTICO · КАТАЛОГ УСЛУГ', 130),
('common.service_modal.prices', 'common', 'Xizmat oynasi', 'Xizmat oynasi: narxlar sarlavhasi', 'text', 'Katalog narxlari', 'Цены по каталогу', 140),
('common.service_modal.button', 'common', 'Xizmat oynasi', 'Xizmat oynasi: tugma', 'text', 'Buyurtma berish', 'Оставить заявку', 150),
('home.hero.eyebrow', 'home', 'Hero', 'Hero: ustki yozuv', 'text', 'JIZZAX · OʻZBEKISTON · 2011-YILDAN', 'ДЖИЗАК · УЗБЕКИСТАН · С 2011 ГОДА', 160),
('home.hero.line1', 'home', 'Hero', 'Hero: sarlavha 1-qator', 'text', 'KATTA FORMAT.', 'БОЛЬШОЙ ФОРМАТ.', 170),
('home.hero.line2', 'home', 'Hero', 'Hero: sarlavha 2-qator (kontur)', 'text', 'KUCHLI TAʼSIR', 'МОЩНЫЙ ЭФФЕКТ', 180),
('home.hero.description', 'home', 'Hero', 'Hero: tavsif', 'textarea', 'LED ekranlar, billboardlar, 3D harflar va transport reklamasi — gʻoyadan oʻrnatishgacha bitta jamoa.', 'LED-экраны, билборды, объёмные буквы и реклама на транспорте — от идеи до монтажа одной командой.', 190),
('home.hero.button_primary', 'home', 'Hero', 'Hero: asosiy tugma', 'text', 'Loyihani boshlash', 'Начать проект', 200),
('home.hero.button_secondary', 'home', 'Hero', 'Hero: ikkinchi tugma', 'text', 'Ishlarimiz', 'Наши работы', 210),
('home.art.letter', 'home', 'Hero bezagi', 'Hero bezagi: katta harf', 'text', 'A', '', 220),
('home.art.line1', 'home', 'Hero bezagi', 'Hero bezagi: 1-qator', 'text', 'OUTDOOR', '', 230),
('home.art.line2', 'home', 'Hero bezagi', 'Hero bezagi: 2-qator', 'text', 'MEDIA', '', 240),
('home.art.line3', 'home', 'Hero bezagi', 'Hero bezagi: 3-qator (qalin)', 'text', '01 — 26', '', 250),
('home.art.index', 'home', 'Hero bezagi', 'Hero bezagi: pastki yozuv', 'text', 'ADVERTICO / JIZZAX', '', 260),
('home.services.title', 'home', 'Xizmatlar bloki', 'Xizmatlar bloki: sarlavha', 'text', 'Asosiy xizmatlar', 'Ключевые услуги', 270),
('home.cta.title', 'home', 'Chaqiruv bloki', 'Chaqiruv bloki: sarlavha', 'text', 'Kampaniyani bugun boshlaymiz', 'Запускаем кампанию уже сегодня', 280),
('home.cta.text', 'home', 'Chaqiruv bloki', 'Chaqiruv bloki: matn', 'textarea', 'Bepul maslahat va smeta — 1 ish kuni ichida.', 'Бесплатная консультация и смета — в течение 1 рабочего дня.', 290),
('home.cta.button', 'home', 'Chaqiruv bloki', 'Chaqiruv bloki: tugma', 'text', 'Bogʻlanish', 'Связаться', 300),
('about.title', 'about', 'Hikoya', 'Sarlavha', 'text', 'Advertico hikoyasi', 'История Advertico', 310),
('about.story.p1', 'about', 'Hikoya', '1-xatboshi (bo‘sh qoldirilsa ko‘rsatilmaydi)', 'textarea', '“ADVERTICO” – Jizzax viloyatidagi birinchi va eng yirik tashqi reklama operatoridir. Kompaniyamiz oʻz faoliyatini 2011-yilda bir nechta billboardlarni joylashtirish bilan boshlagan.', '«ADVERTICO» — первый и крупнейший оператор наружной рекламы в Джизакской области. Компания начала свою деятельность в 2011 году с размещения нескольких билбордов.', 320),
('about.story.p2', 'about', 'Hikoya', '2-xatboshi (bo‘sh qoldirilsa ko‘rsatilmaydi)', 'textarea', 'Bugungi kunda biz 100 dan ortiq billboardlar, 20 dan ziyod brandmauerlar (katta hajmli konstruksiyalar), 10 dan ortiq zamonaviy LED ekranlar hamda sitibokslar, bekat brendinglari va jamoat transportidagi reklama obyektlarini boshqaruvchi yirik, malakali jamoaga aylanganmiz. Hududiy qamrovimiz Jizzax viloyati bilan cheklanmay, butun Respublikamiz boʻylab kengayib bormoqda.', 'Сегодня мы стали крупной квалифицированной командой, управляющей более чем 100 билбордами, свыше 20 брендмауэрами (крупноформатными конструкциями), более чем 10 современными LED-экранами, а также ситибоксами, брендингом остановок и рекламными объектами на общественном транспорте. География нашей деятельности не ограничивается Джизакской областью и расширяется по всей Республике Узбекистан.', 330),
('about.story.p3', 'about', 'Hikoya', '3-xatboshi (bo‘sh qoldirilsa ko‘rsatilmaydi)', 'textarea', 'Shuningdek, kompaniyamizda turli reklama vositalari va materiallarini ishlab chiqarish yoʻlga qoʻyilgan. Katta hajmdagi bosma mahsulotlar (banner, orakal), boʻrtma harflar va boshqa reklama materiallari uchun alohida ishlab chiqarish sexi hamda dizayn studiyasi faoliyat yuritadi.', 'Кроме того, компания наладила производство различных рекламных конструкций и материалов. Для изготовления крупноформатной печатной продукции (баннеров, оракала), объёмных букв и других рекламных материалов работают отдельный производственный цех и дизайн-студия.', 340),
('about.story.p4', 'about', 'Hikoya', '4-xatboshi (bo‘sh qoldirilsa ko‘rsatilmaydi)', 'textarea', 'Zamonaviy biznes tendensiyalaridan kelib chiqib, onlayn reklama xizmatlarini ham taklif qilamiz. Ular qatoriga media joylashtirish, brendlashtirish, ijtimoiy tarmoqlar uchun kontent ishlab chiqish va uni yetakchi platformalarda samarali joylashtirish kiradi.', 'Следуя современным тенденциям бизнеса, мы также предлагаем услуги онлайн-рекламы: размещение медиа, брендинг, создание контента для социальных сетей и его эффективное продвижение на ведущих платформах.', 350),
('about.story.p5', 'about', 'Hikoya', '5-xatboshi (bo‘sh qoldirilsa ko‘rsatilmaydi)', 'textarea', 'Bugungi kunga qadar Oʻzbekiston boʻylab turli sohalarda faoliyat yuritayotgan 500 dan ortiq biznes egalariga kreativ, natijaga yoʻnaltirilgan reklama yechimlarini taqdim etdik. Har bir mijozga individual yondashib, ularning brendi, mahsuloti yoki xizmatini keng auditoriyaga yetkazishda ishonchli hamkor boʻlib kelmoqdamiz.', 'На сегодняшний день мы предложили креативные и ориентированные на результат рекламные решения более чем 500 владельцам бизнеса из разных отраслей по всему Узбекистану. Мы индивидуально подходим к каждому клиенту и остаёмся надёжным партнёром в продвижении его бренда, продукта или услуги широкой аудитории.', 360),
('about.mission.icon', 'about', 'Missiya', 'Missiya: belgi', 'text', '◎', '', 370),
('about.mission.title', 'about', 'Missiya', 'Missiya: sarlavha', 'text', 'Missiya', 'Миссия', 380),
('about.mission.text', 'about', 'Missiya', 'Missiya: matn', 'textarea', 'Mahalliy bizneslarga koʻrinadigan va oʻlchanadigan reklama berish.', 'Давать местному бизнесу заметную и измеримую рекламу.', 390),
('about.vision.icon', 'about', 'Vizyon', 'Vizyon: belgi', 'text', '◉', '', 400),
('about.vision.title', 'about', 'Vizyon', 'Vizyon: sarlavha', 'text', 'Vizyon', 'Видение', 410),
('about.vision.text', 'about', 'Vizyon', 'Vizyon: matn', 'textarea', 'Markaziy Osiyoning eng ishonchli tashqi reklama brendi boʻlish.', 'Стать самым надёжным брендом наружной рекламы в Центральной Азии.', 420),
('about.values.icon', 'about', 'Qadriyatlar', 'Qadriyatlar: belgi', 'text', '✦', '', 430),
('about.values.title', 'about', 'Qadriyatlar', 'Qadriyatlar: sarlavha', 'text', 'Qadriyatlar', 'Ценности', 440),
('about.values.text', 'about', 'Qadriyatlar', 'Qadriyatlar: matn', 'textarea', 'Shaffoflik, sifat, muddatga rioya.', 'Прозрачность, качество, соблюдение сроков.', 450),
('about.team.title', 'about', 'Jamoa', 'Jamoa: sarlavha', 'text', 'Jamoa', 'Команда', 460),
('about.team.note', 'about', 'Jamoa', 'Jamoa: izoh (bo‘sh qoldirilsa ko‘rsatilmaydi)', 'text', 'Namuna maʼlumotlar — haqiqiy jamoa bilan almashtiring.', 'Образец данных — замените реальной командой.', 470),
('services.title', 'services', 'Sahifa', 'Sarlavha', 'text', 'Xizmatlar katalogi', 'Каталог услуг', 480),
('portfolio.title', 'portfolio', 'Portfolio', 'Sarlavha', 'text', 'Portfolio', 'Портфолио', 490),
('portfolio.filter_all', 'portfolio', 'Portfolio', 'Filtr: barchasi', 'text', 'Barchasi', 'Все', 500),
('portfolio.clients_title', 'portfolio', 'Mijozlar', 'Mijozlar: sarlavha', 'text', 'Mijozlar va hamkorlar', 'Клиенты и партнёры', 510),
('portfolio.testimonials_title', 'portfolio', 'Fikrlar', 'Fikrlar: sarlavha', 'text', 'Fikrlar', 'Отзывы', 520),
('portfolio.cases_title', 'portfolio', 'Keyslar', 'Keyslar: sarlavha', 'text', 'Keyslar', 'Кейсы', 530),
('portfolio.note', 'portfolio', 'Keyslar', 'Keyslar: izoh (bo‘sh qoldirilsa ko‘rsatilmaydi)', 'text', 'Namuna maʼlumotlar — haqiqiy mijozlar bilan almashtiring.', 'Образец данных — замените реальными клиентами.', 540),
('locations.eyebrow', 'locations', 'Sarlavha', 'Ustki yozuv', 'text', 'HUDUDIY QAMROV', 'ГЕОГРАФИЯ РАБОТЫ', 550),
('locations.title', 'locations', 'Sarlavha', 'Sarlavha', 'text', 'Jizzax viloyati xaritasi', 'Карта Джизакской области', 560),
('locations.intro', 'locations', 'Sarlavha', 'Kirish matni', 'textarea', 'Jizzax viloyati va tumanlarining haqiqiy maʼmuriy chegaralari xaritada. Tumanni tanlab, hudud bo‘yicha maʼlumot oling.', 'На карте показаны административные границы Джизакской области и районов. Выберите район, чтобы узнать подробнее о территории.', 570),
('locations.badge_number', 'locations', 'Sarlavha', 'Nishon: raqam', 'text', '12', '', 580),
('locations.badge_unit', 'locations', 'Sarlavha', 'Nishon: birlik', 'text', 'tuman', 'районов', 590),
('locations.badge_sub', 'locations', 'Sarlavha', 'Nishon: pastki yozuv', 'text', 'va Jizzax shahri', 'и город Джизак', 600),
('locations.map_label', 'locations', 'Xarita', 'Xarita: chap yuqori yozuv', 'text', 'JIZZAX VILOYATI', 'ДЖИЗАКСКАЯ ОБЛАСТЬ', 610),
('locations.map_schematic', 'locations', 'Xarita', 'Xarita: o‘ng yuqori yozuv', 'text', 'MAʼMURIY CHEGARALAR', 'АДМИНИСТРАТИВНЫЕ ГРАНИЦЫ', 620),
('locations.map_title', 'locations', 'Xarita', 'Xarita: nomi (ekran o‘quvchilar uchun)', 'text', 'Jizzax viloyati maʼmuriy xaritasi', 'Административная карта Джизакской области', 630),
('locations.map_desc', 'locations', 'Xarita', 'Xarita: tavsif (ekran o‘quvchilar uchun)', 'textarea', 'Ochiq geografik maʼlumotlar asosida chizilgan Jizzax viloyati va tumanlarining maʼmuriy chegaralari.', 'Административные границы Джизакской области и районов на основе открытых географических данных.', 640),
('locations.map_scale', 'locations', 'Xarita', 'Xarita: masshtab yozuvi', 'text', 'Viloyat va tuman chegaralari', 'Границы области и районов', 650),
('locations.legend_district', 'locations', 'Xarita', 'Xarita: tuman belgisi', 'text', 'Tuman belgisi', 'Обозначение района', 660),
('locations.legend_city', 'locations', 'Xarita', 'Xarita: shahar belgisi', 'text', 'Jizzax shahri', 'Город Джизак', 670),
('locations.legend_borders', 'locations', 'Xarita', 'Xarita: chegaralar izohi', 'text', 'Tumanlar maʼmuriy chegaralari', 'Административные границы районов', 680),
('locations.panel_eyebrow', 'locations', 'Hudud paneli', 'Panel: ustki yozuv', 'text', 'TANLANGAN HUDUD', 'ВЫБРАННАЯ ТЕРРИТОРИЯ', 690),
('locations.panel_title', 'locations', 'Hudud paneli', 'Panel: standart sarlavha', 'text', 'Jizzax viloyati', 'Джизакская область', 700),
('locations.panel_hint', 'locations', 'Hudud paneli', 'Panel: standart matn', 'textarea', 'Xaritadagi belgini yoki tuman nomini tanlang — hudud haqida maʼlumot shu yerda ko‘rsatiladi.', 'Выберите метку на карте или название района — здесь появится информация о территории.', 710),
('locations.district_default', 'locations', 'Hudud paneli', 'Tuman tavsifi (tumanga alohida tavsif yozilmagan bo‘lsa)', 'textarea', 'Ushbu hudud bo‘yicha reklama formatlari va mavjud joylar haqida batafsil maʼlumot olish uchun biz bilan bog‘laning.', 'Свяжитесь с нами, чтобы узнать о рекламных форматах и доступных местах в этом районе.', 720),
('locations.panel_button', 'locations', 'Hudud paneli', 'Panel: tugma', 'text', 'Hudud bo‘yicha bog‘lanish', 'Связаться по региону', 730),
('locations.office_label', 'locations', 'Ofis kartasi', 'Ofis kartasi: ustki yozuv', 'text', 'TASHRIF BUYURING', 'ЖДЁМ ВАС В ОФИСЕ', 740),
('locations.office_title', 'locations', 'Ofis kartasi', 'Ofis kartasi: sarlavha', 'text', 'Advertico ofisi · Jizzax', 'Офис Advertico · Джизак', 750),
('locations.office_link', 'locations', 'Ofis kartasi', 'Ofis kartasi: havola matni', 'text', 'Ofis manzilini xaritada ochish', 'Открыть адрес офиса на карте', 760),
('contact.form_title', 'contact', 'Forma', 'Forma: sarlavha', 'text', 'Bogʻlanish', 'Связаться с нами', 770),
('contact.label_name', 'contact', 'Forma', 'Forma: ism', 'text', 'Ism', 'Имя', 780),
('contact.label_phone', 'contact', 'Forma', 'Forma: telefon', 'text', 'Telefon', 'Телефон', 790),
('contact.phone_placeholder', 'contact', 'Forma', 'Forma: telefon namunasi', 'text', '+998', '', 800),
('contact.label_service', 'contact', 'Forma', 'Forma: xizmat', 'text', 'Xizmat', 'Услуга', 810),
('contact.label_message', 'contact', 'Forma', 'Forma: xabar', 'text', 'Xabar', 'Сообщение', 820),
('contact.submit', 'contact', 'Forma', 'Forma: tugma', 'text', 'Ariza qoldirish', 'Оставить заявку', 830),
('contact.msg_sending', 'contact', 'Forma xabarlari', 'Forma: yuborilmoqda', 'text', 'Yuborilmoqda...', 'Отправка...', 840),
('contact.msg_success', 'contact', 'Forma xabarlari', 'Forma: muvaffaqiyatli', 'textarea', 'Murojaatingiz qabul qilindi. Tez orada siz bilan bog‘lanamiz.', 'Ваша заявка принята. Мы скоро свяжемся с вами.', 850),
('contact.msg_error', 'contact', 'Forma xabarlari', 'Forma: xatolik', 'text', 'Yuborishda xatolik yuz berdi.', 'Не удалось отправить заявку.', 860),
('contact.msg_network', 'contact', 'Forma xabarlari', 'Forma: server bilan aloqa yo‘q', 'text', 'Server bilan bog‘lanib bo‘lmadi. Keyinroq urinib ko‘ring.', 'Не удалось связаться с сервером. Попробуйте позже.', 870),
('contact.office_label', 'contact', 'Kontakt kartalari', 'Ofis kartasi: ustki yozuv', 'text', 'BIZNING OFIS', 'НАШ ОФИС', 880),
('contact.office_title', 'contact', 'Kontakt kartalari', 'Ofis kartasi: manzil', 'text', 'Jizzax, Oʻzbekiston', 'Джизак, Узбекистан', 890),
('contact.office_link', 'contact', 'Kontakt kartalari', 'Ofis kartasi: havola matni', 'text', 'Xaritada ochish', 'Открыть на карте', 900),
('contact.phone_label', 'contact', 'Kontakt kartalari', 'Telefon kartasi: ustki yozuv', 'text', 'QOʻNGʻIROQ QILING', 'ПОЗВОНИТЕ НАМ', 910),
('contact.phone_link', 'contact', 'Kontakt kartalari', 'Telefon kartasi: havola matni', 'text', 'Telefon orqali bogʻlanish', 'Позвонить', 920),
('contact.instagram_label', 'contact', 'Kontakt kartalari', 'Instagram kartasi: ustki yozuv', 'text', 'INSTAGRAM', 'INSTAGRAM', 930),
('contact.instagram_link', 'contact', 'Kontakt kartalari', 'Instagram kartasi: havola matni', 'text', 'Sahifamizni kuzating', 'Подписывайтесь на нас', 940);

INSERT INTO settings (`key`, `value`) VALUES
('phone', '+998 93 307 33 33'),
('instagram_url', 'https://www.instagram.com/advertico.uz/'),
('instagram_handle', '@advertico.uz'),
('map_url', 'https://maps.app.goo.gl/nkShhnYp1gjqPGTU6'),
('telegram_enabled', '1'),
('telegram_chat_id', ''),
('rate_limit_per_hour', '5');

INSERT INTO seo_pages (`page_key`, `label`, `title_uz`, `title_ru`, `description_uz`, `description_ru`, `og_image_id`, `noindex`) VALUES
('home', 'Bosh sahifa', 'Advertico — Tashqi reklama / Наружная реклама, Jizzax', 'Advertico — Наружная реклама, Джизак', 'Advertico — Jizzax viloyatidagi birinchi va eng yirik tashqi reklama operatori. Billboardlar, LED ekranlar, siti-bordlar, 3D harflar va transport reklamasi.', 'Advertico — первый и крупнейший оператор наружной рекламы в Джизакской области. Билборды, LED-экраны, ситиборды, объёмные буквы и реклама на транспорте.', 1, 0),
('about', 'Biz haqimizda', 'Advertico — Biz haqimizda / Наружная реклама, Jizzax', 'Advertico — О нас, Джизак', '2011-yildan beri Jizzaxda tashqi reklama: 100+ billboard, 20+ brandmauer, 10+ LED ekran, ishlab chiqarish sexi va dizayn studiyasi.', 'Наружная реклама в Джизаке с 2011 года: 100+ билбордов, 20+ брендмауэров, 10+ LED-экранов, производственный цех и дизайн-студия.', NULL, 0),
('services', 'Xizmatlar', 'Advertico — Xizmatlar / Услуги, Jizzax', 'Advertico — Услуги, Джизак', 'Billboard, LED monitor, siti-bord, tom brendlash, 3D harflar, bekat va avtobus reklamasi — katalog narxlari bilan.', 'Билборды, LED-экраны, ситиборды, брендирование крыш, объёмные буквы, реклама на остановках и автобусах — с ценами по каталогу.', NULL, 0),
('portfolio', 'Ishlar va mijozlar', 'Advertico — Ishlar va mijozlar, Jizzax', 'Advertico — Портфолио и клиенты, Джизак', 'Advertico bajargan loyihalar, mijozlar, fikrlar va natijalar.', 'Проекты Advertico, клиенты, отзывы и результаты.', NULL, 0),
('locations', 'Lokatsiyalar', 'Advertico — Lokatsiyalar / Локации, Jizzax', 'Advertico — Локации, Джизак', 'Jizzax viloyatining 12 tumani va Jizzax shahrida reklama joylari xaritasi.', 'Карта рекламных мест в 12 районах Джизакской области и городе Джизак.', NULL, 0),
('contact', 'Aloqa', 'Advertico — Aloqa / Наружная реклама, Jizzax', 'Advertico — Контакты, Джизак', 'Advertico bilan bog‘laning: +998 93 307 33 33. Bepul maslahat va smeta — 1 ish kuni ichida.', 'Свяжитесь с Advertico: +998 93 307 33 33. Бесплатная консультация и смета — в течение 1 рабочего дня.', NULL, 0);

INSERT INTO services (`id`, `slug`, `number`, `name_uz`, `name_ru`, `title_uz`, `title_ru`, `description_uz`, `description_ru`, `home_price_uz`, `home_price_ru`, `list_price_uz`, `list_price_ru`, `prices`, `image_id`, `show_on_home`, `is_active`, `sort_order`) VALUES
(1, 'billboard', '01', 'Billboardlar', 'Билборды', 'Billboard 6×3 m', 'Билборд 6×3 м', 'Katalogdagi 18 m² billboard taklifi.', 'Билборд площадью 18 м² по каталогу.', '1 500 000 soʻm/oy', '1 500 000 сум/мес', '1 500 000 UZS / oy', '1 500 000 UZS / мес.', '[{"label_uz":"1 oylik ijara","label_ru":"Аренда за 1 месяц","value_uz":"1 500 000 UZS","value_ru":"1 500 000 UZS"},{"label_uz":"Bosma (18 m²)","label_ru":"Печать (18 м²)","value_uz":"550 000 UZS","value_ru":"550 000 UZS"},{"label_uz":"Montaj xizmati","label_ru":"Монтаж","value_uz":"250 000 UZS","value_ru":"250 000 UZS"}]', 1, 1, 1, 10),
(2, 'led', '02', 'LED monitorlar', 'LED-экраны', 'LED monitor', 'LED-экран', '1 oylik ijara narxi rolik davomiyligiga qarab belgilanadi.', 'Цена аренды за 1 месяц зависит от длительности ролика.', '5 sek. — 1 500 000 soʻm/oy', '5 сек. — 1 500 000 сум/мес', '5 sek. dan 1 500 000 UZS / oy', 'от 5 сек. — 1 500 000 UZS / мес.', '[{"label_uz":"5 sekundlik rolik \\/ oy","label_ru":"Ролик 5 секунд \\/ мес.","value_uz":"1 500 000 UZS","value_ru":"1 500 000 UZS"},{"label_uz":"10 sekundlik rolik \\/ oy","label_ru":"Ролик 10 секунд \\/ мес.","value_uz":"2 000 000 UZS","value_ru":"2 000 000 UZS"},{"label_uz":"15 sekundlik rolik \\/ oy","label_ru":"Ролик 15 секунд \\/ мес.","value_uz":"2 500 000 UZS","value_ru":"2 500 000 UZS"},{"label_uz":"20 sekundlik rolik \\/ oy","label_ru":"Ролик 20 секунд \\/ мес.","value_uz":"3 000 000 UZS","value_ru":"3 000 000 UZS"}]', 2, 1, 1, 20),
(3, 'cityboard', '03', 'Siti-bordlar', 'Ситиборды', 'Cityboard 3,5×2,5 m', 'Ситиборд 3,5×2,5 м', 'Lightbox formatidagi cityboard, 8,75 m².', 'Ситиборд в формате lightbox, 8,75 м².', '1 500 000 soʻm/oy', '1 500 000 сум/мес', '1 500 000 UZS / oy', '1 500 000 UZS / мес.', '[{"label_uz":"1 oylik ijara","label_ru":"Аренда за 1 месяц","value_uz":"1 500 000 UZS","value_ru":"1 500 000 UZS"},{"label_uz":"Bosma (8,75 m²)","label_ru":"Печать (8,75 м²)","value_uz":"1 350 000 UZS","value_ru":"1 350 000 UZS"},{"label_uz":"Montaj xizmati","label_ru":"Монтаж","value_uz":"150 000 UZS","value_ru":"150 000 UZS"}]', 3, 1, 1, 30),
(4, 'roof', '04', 'Tom brendlash', 'Брендирование крыш', 'Tom va fasad brendlash', 'Брендирование крыши и фасада', 'Katalogdagi brendmauerlar: bino yon tomoni yoki tom qismiga joylashtiriladigan reklama.', 'Брендмауэры по каталогу: реклама на фасаде или крыше здания.', 'Kelishuv asosida', 'По договорённости', 'Kelishuv asosida', 'По договорённости', '[{"label_uz":"1 oylik ijara","label_ru":"Аренда за 1 месяц","value_uz":"Kelishuv asosida","value_ru":"По договорённости"},{"label_uz":"Bosma (1 m²)","label_ru":"Печать (1 м²)","value_uz":"35 000 UZS","value_ru":"35 000 UZS"},{"label_uz":"Montaj (1 m²)","label_ru":"Монтаж (1 м²)","value_uz":"20 000 UZS","value_ru":"20 000 UZS"}]', 4, 1, 1, 40),
(5, 'letters', '05', '3D yoritilgan harflar', 'Объёмные световые буквы', 'Katta hajmli harflar', 'Крупноформатные буквы', 'Katta yo‘l yoqasi yoki ko‘p qavatli bino tom qismiga o‘rnatiladigan hajmli harflar.', 'Объёмные буквы у крупных дорог или на крышах многоэтажных зданий.', 'Kelishuv asosida', 'По договорённости', 'Kelishuv asosida', 'По договорённости', '[{"label_uz":"1 oylik ijara","label_ru":"Аренда за 1 месяц","value_uz":"Kelishuv asosida","value_ru":"По договорённости"},{"label_uz":"Bosma narxi","label_ru":"Стоимость изготовления","value_uz":"Kelishuv asosida","value_ru":"По договорённости"},{"label_uz":"Montaj xizmati","label_ru":"Монтаж","value_uz":"Kelishuv asosida","value_ru":"По договорённости"}]', 5, 1, 1, 50),
(6, 'bus-stops', '06', 'Avtobus bekatlari', 'Автобусные остановки', 'Avtobus bekatlarini to‘liq brendlash', 'Полное брендирование автобусных остановок', 'Bekat konstruksiyasini to‘liq brendlash bo‘yicha katalog taklifi.', 'Брендирование всей конструкции остановки по каталогу.', '1 800 000 soʻm/oy', '1 800 000 сум/мес', '1 800 000 UZS / oy', '1 800 000 UZS / мес.', '[{"label_uz":"1 oylik ijara","label_ru":"Аренда за 1 месяц","value_uz":"1 800 000 UZS","value_ru":"1 800 000 UZS"},{"label_uz":"Bosma narxi","label_ru":"Стоимость печати","value_uz":"Kelishuv asosida","value_ru":"По договорённости"},{"label_uz":"Montaj xizmati","label_ru":"Монтаж","value_uz":"Kelishuv asosida","value_ru":"По договорённости"}]', 6, 1, 1, 60),
(7, 'bus-branding', '07', 'Avtobus brendlash', 'Брендирование автобусов', 'Avtobuslar uchun tashqi reklama', 'Наружная реклама на автобусах', 'Avtobus kuzoviga reklama joylashtirish bo‘yicha katalog taklifi.', 'Размещение рекламы на автобусах по каталогу.', '1 800 000 soʻm/oy', '1 800 000 сум/мес', '1 800 000 UZS / oy', '1 800 000 UZS / мес.', '[{"label_uz":"1 oylik ijara","label_ru":"Аренда за 1 месяц","value_uz":"1 800 000 UZS","value_ru":"1 800 000 UZS"},{"label_uz":"Bosma narxi","label_ru":"Стоимость печати","value_uz":"650 000 UZS","value_ru":"650 000 UZS"},{"label_uz":"Montaj xizmati","label_ru":"Монтаж","value_uz":"1 000 000 UZS","value_ru":"1 000 000 UZS"}]', 7, 1, 1, 70),
(8, 'underpass', '08', 'Yerosti oʻtish joylari', 'Подземные переходы', 'Yerosti yo‘li tashqi reklamasi 240×120 sm', 'Реклама в подземном переходе 240×120 см', 'Katalogdagi reklama joyi o‘lchami 240×120 sm, bosma maydoni 2,88 m².', 'Размер рекламного места по каталогу — 240×120 см, площадь печати — 2,88 м².', '700 000 soʻm/oy', '700 000 сум/мес', '700 000 UZS / oy', '700 000 UZS / мес.', '[{"label_uz":"1 oylik ijara","label_ru":"Аренда за 1 месяц","value_uz":"700 000 UZS","value_ru":"700 000 UZS"},{"label_uz":"Bosma (2,88 m²)","label_ru":"Печать (2,88 м²)","value_uz":"500 000 UZS","value_ru":"500 000 UZS"},{"label_uz":"Qo‘shimcha","label_ru":"Дополнительно","value_uz":"Sxemalar \\/ Схемы","value_ru":"Sxemalar \\/ Схемы"}]', 8, 1, 1, 80);

INSERT INTO portfolio_categories (`id`, `slug`, `name_uz`, `name_ru`, `sort_order`) VALUES
(1, 'led', 'LED', 'LED', 10),
(2, 'bb', 'Billboardlar', 'Билборды', 20),
(3, 'tr', 'Transport', 'Транспорт', 30);

INSERT INTO portfolio_items (`category_id`, `title_uz`, `title_ru`, `description_uz`, `description_ru`, `accent_color`, `image_id`, `is_published`, `sort_order`) VALUES
(1, 'LED ekran — Markaziy chorraha', 'LED-экран — центральный перекрёсток', '', '', '#16a34a', NULL, 1, 10),
(2, 'Billboard — Toshkent yoʻli', 'Билборд — Ташкентская трасса', '', '', '#0d9488', NULL, 1, 20),
(3, 'Avtobus brendlash', 'Брендирование автобусов', '', '', '#65a30d', NULL, 1, 30),
(1, 'LED — Universitet hududi', 'LED — район университета', '', '', '#15803d', NULL, 1, 40),
(2, 'Siti-bord — Korzinka', 'Ситиборд — Korzinka', '', '', '#059669', NULL, 1, 50),
(3, 'Bekat reklamasi', 'Реклама на остановках', '', '', '#4d7c0f', NULL, 1, 60),
(2, '3D harflar — Savdo markazi', '3D-буквы — торговый центр', '', '', '#047857', NULL, 1, 70),
(1, 'LED — Bozor yoni', 'LED — у рынка', '', '', '#22c55e', NULL, 1, 80);

INSERT INTO clients (`name`, `logo_id`, `logo_svg`, `show_in_grid`, `show_in_marquee`, `sort_order`) VALUES
('TBC Bank', NULL, '<svg viewBox="0 0 240 88" role="img" aria-label="TBC Bank"><text x="120" y="53" text-anchor="middle" class="client-logo-tbc">TBC</text><text x="121" y="72" text-anchor="middle" class="client-logo-sub">BANK</text></svg>', 1, 0, 10),
('BRB Bank', NULL, '<svg viewBox="0 0 240 88" role="img" aria-label="BRB Bank"><path class="client-logo-brb-mark" d="M37 29h14v14H37zM56 29h14v14H56zM37 48h14v14H37zM56 48h14v14H56z"/><text x="82" y="55" class="client-logo-brb">BRB</text><text x="84" y="70" class="client-logo-sub">BANK</text></svg>', 1, 0, 20),
('AVO', NULL, '<svg viewBox="0 0 240 88" role="img" aria-label="AVO"><path class="client-logo-avo-mark" d="M48 22c4 8 4 12 0 20 8-4 12-4 20 0-4-8-4-12 0-20-8 4-12 4-20 0zM34 38c4 8 4 12 0 20 8-4 12-4 20 0-4-8-4-12 0-20-8 4-12 4-20 0z"/><text x="77" y="58" class="client-logo-avo">avo</text></svg>', 1, 0, 30),
('Uzum', NULL, '<svg viewBox="0 0 240 88" role="img" aria-label="Uzum"><path class="client-logo-uzum-mark" d="M48 19c2 11 5 15 16 17-11 2-14 5-16 17-2-12-5-15-16-17 11-2 14-6 16-17zM32 44c1 6 3 8 9 9-6 1-8 3-9 9-1-6-3-8-9-9 6-1 8-3 9-9z"/><text x="75" y="57" class="client-logo-uzum">uzum</text></svg>', 1, 0, 40),
('WaterPro', NULL, '<svg viewBox="0 0 240 88" role="img" aria-label="WaterPro"><path class="client-logo-water-mark" d="M51 17C43 30 33 40 33 51a18 18 0 0 0 36 0c0-11-10-21-18-34zm0 42a9 9 0 0 1-9-9c0-4 4-10 9-17 5 7 9 13 9 17a9 9 0 0 1-9 9z"/><text x="79" y="48" class="client-logo-water">WATER</text><text x="80" y="67" class="client-logo-water-sub">PRO</text></svg>', 1, 0, 50),
('Ultra Beton', NULL, '<svg viewBox="0 0 240 88" role="img" aria-label="Ultra Beton"><path class="client-logo-ultra-mark" d="M35 31 51 22l16 9v20l-16 9-16-9zm16-9v18m16-9-16 9-16-9m16 9v20"/><text x="78" y="47" class="client-logo-ultra">ULTRA</text><text x="79" y="67" class="client-logo-beton">BETON</text></svg>', 1, 0, 60),
('Nova Market', NULL, NULL, 0, 1, 70),
('Orient Group', NULL, NULL, 0, 1, 80),
('Zarafshon Auto', NULL, NULL, 0, 1, 90),
('Samo Telecom', NULL, NULL, 0, 1, 100),
('Bahor Foods', NULL, NULL, 0, 1, 110),
('Titan Build', NULL, NULL, 0, 1, 120),
('Silk Road Bank', NULL, NULL, 0, 1, 130),
('Edu Plus', NULL, NULL, 0, 1, 140);

INSERT INTO testimonials (`author`, `company`, `text_uz`, `text_ru`, `rating`, `is_published`, `sort_order`) VALUES
('Aziz R.', 'Nova Market', 'Natija 3 hafta ichida seziladi. Mijozlar oqimi oshdi.', 'Результат заметен через 3 недели. Поток клиентов вырос.', 5, 1, 10),
('Dilnoza K.', 'Edu Plus', 'Tez, aniq va sifatli oʻrnatish.', 'Быстро, чётко и качественно установили.', 5, 1, 20),
('Sardor T.', 'Titan Build', 'Narx va sifat nisbati ajoyib.', 'Отличное соотношение цены и качества.', 5, 1, 30);

INSERT INTO case_studies (`metric_uz`, `metric_ru`, `client_name`, `text_uz`, `text_ru`, `is_published`, `sort_order`) VALUES
('+38%', '+38%', 'Nova Market', 'Savdo 2 oyda 38% oshdi — LED + billboard.', 'Продажи выросли на 38% за 2 месяца — LED + билборды.', 1, 10),
('+2×', '+2×', 'Edu Plus', 'Qabul soʻrovlari ikki barobar koʻpaydi.', 'Количество заявок на набор выросло вдвое.', 1, 20),
('7 kun', '7 дней', 'Titan Build', 'Kampaniya 7 kunda ishga tushirildi.', 'Кампания запущена за 7 дней.', 1, 30);

INSERT INTO team_members (`name`, `role_uz`, `role_ru`, `photo_id`, `placeholder`, `accent_color`, `is_active`, `sort_order`) VALUES
('Zufar Askarov', 'Direktor', 'Директор', NULL, '01', '#16a34a', 1, 10),
('Bahodir Alixo''jayev', 'Dizayner', 'Дизайнер', 9, '02', '#0d9488', 1, 20),
('Ilyosbek Norboyev', 'Montaj ustasi va SMM mutaxassisi', 'Монтажник и SMM-специалист', 10, '03', '#65a30d', 1, 30),
('Shahriyor Yorbekov', 'Marketolog', 'Маркетолог', 11, '04', '#059669', 1, 40);

INSERT INTO stats (`group_key`, `value`, `suffix`, `label_uz`, `label_ru`, `is_active`, `sort_order`) VALUES
('home_hero', 15, '+', 'YILLIK TAJRIBA', 'ЛЕТ ОПЫТА', 1, 10),
('home_hero', 120, '+', 'REKLAMA MAYDONI', 'РЕКЛАМНЫХ ПЛОЩАДОК', 1, 20),
('home_hero', 350, '+', 'BAJARILGAN LOYIHA', 'РЕАЛИЗОВАННЫХ ПРОЕКТОВ', 1, 30),
('home_counters', 120, '+', 'Reklama konstruksiyalari', 'Рекламных конструкций', 1, 40),
('home_counters', 15, '+', 'Yillik tajriba', 'Лет опыта', 1, 50),
('home_counters', 350, '+', 'Bajarilgan loyihalar', 'Выполненных проектов', 1, 60),
('home_counters', 98, '%', 'Mamnun mijozlar', 'Довольных клиентов', 1, 70),
('about_counters', 120, '+', 'Konstruksiyalar', 'Конструкций', 1, 80),
('about_counters', 15, '', 'Jamoa aʼzolari', 'Специалистов', 1, 90),
('about_counters', 15, '+', 'Yil', 'Лет', 1, 100),
('about_counters', 24, '/7', 'Monitoring', 'Мониторинг', 1, 110);

INSERT INTO districts (`slug`, `number`, `name_uz`, `name_ru`, `short_uz`, `short_ru`, `description_uz`, `description_ru`, `marker_x`, `marker_y`, `label_x`, `label_y`, `label_left`, `is_city`, `is_active`, `sort_order`, `map_order`) VALUES
('arnasoy', '01', 'Arnasoy tumani', 'Арнасайский район', 'Arnasoy', 'Арнасай', '', '', 429, 248, 447, 245, 0, 0, 1, 10, 20),
('baxmal', '02', 'Baxmal tumani', 'Бахмальский район', 'Baxmal', 'Бахмал', '', '', 418, 472, 435, 477, 0, 0, 1, 20, 110),
('dustlik', '03', 'Do‘stlik tumani', 'Дустликский район', 'Do‘stlik', 'Дустлик', '', '', 468, 263, 486, 280, 0, 0, 1, 30, 40),
('forish', '04', 'Forish tumani', 'Форишский район', 'Forish', 'Фориш', '', '', 336, 256, 318, 261, 1, 0, 1, 40, 10),
('gallaorol', '05', 'G‘allaorol tumani', 'Галляаральский район', 'G‘allaorol', 'Галляарал', '', '', 344, 398, 326, 414, 1, 0, 1, 50, 100),
('jizzakh-city', '06', 'Jizzax shahri', 'город Джизак', 'Jizzax shahri', 'город Джизак', '', '', 431, 368, 431, 345, 0, 1, 1, 60, 80),
('mirzachul', '07', 'Mirzacho‘l tumani', 'Мирзачульский район', 'Mirzacho‘l', 'Мирзачуль', '', '', 470, 215, 486, 210, 0, 0, 1, 70, 30),
('paxtakor', '08', 'Paxtakor tumani', 'Пахтакорский район', 'Paxtakor', 'Пахтакор', '', '', 466, 311, 483, 327, 0, 0, 1, 80, 50),
('sharof-rashidov', '09', 'Sharof Rashidov tumani', 'Шараф-Рашидовский район', 'Sharof Rashidov', 'Шараф-Рашидов', '', '', 428, 382, 410, 403, 1, 0, 1, 90, 90),
('yangiobod', '10', 'Yangiobod tumani', 'Янгиабадский район', 'Yangiobod', 'Янгиабад', '', '', 621, 396, 638, 401, 0, 0, 1, 100, 130),
('zafarobod', '11', 'Zafarobod tumani', 'Зафарабадский район', 'Zafarobod', 'Зафарабад', '', '', 412, 303, 390, 295, 1, 0, 1, 110, 60),
('zarbdor', '12', 'Zarbdor tumani', 'Зарбдорский район', 'Zarbdor', 'Зарбдор', '', '', 492, 363, 509, 383, 0, 0, 1, 120, 70),
('zomin', '13', 'Zomin tumani', 'Зааминский район', 'Zomin', 'Заамин', '', '', 547, 421, 564, 426, 0, 0, 1, 130, 120);


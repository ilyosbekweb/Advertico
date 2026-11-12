<?php
declare(strict_types=1);

/*
 * Universal CRUD: har bir kontent turi shu yerdagi konfiguratsiya bilan tavsiflanadi.
 * Ustun nomlari faqat konfiguratsiyadan olinadi (foydalanuvchi kiritmasidan emas),
 * qiymatlar esa doim prepared statement orqali yoziladi.
 *
 * Maydon turlari: text, textarea, i18n, i18n_textarea, number, bool, color, select,
 *                 media, slug, svg, prices
 */

function crud_entities(): array
{
    static $entities = null;
    if ($entities !== null) {
        return $entities;
    }
    $statGroups = [
        'home_hero'      => 'Bosh sahifa — hero raqamlari',
        'home_counters'  => 'Bosh sahifa — hisoblagichlar',
        'about_counters' => 'Biz haqimizda — hisoblagichlar',
    ];
    $entities = [
        'services' => [
            'table' => 'services', 'title' => 'Xizmatlar', 'singular' => 'xizmat', 'icon' => 'services',
            'description' => 'Bosh sahifa kartalari, menyu, xizmatlar katalogi, katalog oynasi va aloqa formasidagi ro‘yxat shu yerdan olinadi.',
            'sortable' => true, 'thumb' => 'image_id',
            'columns' => [['number', '№'], ['name_uz', 'Nomi'], ['list_price_uz', 'Narx'], ['is_active', 'Saytda', 'bool'], ['show_on_home', 'Bosh sahifada', 'bool']],
            'fields' => [
                ['name' => 'name', 'type' => 'i18n', 'label' => 'Qisqa nomi', 'required' => true, 'max' => 160, 'help' => 'Menyu, bosh sahifa kartasi va aloqa formasida ko‘rinadi.'],
                ['name' => 'title', 'type' => 'i18n', 'label' => 'Katalogdagi sarlavha', 'required' => true, 'max' => 200],
                ['name' => 'description', 'type' => 'i18n_textarea', 'label' => 'Tavsif', 'max' => 2000],
                ['name' => 'home_price', 'type' => 'i18n', 'label' => 'Bosh sahifadagi narx', 'max' => 160, 'help' => 'Masalan: 1 500 000 soʻm/oy'],
                ['name' => 'list_price', 'type' => 'i18n', 'label' => 'Xizmatlar sahifasidagi narx', 'max' => 160],
                ['name' => 'prices', 'type' => 'prices', 'label' => 'Katalog narxlari (oynada ko‘rinadi)'],
                ['name' => 'image_id', 'type' => 'media', 'label' => 'Katalog rasmi'],
                ['name' => 'number', 'type' => 'text', 'label' => 'Ko‘rinadigan raqam', 'max' => 10, 'help' => '01, 02 … Kartalarda chiqadi.', 'half' => true],
                ['name' => 'slug', 'type' => 'slug', 'label' => 'Slug (havola)', 'required' => true, 'max' => 60, 'help' => 'Faqat kichik lotin harflar, raqam va “-”. Masalan: billboard', 'half' => true],
                ['name' => 'is_active', 'type' => 'bool', 'label' => 'Saytda ko‘rsatish', 'default' => 1],
                ['name' => 'show_on_home', 'type' => 'bool', 'label' => 'Bosh sahifada ko‘rsatish', 'default' => 1],
            ],
        ],
        'portfolio' => [
            'table' => 'portfolio_items', 'title' => 'Portfolio loyihalari', 'singular' => 'loyiha', 'icon' => 'portfolio',
            'description' => 'Portfolio sahifasidagi plitkalar. Rasm qo‘shilmasa, rangli fon ko‘rsatiladi.',
            'sortable' => true, 'thumb' => 'image_id',
            'columns' => [['title_uz', 'Nomi'], ['category_id', 'Kategoriya', 'select'], ['is_published', 'Saytda', 'bool']],
            'fields' => [
                ['name' => 'title', 'type' => 'i18n', 'label' => 'Loyiha nomi', 'required' => true, 'max' => 200],
                ['name' => 'category_id', 'type' => 'select', 'label' => 'Kategoriya', 'nullable' => true,
                    'options' => static fn() => crud_options('SELECT id, name_uz FROM portfolio_categories ORDER BY sort_order, id')],
                ['name' => 'description', 'type' => 'i18n_textarea', 'label' => 'Tavsif (loyiha oynasida)', 'max' => 2000, 'help' => 'Bo‘sh qoldirilsa, umumiy matn ko‘rsatiladi.'],
                ['name' => 'image_id', 'type' => 'media', 'label' => 'Rasm'],
                ['name' => 'accent_color', 'type' => 'color', 'label' => 'Fon rangi', 'default' => '#16a34a'],
                ['name' => 'is_published', 'type' => 'bool', 'label' => 'Saytda ko‘rsatish', 'default' => 1],
            ],
        ],
        'categories' => [
            'table' => 'portfolio_categories', 'title' => 'Portfolio kategoriyalari', 'singular' => 'kategoriya', 'icon' => 'tag',
            'description' => 'Portfolio sahifasidagi filtr tugmalari.',
            'sortable' => true,
            'columns' => [['name_uz', 'Nomi'], ['slug', 'Slug']],
            'fields' => [
                ['name' => 'name', 'type' => 'i18n', 'label' => 'Nomi', 'required' => true, 'max' => 120],
                ['name' => 'slug', 'type' => 'slug', 'label' => 'Slug', 'required' => true, 'max' => 40],
            ],
        ],
        'clients' => [
            'table' => 'clients', 'title' => 'Mijozlar', 'singular' => 'mijoz', 'icon' => 'clients',
            'description' => 'Logotiplar bloki va bosh sahifadagi yuguruvchi qator.',
            'sortable' => true, 'thumb' => 'logo_id',
            'columns' => [['name', 'Nomi'], ['show_in_grid', 'Logotiplarda', 'bool'], ['show_in_marquee', 'Yuguruvchi qatorda', 'bool']],
            'fields' => [
                ['name' => 'name', 'type' => 'text', 'label' => 'Nomi', 'required' => true, 'max' => 120],
                ['name' => 'logo_id', 'type' => 'media', 'label' => 'Logotip rasmi', 'help' => 'Rasm yuklansa, quyidagi SVG o‘rniga rasm ko‘rsatiladi.'],
                ['name' => 'logo_svg', 'type' => 'svg', 'label' => 'Logotip (SVG kodi)', 'max' => 20000, 'help' => 'Ixtiyoriy. Xavfsizlik uchun skriptlar avtomatik olib tashlanadi.'],
                ['name' => 'show_in_grid', 'type' => 'bool', 'label' => '“Mijozlar va hamkorlar” blokida ko‘rsatish', 'default' => 1],
                ['name' => 'show_in_marquee', 'type' => 'bool', 'label' => 'Bosh sahifadagi yuguruvchi qatorda ko‘rsatish'],
            ],
        ],
        'testimonials' => [
            'table' => 'testimonials', 'title' => 'Fikrlar', 'singular' => 'fikr', 'icon' => 'quote',
            'sortable' => true,
            'columns' => [['author', 'Muallif'], ['company', 'Kompaniya'], ['rating', 'Baho'], ['is_published', 'Saytda', 'bool']],
            'fields' => [
                ['name' => 'text', 'type' => 'i18n_textarea', 'label' => 'Fikr matni', 'required' => true, 'max' => 1000],
                ['name' => 'author', 'type' => 'text', 'label' => 'Muallif', 'required' => true, 'max' => 120, 'half' => true],
                ['name' => 'company', 'type' => 'text', 'label' => 'Kompaniya', 'max' => 120, 'half' => true],
                ['name' => 'rating', 'type' => 'number', 'label' => 'Baho (1–5)', 'min' => 1, 'max' => 5, 'default' => 5, 'half' => true],
                ['name' => 'is_published', 'type' => 'bool', 'label' => 'Saytda ko‘rsatish', 'default' => 1],
            ],
        ],
        'cases' => [
            'table' => 'case_studies', 'title' => 'Keyslar', 'singular' => 'keys', 'icon' => 'case',
            'sortable' => true,
            'columns' => [['metric_uz', 'Natija'], ['client_name', 'Mijoz'], ['is_published', 'Saytda', 'bool']],
            'fields' => [
                ['name' => 'metric', 'type' => 'i18n', 'label' => 'Natija (katta raqam)', 'required' => true, 'max' => 40, 'help' => 'Masalan: +38%, 7 kun'],
                ['name' => 'client_name', 'type' => 'text', 'label' => 'Mijoz', 'required' => true, 'max' => 120],
                ['name' => 'text', 'type' => 'i18n_textarea', 'label' => 'Tavsif', 'required' => true, 'max' => 1000],
                ['name' => 'is_published', 'type' => 'bool', 'label' => 'Saytda ko‘rsatish', 'default' => 1],
            ],
        ],
        'team' => [
            'table' => 'team_members', 'title' => 'Jamoa', 'singular' => 'xodim', 'icon' => 'team',
            'description' => '“Biz haqimizda” sahifasidagi jamoa bloki.',
            'sortable' => true, 'thumb' => 'photo_id',
            'columns' => [['name', 'Ism'], ['role_uz', 'Lavozim'], ['is_active', 'Saytda', 'bool']],
            'fields' => [
                ['name' => 'name', 'type' => 'text', 'label' => 'Ism familiya', 'required' => true, 'max' => 120],
                ['name' => 'role', 'type' => 'i18n', 'label' => 'Lavozim', 'required' => true, 'max' => 160],
                ['name' => 'photo_id', 'type' => 'media', 'label' => 'Rasm', 'help' => 'Kvadrat rasm tavsiya etiladi. Rasm alt matni Media bo‘limida tahrirlanadi.'],
                ['name' => 'placeholder', 'type' => 'text', 'label' => 'Rasm bo‘lmasa ko‘rsatiladigan belgi', 'max' => 10, 'half' => true],
                ['name' => 'accent_color', 'type' => 'color', 'label' => 'Fon rangi', 'default' => '#16a34a', 'half' => true],
                ['name' => 'is_active', 'type' => 'bool', 'label' => 'Saytda ko‘rsatish', 'default' => 1],
            ],
        ],
        'stats' => [
            'table' => 'stats', 'title' => 'Statistika', 'singular' => 'ko‘rsatkich', 'icon' => 'stats',
            'description' => 'Saytdagi raqamli ko‘rsatkichlar (animatsiyali hisoblagichlar).',
            'sortable' => true, 'filter' => ['field' => 'group_key', 'options' => $statGroups],
            'columns' => [['value', 'Qiymat'], ['suffix', 'Qo‘shimcha'], ['label_uz', 'Yozuv'], ['is_active', 'Saytda', 'bool']],
            'fields' => [
                ['name' => 'group_key', 'type' => 'select', 'label' => 'Joylashuvi', 'required' => true, 'options' => static fn() => $statGroups],
                ['name' => 'value', 'type' => 'number', 'label' => 'Qiymat', 'required' => true, 'min' => 0, 'max' => 99999999, 'half' => true],
                ['name' => 'suffix', 'type' => 'text', 'label' => 'Qo‘shimcha belgi', 'max' => 10, 'help' => 'Masalan: +, %, /7', 'half' => true],
                ['name' => 'label', 'type' => 'i18n', 'label' => 'Yozuv', 'required' => true, 'max' => 160],
                ['name' => 'is_active', 'type' => 'bool', 'label' => 'Saytda ko‘rsatish', 'default' => 1],
            ],
        ],
        'districts' => [
            'table' => 'districts', 'title' => 'Tumanlar', 'singular' => 'tuman', 'icon' => 'map',
            'description' => 'Lokatsiyalar sahifasidagi xarita va tumanlar ro‘yxati. Tumanga tavsif yozilmasa, “Sahifa matnlari”dagi umumiy tavsif ko‘rsatiladi.',
            'sortable' => true,
            'columns' => [['number', '№'], ['name_uz', 'Nomi'], ['is_active', 'Saytda', 'bool']],
            'fields' => [
                ['name' => 'name', 'type' => 'i18n', 'label' => 'To‘liq nomi', 'required' => true, 'max' => 120, 'help' => 'Panel sarlavhasida: “Arnasoy tumani”'],
                ['name' => 'short', 'type' => 'i18n', 'label' => 'Qisqa nomi', 'required' => true, 'max' => 80, 'help' => 'Xarita va ro‘yxatda: “Arnasoy”'],
                ['name' => 'description', 'type' => 'i18n_textarea', 'label' => 'Tavsif', 'max' => 2000],
                ['name' => 'number', 'type' => 'text', 'label' => 'Raqam', 'required' => true, 'max' => 4, 'half' => true],
                ['name' => 'slug', 'type' => 'slug', 'label' => 'Slug', 'required' => true, 'max' => 60, 'half' => true],
                ['name' => 'marker_x', 'type' => 'number', 'label' => 'Belgi X (0–860)', 'min' => 0, 'max' => 860, 'half' => true, 'group' => 'Xaritadagi joylashuv'],
                ['name' => 'marker_y', 'type' => 'number', 'label' => 'Belgi Y (0–580)', 'min' => 0, 'max' => 580, 'half' => true],
                ['name' => 'label_x', 'type' => 'number', 'label' => 'Yozuv X', 'min' => 0, 'max' => 860, 'half' => true],
                ['name' => 'label_y', 'type' => 'number', 'label' => 'Yozuv Y', 'min' => 0, 'max' => 580, 'half' => true],
                ['name' => 'map_order', 'type' => 'number', 'label' => 'Xaritada chizilish tartibi', 'min' => 0, 'max' => 9999, 'half' => true],
                ['name' => 'label_left', 'type' => 'bool', 'label' => 'Yozuv belgining chap tomonida'],
                ['name' => 'is_city', 'type' => 'bool', 'label' => 'Shahar (kattaroq belgi)'],
                ['name' => 'is_active', 'type' => 'bool', 'label' => 'Saytda ko‘rsatish', 'default' => 1],
            ],
        ],
        'seo' => [
            'table' => 'seo_pages', 'pk' => 'page_key', 'title' => 'SEO', 'singular' => 'sahifa', 'icon' => 'seo',
            'description' => 'Qidiruv tizimlari va ijtimoiy tarmoqlarda ko‘rinadigan sarlavha va tavsiflar. sitemap.xml avtomatik yangilanadi.',
            'can_create' => false, 'can_delete' => false, 'order' => "FIELD(page_key,'home','about','services','portfolio','locations','contact')",
            'columns' => [['label', 'Sahifa'], ['title_uz', 'Sarlavha'], ['noindex', 'Yashirilgan', 'bool']],
            'fields' => [
                ['name' => 'title', 'type' => 'i18n', 'label' => 'Sarlavha (title)', 'required' => true, 'max' => 255, 'counter' => 60],
                ['name' => 'description', 'type' => 'i18n_textarea', 'label' => 'Tavsif (meta description)', 'max' => 500, 'counter' => 160],
                ['name' => 'og_image_id', 'type' => 'media', 'label' => 'Ijtimoiy tarmoqlar uchun rasm (Open Graph)', 'help' => 'Tavsiya: 1200×630 px.'],
                ['name' => 'noindex', 'type' => 'bool', 'label' => 'Qidiruv tizimlaridan yashirish (noindex)'],
            ],
            'seo_preview' => true,
        ],
    ];
    return $entities;
}

/** SELECT id, nom => [id => nom] */
function crud_options(string $sql): array
{
    $options = [];
    foreach (db()->query($sql)->fetchAll(PDO::FETCH_NUM) as [$id, $label]) {
        $options[(string)$id] = (string)$label;
    }
    return $options;
}

function crud_field_options(array $field): array
{
    $options = $field['options'] ?? [];
    return is_callable($options) ? $options() : $options;
}

/* ===================================================================== */

function crud_handle(string $key): void
{
    $cfg = crud_entities()[$key];
    $cfg['key'] = $key;
    $cfg['pk'] ??= 'id';
    $cfg['can_create'] ??= true;
    $cfg['can_delete'] ??= true;
    $action = input('a', 'list');

    if ($action === 'reorder' && is_post() && !empty($cfg['sortable'])) {
        csrf_check();
        crud_reorder($cfg);
    }
    if ($action === 'delete' && is_post() && $cfg['can_delete']) {
        csrf_check();
        crud_delete($cfg);
    }
    if (in_array($action, ['new', 'edit'], true)) {
        $row = [];
        $id = input('id');
        if ($action === 'edit') {
            $row = crud_find($cfg, $id);
            if (!$row) {
                flash('error', 'Yozuv topilmadi.');
                redirect_to($key);
            }
        } elseif (!$cfg['can_create']) {
            redirect_to($key);
        } else {
            foreach ($cfg['fields'] as $f) {
                if (array_key_exists('default', $f)) {
                    $row[$f['name']] = $f['default'];
                }
            }
            if (!empty($cfg['filter']) && input('filter') !== '') {
                $row[$cfg['filter']['field']] = input('filter');
            }
        }
        $errors = [];
        if (is_post()) {
            csrf_check();
            [$data, $errors] = crud_collect($cfg, $action === 'edit' ? $id : null);
            if (!$errors) {
                $savedId = crud_save($cfg, $data, $action === 'edit' ? $id : null);
                flash('success', 'Saqlandi.');
                if (input('_stay') === '1') {
                    redirect_to($key, ['a' => 'edit', 'id' => $savedId]);
                }
                redirect_to($key, !empty($cfg['filter']) && isset($data[$cfg['filter']['field']]) ? ['filter' => $data[$cfg['filter']['field']]] : []);
            }
            $row = array_merge($row, $data);
        }
        crud_render_form($cfg, $row, $errors, $action === 'edit' ? $id : null);
        return;
    }
    crud_render_list($cfg);
}

function crud_find(array $cfg, string $id): ?array
{
    $stmt = db()->prepare("SELECT * FROM {$cfg['table']} WHERE {$cfg['pk']} = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

/** POST ma'lumotlarini tekshirish. @return array{0: array, 1: array} */
function crud_collect(array $cfg, ?string $id): array
{
    $data = [];
    $errors = [];
    foreach ($cfg['fields'] as $f) {
        $name = $f['name'];
        $label = $f['label'];
        $max = (int)($f['max'] ?? 255);
        switch ($f['type']) {
            case 'i18n':
            case 'i18n_textarea':
                foreach (['uz', 'ru'] as $lang) {
                    $value = trim((string)($_POST[$name . '_' . $lang] ?? ''));
                    if ($f['type'] === 'i18n') {
                        $value = preg_replace('/\s+/u', ' ', $value) ?? '';
                    }
                    if (mb_strlen($value) > $max) {
                        $errors[$name . '_' . $lang] = "Ko‘pi bilan {$max} ta belgi.";
                    }
                    $data[$name . '_' . $lang] = $value;
                }
                if (!empty($f['required']) && $data[$name . '_uz'] === '') {
                    $errors[$name . '_uz'] = 'O‘zbekcha qiymat majburiy.';
                }
                break;
            case 'number':
                $raw = trim((string)($_POST[$name] ?? ''));
                if ($raw === '' && empty($f['required'])) {
                    $data[$name] = (int)($f['default'] ?? 0);
                    break;
                }
                if (!preg_match('/^-?\d+$/', $raw)) {
                    $errors[$name] = 'Butun son kiriting.';
                    $data[$name] = $raw;
                    break;
                }
                $value = (int)$raw;
                if (isset($f['min']) && $value < $f['min'] || isset($f['max']) && $value > $f['max']) {
                    $errors[$name] = "Qiymat {$f['min']}–{$f['max']} oralig‘ida bo‘lishi kerak.";
                }
                $data[$name] = $value;
                break;
            case 'bool':
                $data[$name] = !empty($_POST[$name]) ? 1 : 0;
                break;
            case 'color':
                $value = trim((string)($_POST[$name] ?? ''));
                if (!preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
                    $errors[$name] = 'Rang #RRGGBB formatida bo‘lishi kerak.';
                }
                $data[$name] = strtolower($value);
                break;
            case 'select':
                $value = (string)($_POST[$name] ?? '');
                $options = crud_field_options($f);
                if ($value === '' && !empty($f['nullable'])) {
                    $data[$name] = null;
                } elseif (!array_key_exists($value, $options)) {
                    $errors[$name] = 'Ro‘yxatdan tanlang.';
                    $data[$name] = $value;
                } else {
                    $data[$name] = $value;
                }
                break;
            case 'media':
                $value = (int)($_POST[$name] ?? 0);
                if ($value > 0 && !media_by_id($value)) {
                    $errors[$name] = 'Rasm topilmadi.';
                }
                $data[$name] = $value > 0 ? $value : null;
                break;
            case 'slug':
                $value = strtolower(trim((string)($_POST[$name] ?? '')));
                if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) || strlen($value) > $max) {
                    $errors[$name] = 'Faqat kichik lotin harflar, raqamlar va “-”.';
                } else {
                    $stmt = db()->prepare("SELECT COUNT(*) FROM {$cfg['table']} WHERE {$name} = :v" . ($id !== null ? " AND {$cfg['pk']} <> :id" : ''));
                    $stmt->execute($id !== null ? [':v' => $value, ':id' => $id] : [':v' => $value]);
                    if ((int)$stmt->fetchColumn() > 0) {
                        $errors[$name] = 'Bu slug allaqachon band.';
                    }
                }
                $data[$name] = $value;
                break;
            case 'prices':
                $prices = [];
                foreach ((array)($_POST[$name] ?? []) as $p) {
                    if (!is_array($p)) {
                        continue;
                    }
                    $item = [];
                    foreach (['label_uz', 'label_ru', 'value_uz', 'value_ru'] as $k) {
                        $item[$k] = mb_substr(trim((string)($p[$k] ?? '')), 0, 160);
                    }
                    if ($item['label_uz'] !== '' || $item['value_uz'] !== '') {
                        $prices[] = $item;
                    }
                }
                $data[$name] = json_encode($prices, JSON_UNESCAPED_UNICODE);
                break;
            case 'svg':
                $value = trim((string)($_POST[$name] ?? ''));
                if ($value !== '' && safe_svg($value) === '') {
                    $errors[$name] = 'Kod <svg> tegi bilan boshlanishi kerak.';
                }
                if (mb_strlen($value) > $max) {
                    $errors[$name] = 'SVG kodi juda katta.';
                }
                $data[$name] = $value !== '' ? safe_svg($value) : null;
                break;
            default: // text, textarea
                $value = trim((string)($_POST[$name] ?? ''));
                if (!empty($f['required']) && $value === '') {
                    $errors[$name] = "“{$label}” majburiy.";
                }
                if (mb_strlen($value) > $max) {
                    $errors[$name] = "Ko‘pi bilan {$max} ta belgi.";
                }
                $data[$name] = $value;
        }
    }
    return [$data, $errors];
}

function crud_save(array $cfg, array $data, ?string $id): string
{
    $columns = array_keys($data);
    if ($id !== null) {
        $set = implode(', ', array_map(static fn($c) => "`{$c}` = :{$c}", $columns));
        $stmt = db()->prepare("UPDATE {$cfg['table']} SET {$set} WHERE {$cfg['pk']} = :__id");
        $stmt->execute(array_merge(crud_params($data), [':__id' => $id]));
        audit('update', $cfg['key'], ctype_digit($id) ? (int)$id : null, ['key' => $id]);
        return $id;
    }
    if (!empty($cfg['sortable'])) {
        $data['sort_order'] = (int)db()->query("SELECT COALESCE(MAX(sort_order), 0) + 10 FROM {$cfg['table']}")->fetchColumn();
        $columns = array_keys($data);
    }
    $stmt = db()->prepare("INSERT INTO {$cfg['table']} (`" . implode('`, `', $columns) . '`) VALUES (:' . implode(', :', $columns) . ')');
    $stmt->execute(crud_params($data));
    $newId = (string)db()->lastInsertId();
    audit('create', $cfg['key'], (int)$newId);
    return $newId;
}

function crud_params(array $data): array
{
    $params = [];
    foreach ($data as $k => $v) {
        $params[':' . $k] = $v;
    }
    return $params;
}

function crud_delete(array $cfg): never
{
    $id = input('id');
    $row = crud_find($cfg, $id);
    if ($row) {
        db()->prepare("DELETE FROM {$cfg['table']} WHERE {$cfg['pk']} = :id")->execute([':id' => $id]);
        audit('delete', $cfg['key'], ctype_digit($id) ? (int)$id : null, ['title' => crud_row_title($cfg, $row)]);
        flash('success', 'O‘chirildi.');
    }
    redirect_to($cfg['key'], input('filter') !== '' ? ['filter' => input('filter')] : []);
}

function crud_reorder(array $cfg): never
{
    $ids = array_values(array_filter(array_map('intval', (array)($_POST['ids'] ?? []))));
    $stmt = db()->prepare("UPDATE {$cfg['table']} SET sort_order = :s WHERE id = :id");
    db()->beginTransaction();
    foreach ($ids as $position => $id) {
        $stmt->execute([':s' => ($position + 1) * 10, ':id' => $id]);
    }
    db()->commit();
    audit('reorder', $cfg['key']);
    json_response(200, ['success' => true]);
}

function crud_row_title(array $cfg, array $row): string
{
    foreach (['name_uz', 'title_uz', 'name', 'author', 'label_uz', 'label', 'metric_uz'] as $k) {
        if (!empty($row[$k])) {
            return (string)$row[$k];
        }
    }
    return '#' . ($row[$cfg['pk']] ?? '');
}

/* ===================================================================== Ko'rinishlar */

function crud_render_list(array $cfg): void
{
    $where = '';
    $params = [];
    $filterValue = '';
    if (!empty($cfg['filter'])) {
        $filterValue = input('filter');
        if (!array_key_exists($filterValue, $cfg['filter']['options'])) {
            $filterValue = (string)array_key_first($cfg['filter']['options']);
        }
        $where = " WHERE {$cfg['filter']['field']} = :f";
        $params[':f'] = $filterValue;
    }
    $order = $cfg['order'] ?? (!empty($cfg['sortable']) ? 'sort_order, id' : $cfg['pk']);
    $stmt = db()->prepare("SELECT * FROM {$cfg['table']}{$where} ORDER BY {$order}");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $selectLabels = [];
    foreach ($cfg['fields'] as $f) {
        if ($f['type'] === 'select') {
            $selectLabels[$f['name']] = crud_field_options($f);
        }
    }
    $thumbs = [];
    if (!empty($cfg['thumb'])) {
        $ids = array_filter(array_map(static fn($r) => (int)$r[$cfg['thumb']], $rows));
        if ($ids) {
            foreach (db()->query('SELECT id, path FROM media WHERE id IN (' . implode(',', $ids) . ')') as $m) {
                $thumbs[(int)$m['id']] = $m['path'];
            }
        }
    }

    render_admin($cfg['title'], $cfg['key'], static function () use ($cfg, $rows, $selectLabels, $thumbs, $filterValue) {
        $filterParams = $filterValue !== '' ? ['filter' => $filterValue] : [];
        ?>
        <div class="page-head">
            <div>
                <h1><?= e($cfg['title']) ?></h1>
                <?php if (!empty($cfg['description'])): ?><p class="muted"><?= e($cfg['description']) ?></p><?php endif; ?>
            </div>
            <?php if ($cfg['can_create']): ?>
                <a class="btn" href="<?= e(admin_url($cfg['key'], ['a' => 'new'] + $filterParams)) ?>"><?= icon('plus') ?>Yangi <?= e($cfg['singular']) ?></a>
            <?php endif; ?>
        </div>
        <?php if (!empty($cfg['filter'])): ?>
            <div class="tabs">
                <?php foreach ($cfg['filter']['options'] as $value => $label): ?>
                    <a class="tab<?= $value === $filterValue ? ' on' : '' ?>" href="<?= e(admin_url($cfg['key'], ['filter' => $value])) ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="card table-card">
            <?php if (!$rows): ?>
                <div class="empty">Hozircha hech narsa yo‘q.</div>
            <?php else: ?>
            <table class="table" <?php if (!empty($cfg['sortable'])): ?>data-sortable="<?= e(admin_url($cfg['key'], ['a' => 'reorder'])) ?>"<?php endif; ?>>
                <thead><tr>
                    <?php if (!empty($cfg['sortable'])): ?><th class="col-grip" aria-label="Tartib"></th><?php endif; ?>
                    <?php if (!empty($cfg['thumb'])): ?><th class="col-thumb">Rasm</th><?php endif; ?>
                    <?php foreach ($cfg['columns'] as $col): ?><th><?= e($col[1]) ?></th><?php endforeach; ?>
                    <th class="col-actions"><span class="sr">Amallar</span></th>
                </tr></thead>
                <tbody>
                <?php foreach ($rows as $row): $rowId = (string)$row[$cfg['pk']]; ?>
                    <tr data-id="<?= e($rowId) ?>">
                        <?php if (!empty($cfg['sortable'])): ?><td class="col-grip"><span class="grip" draggable="true" title="Sudrab tartibni o‘zgartiring"><?= icon('grip') ?></span></td><?php endif; ?>
                        <?php if (!empty($cfg['thumb'])): $t = $thumbs[(int)$row[$cfg['thumb']]] ?? null; ?>
                            <td class="col-thumb"><?php if ($t): ?><img src="<?= e(media_url($t)) ?>" alt="" loading="lazy"><?php else: ?><span class="thumb-empty"></span><?php endif; ?></td>
                        <?php endif; ?>
                        <?php foreach ($cfg['columns'] as $i => $col):
                            $value = $row[$col[0]] ?? '';
                            $type = $col[2] ?? 'text'; ?>
                            <td<?= $i === 0 ? ' class="strong"' : '' ?>>
                                <?php if ($type === 'bool'): ?>
                                    <?= (int)$value === 1 ? '<span class="dot dot-on" title="Ha"></span>' : '<span class="dot" title="Yo‘q"></span>' ?>
                                <?php elseif ($type === 'select'): ?>
                                    <?= e($selectLabels[$col[0]][(string)$value] ?? '—') ?>
                                <?php elseif ($i === 0): ?>
                                    <a href="<?= e(admin_url($cfg['key'], ['a' => 'edit', 'id' => $rowId])) ?>"><?= e(str_limit((string)$value, 80)) ?></a>
                                <?php else: ?>
                                    <?= e(str_limit((string)$value, 80)) ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="col-actions">
                            <a class="icon-btn" href="<?= e(admin_url($cfg['key'], ['a' => 'edit', 'id' => $rowId])) ?>" title="Tahrirlash"><?= icon('edit') ?></a>
                            <?php if ($cfg['can_delete']): ?>
                            <form method="post" action="<?= e(admin_url($cfg['key'], ['a' => 'delete', 'id' => $rowId] + $filterParams)) ?>" data-confirm="“<?= e(crud_row_title($cfg, $row)) ?>” o‘chirilsinmi? Bu amalni ortga qaytarib bo‘lmaydi.">
                                <?= csrf_field() ?>
                                <button class="icon-btn danger" type="submit" title="O‘chirish"><?= icon('trash') ?></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php if (!empty($cfg['sortable']) && $rows): ?><p class="muted small">Tartibni o‘zgartirish uchun qatorni ⠿ belgisidan ushlab sudrang. O‘zgarish darhol saqlanadi.</p><?php endif; ?>
        <?php
    });
}

function crud_render_form(array $cfg, array $row, array $errors, ?string $id): void
{
    $title = $id !== null ? crud_row_title($cfg, $row) : 'Yangi ' . $cfg['singular'];
    render_admin($title . ' — ' . $cfg['title'], $cfg['key'], static function () use ($cfg, $row, $errors, $id, $title) {
        $back = admin_url($cfg['key'], !empty($cfg['filter']) && !empty($row[$cfg['filter']['field']]) ? ['filter' => $row[$cfg['filter']['field']]] : []);
        $action = $id !== null ? admin_url($cfg['key'], ['a' => 'edit', 'id' => $id]) : admin_url($cfg['key'], ['a' => 'new']);
        ?>
        <div class="page-head">
            <div>
                <a class="back" href="<?= e($back) ?>">← <?= e($cfg['title']) ?></a>
                <h1><?= e($title) ?></h1>
            </div>
        </div>
        <?php if ($errors): ?><div class="alert alert-error">Xatolarni tuzating: <?= count($errors) ?> ta maydon.</div><?php endif; ?>
        <form method="post" action="<?= e($action) ?>" class="form card" novalidate>
            <?= csrf_field() ?>
            <div class="form-grid">
                <?php foreach ($cfg['fields'] as $field) { crud_render_field($field, $row, $errors); } ?>
            </div>
            <?php if (!empty($cfg['seo_preview'])): ?>
                <div class="seo-preview" data-seo-preview>
                    <div class="seo-preview-label">Google’dagi ko‘rinishi (o‘zbekcha)</div>
                    <div class="seo-preview-url"><?= e(absolute_url($row['page_key'] === 'home' ? '' : $row['page_key'] . '.html')) ?></div>
                    <div class="seo-preview-title" data-preview-title></div>
                    <div class="seo-preview-desc" data-preview-desc></div>
                </div>
            <?php endif; ?>
            <div class="form-actions">
                <button class="btn" type="submit"><?= icon('check') ?>Saqlash</button>
                <button class="btn btn-ghost" type="submit" name="_stay" value="1">Saqlash va davom etish</button>
                <a class="btn btn-ghost" href="<?= e($back) ?>">Bekor qilish</a>
            </div>
        </form>
        <?php
    });
}

function crud_render_field(array $f, array $row, array $errors): void
{
    $name = $f['name'];
    $type = $f['type'];
    $label = $f['label'];
    $required = !empty($f['required']);
    $classes = 'field' . (!empty($f['half']) ? ' half' : '') . ' field-' . $type;
    $help = !empty($f['help']) ? '<small class="help">' . e($f['help']) . '</small>' : '';
    $err = static fn(string $key) => isset($errors[$key]) ? '<small class="error">' . e($errors[$key]) . '</small>' : '';
    $counter = !empty($f['counter']) ? ' data-counter="' . (int)$f['counter'] . '"' : '';
    $max = !empty($f['max']) ? ' maxlength="' . (int)$f['max'] . '"' : '';

    if (!empty($f['group'])) {
        echo '<div class="field-group-title">' . e($f['group']) . '</div>';
    }

    switch ($type) {
        case 'i18n':
        case 'i18n_textarea':
            echo '<div class="' . $classes . ' full"><span class="label">' . e($label) . ($required ? ' <i>*</i>' : '') . '</span><div class="i18n">';
            foreach (['uz' => 'UZ', 'ru' => 'RU'] as $lang => $tag) {
                $key = $name . '_' . $lang;
                $value = (string)($row[$key] ?? '');
                echo '<label class="i18n-row' . (isset($errors[$key]) ? ' has-error' : '') . '"><span class="lang">' . $tag . '</span>';
                if ($type === 'i18n') {
                    echo '<input type="text" name="' . e($key) . '" value="' . e($value) . '"' . $max . $counter . ($lang === 'uz' && $required ? ' required' : '') . ' data-lang="' . $lang . '" data-field="' . e($name) . '">';
                } else {
                    echo '<textarea name="' . e($key) . '" rows="3"' . $max . $counter . ($lang === 'uz' && $required ? ' required' : '') . ' data-lang="' . $lang . '" data-field="' . e($name) . '">' . e($value) . '</textarea>';
                }
                echo '</label>' . $err($key);
            }
            echo '</div>' . $help . '</div>';
            break;

        case 'bool':
            $checked = (int)($row[$name] ?? 0) === 1 ? ' checked' : '';
            echo '<label class="' . $classes . ' full switch-row"><input type="checkbox" class="switch" name="' . e($name) . '" value="1"' . $checked . '><span>' . e($label) . '</span></label>';
            break;

        case 'select':
            echo '<label class="' . $classes . (isset($errors[$name]) ? ' has-error' : '') . '"><span class="label">' . e($label) . ($required ? ' <i>*</i>' : '') . '</span><select name="' . e($name) . '">';
            if (!empty($f['nullable'])) {
                echo '<option value="">— Tanlanmagan —</option>';
            }
            foreach (crud_field_options($f) as $value => $optionLabel) {
                $selected = (string)($row[$name] ?? '') === (string)$value ? ' selected' : '';
                echo '<option value="' . e($value) . '"' . $selected . '>' . e($optionLabel) . '</option>';
            }
            echo '</select>' . $err($name) . $help . '</label>';
            break;

        case 'media':
            $media = media_by_id(isset($row[$name]) ? (int)$row[$name] : null);
            echo '<div class="' . $classes . ' full"><span class="label">' . e($label) . '</span>';
            echo '<div class="media-field" data-media-field><input type="hidden" name="' . e($name) . '" value="' . e($media['id'] ?? '') . '">';
            echo '<div class="media-field-preview">' . ($media ? '<img src="' . e(media_url($media['path'])) . '" alt="">' : '<span>Rasm tanlanmagan</span>') . '</div>';
            echo '<div class="media-field-actions"><button type="button" class="btn btn-ghost btn-sm" data-media-pick>' . icon('media') . 'Tanlash yoki yuklash</button>';
            echo '<button type="button" class="btn btn-ghost btn-sm" data-media-clear' . ($media ? '' : ' hidden') . '>' . icon('x') . 'Olib tashlash</button></div></div>';
            echo $err($name) . $help . '</div>';
            break;

        case 'color':
            $value = (string)($row[$name] ?? '#16a34a');
            echo '<label class="' . $classes . (isset($errors[$name]) ? ' has-error' : '') . '"><span class="label">' . e($label) . '</span><span class="color-input"><input type="color" value="' . e($value) . '" data-color-sync><input type="text" name="' . e($name) . '" value="' . e($value) . '" maxlength="7" pattern="#[0-9a-fA-F]{6}"></span>' . $err($name) . $help . '</label>';
            break;

        case 'prices':
            $prices = json_decode((string)($row[$name] ?? '[]'), true) ?: [];
            echo '<div class="' . $classes . ' full"><span class="label">' . e($label) . '</span><div class="repeater" data-repeater data-name="' . e($name) . '">';
            echo '<div class="repeater-head"><span>Nomi (UZ)</span><span>Nomi (RU)</span><span>Qiymat (UZ)</span><span>Qiymat (RU)</span><span></span></div><div class="repeater-rows">';
            foreach ($prices as $i => $p) {
                crud_price_row($name, (string)$i, $p);
            }
            echo '</div><template>';
            crud_price_row($name, '__i__', []);
            echo '</template><button type="button" class="btn btn-ghost btn-sm" data-repeater-add>' . icon('plus') . 'Qator qo‘shish</button></div>';
            echo '<small class="help">Qiymat (RU) bo‘sh qoldirilsa, o‘zbekcha qiymat ko‘rsatiladi.</small></div>';
            break;

        case 'svg':
        case 'textarea':
            echo '<label class="' . $classes . ' full' . (isset($errors[$name]) ? ' has-error' : '') . '"><span class="label">' . e($label) . '</span><textarea name="' . e($name) . '" rows="' . ($type === 'svg' ? 5 : 4) . '"' . ($type === 'svg' ? ' class="code" spellcheck="false"' : '') . $max . '>' . e((string)($row[$name] ?? '')) . '</textarea>' . $err($name) . $help . '</label>';
            break;

        default: // text, number, slug
            $inputType = $type === 'number' ? 'number' : 'text';
            $extra = $type === 'number' ? (isset($f['min']) ? ' min="' . (int)$f['min'] . '"' : '') . (isset($f['max']) ? ' max="' . (int)$f['max'] . '"' : '') : $max;
            echo '<label class="' . $classes . (isset($errors[$name]) ? ' has-error' : '') . '"><span class="label">' . e($label) . ($required ? ' <i>*</i>' : '') . '</span><input type="' . $inputType . '" name="' . e($name) . '" value="' . e((string)($row[$name] ?? '')) . '"' . $extra . ($required ? ' required' : '') . ($type === 'slug' ? ' spellcheck="false" autocapitalize="off"' : '') . '>' . $err($name) . $help . '</label>';
    }
}

function crud_price_row(string $name, string $index, array $p): void
{
    echo '<div class="repeater-row">';
    foreach (['label_uz' => 'Nomi (UZ)', 'label_ru' => 'Nomi (RU)', 'value_uz' => 'Qiymat (UZ)', 'value_ru' => 'Qiymat (RU)'] as $k => $placeholder) {
        echo '<input type="text" name="' . e($name) . '[' . e($index) . '][' . $k . ']" value="' . e((string)($p[$k] ?? '')) . '" placeholder="' . e($placeholder) . '" maxlength="160" aria-label="' . e($placeholder) . '">';
    }
    echo '<button type="button" class="icon-btn danger" data-repeater-remove title="Qatorni o‘chirish">' . icon('trash') . '</button></div>';
}

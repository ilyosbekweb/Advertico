<?php
declare(strict_types=1);

/**
 * Public sayt uchun kontentni bazadan o'qish va shablon helperlari.
 * Ikki tilli maydonlar bazada *_uz / *_ru ustunlarida turadi va sahifaga
 * hozirgi tizim kutgan data-uz / data-ru atributlari bilan chiqariladi.
 */
final class Content
{
    /** @var array<string, array{uz:string, ru:string}> */
    private static array $texts = [];
    /** @var array<int, array> */
    private static array $media = [];
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }
        foreach (db()->query('SELECT `key`, uz, ru FROM texts') as $row) {
            self::$texts[$row['key']] = ['uz' => (string)$row['uz'], 'ru' => (string)$row['ru']];
        }
        foreach (db()->query('SELECT id, path, alt_uz, alt_ru, width, height FROM media') as $row) {
            self::$media[(int)$row['id']] = $row;
        }
        self::$loaded = true;
    }

    /** @return array{uz:string, ru:string} */
    public static function text(string $key): array
    {
        $row = self::$texts[$key] ?? ['uz' => '', 'ru' => ''];
        return ['uz' => $row['uz'], 'ru' => $row['ru'] !== '' ? $row['ru'] : $row['uz']];
    }

    public static function media(?int $id): ?array
    {
        return $id ? (self::$media[$id] ?? null) : null;
    }

    public static function mediaPath(?int $id, string $fallback = ''): string
    {
        $media = self::media($id);
        return $media ? (string)$media['path'] : $fallback;
    }

    /** @return list<array> */
    public static function rows(string $sql, array $params = []): array
    {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function services(): array
    {
        return self::rows('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order, id');
    }

    public static function stats(string $group): array
    {
        return self::rows('SELECT * FROM stats WHERE group_key = :g AND is_active = 1 ORDER BY sort_order, id', [':g' => $group]);
    }

    public static function districts(string $order = 'sort_order'): array
    {
        $order = $order === 'map_order' ? 'map_order' : 'sort_order';
        return self::rows("SELECT * FROM districts WHERE is_active = 1 ORDER BY {$order}, id");
    }

    public static function seo(string $page): array
    {
        $rows = self::rows('SELECT * FROM seo_pages WHERE page_key = :p', [':p' => $page]);
        return $rows[0] ?? [];
    }

    /** Xizmatlar katalogi script.js kutgan formatda. */
    public static function serviceCatalog(): array
    {
        $catalog = [];
        foreach (self::services() as $s) {
            $prices = [];
            foreach (json_decode((string)$s['prices'], true) ?: [] as $p) {
                $prices[] = [
                    'label' => ['uz' => (string)($p['label_uz'] ?? ''), 'ru' => (string)(($p['label_ru'] ?? '') ?: ($p['label_uz'] ?? ''))],
                    'value' => ['uz' => (string)($p['value_uz'] ?? ''), 'ru' => (string)(($p['value_ru'] ?? '') ?: ($p['value_uz'] ?? ''))],
                ];
            }
            $catalog[$s['slug']] = [
                'image'       => self::mediaPath($s['image_id'] !== null ? (int)$s['image_id'] : null),
                'title'       => self::pair($s, 'title'),
                'description' => self::pair($s, 'description'),
                'prices'      => $prices,
            ];
        }
        return $catalog;
    }

    public static function districtMap(): array
    {
        $map = [];
        foreach (self::districts() as $d) {
            $map[$d['slug']] = [
                'name'        => self::pair($d, 'name'),
                'number'      => (string)$d['number'],
                'description' => self::pair($d, 'description'),
            ];
        }
        return $map;
    }

    /** @return array{uz:string, ru:string} */
    public static function pair(array $row, string $field): array
    {
        $uz = (string)($row[$field . '_uz'] ?? '');
        $ru = (string)($row[$field . '_ru'] ?? '');
        return ['uz' => $uz, 'ru' => $ru !== '' ? $ru : $uz];
    }
}

/* ---------- Shablon helperlari ---------- */

/** Matn kaliti bo'yicha to'liq element: <span data-uz=".." data-ru="..">..</span> */
function T(string $key, string $tag = 'span', string $attrs = ''): string
{
    $t = Content::text($key);
    return pair_html($t, $tag, $attrs);
}

/** Matn kaliti uchun faqat atributlar: ' data-uz=".." data-ru=".."' */
function ta(string $key): string
{
    $t = Content::text($key);
    return ' data-uz="' . e($t['uz']) . '" data-ru="' . e($t['ru']) . '"';
}

/** Matn kalitining o'zbekcha (standart) qiymati, escape qilingan. */
function tv(string $key): string
{
    return e(Content::text($key)['uz']);
}

/** Kalit bo'sh emasligini tekshirish (ixtiyoriy bloklarni yashirish uchun). */
function t_has(string $key): bool
{
    return trim(Content::text($key)['uz']) !== '';
}

/** Jadval qatoridagi *_uz / *_ru maydon uchun to'liq element. */
function L(array $row, string $field, string $tag = 'span', string $attrs = ''): string
{
    return pair_html(Content::pair($row, $field), $tag, $attrs);
}

function la(array $row, string $field): string
{
    $p = Content::pair($row, $field);
    return ' data-uz="' . e($p['uz']) . '" data-ru="' . e($p['ru']) . '"';
}

function lv(array $row, string $field): string
{
    return e(Content::pair($row, $field)['uz']);
}

function pair_html(array $pair, string $tag = 'span', string $attrs = ''): string
{
    $attrs = $attrs !== '' ? ' ' . trim($attrs) : '';
    return '<' . $tag . $attrs . ' data-uz="' . e($pair['uz']) . '" data-ru="' . e($pair['ru']) . '">' . e($pair['uz']) . '</' . $tag . '>';
}

/**
 * Admin kiritgan inline SVG logotipni xavfsiz holatga keltirish:
 * script, foreignObject, on* hodisalar va javascript: havolalar olib tashlanadi.
 */
function safe_svg(string $svg): string
{
    $svg = trim($svg);
    if (!preg_match('/^<svg[\s>]/i', $svg)) {
        return '';
    }
    $svg = preg_replace('#<\s*(script|foreignObject|iframe|object|embed|style)\b.*?<\s*/\s*\1\s*>#is', '', $svg) ?? '';
    $svg = preg_replace('#<\s*(script|foreignObject|iframe|object|embed)\b[^>]*/?>#is', '', $svg) ?? '';
    $svg = preg_replace('#\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $svg) ?? '';
    $svg = preg_replace('#(href\s*=\s*["\']?)\s*javascript:#i', '$1#', $svg) ?? '';
    return $svg;
}

<?php
declare(strict_types=1);

/**
 * Reklama nuqtalari: nashr qoidalari va public ma'lumotlar (API, aloqa formasi, admin uchun yagona manba).
 * Public tomonga faqat status = 'published' va koordinatasi, tumani bor yozuvlar chiqadi.
 */
final class AdLocations
{
    public const AVAILABILITY = ['available', 'reserved', 'occupied', 'maintenance'];

    /** Nashr qilish uchun yetishmayotgan narsalar ro'yxati (bo'sh bo'lsa — nashr qilsa bo'ladi). */
    public static function publishProblems(array $row): array
    {
        $problems = [];
        if (trim((string)($row['name_uz'] ?? '')) === '') {
            $problems[] = 'Joy nomi (UZ) kiritilmagan';
        }
        if (empty($row['district_id'])) {
            $problems[] = 'Tuman tanlanmagan';
        }
        if (empty($row['service_id'])) {
            $problems[] = 'Reklama turi tanlanmagan';
        }
        $lat = $row['lat'] ?? null;
        $lng = $row['lng'] ?? null;
        if ($lat === null || $lat === '' || $lng === null || $lng === '') {
            $problems[] = 'Koordinatalar kiritilmagan';
        } elseif (!Geo::inRegionBox((float)$lat, (float)$lng)) {
            $problems[] = 'Koordinatalar Jizzax viloyati hududidan tashqarida';
        }
        return $problems;
    }

    /** Barcha nashr qilingan joylar — public xarita uchun. */
    public static function publicList(): array
    {
        $rows = db()->query("SELECT l.*, d.slug AS district_slug, s.slug AS service_slug
            FROM ad_locations l
            JOIN districts d ON d.id = l.district_id
            LEFT JOIN services s ON s.id = l.service_id
            WHERE l.status = 'published' AND l.lat IS NOT NULL AND l.lng IS NOT NULL
            ORDER BY l.district_id, l.id")->fetchAll();
        if (!$rows) {
            return [];
        }
        $photos = self::photosFor(array_map(static fn($r) => (int)$r['id'], $rows));
        return array_map(static fn($r) => self::present($r, $photos[(int)$r['id']] ?? []), $rows);
    }

    /** Bitta nashr qilingan joy (aloqa formasi uchun). Nashr qilinmagan bo'lsa null. */
    public static function findPublished(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $stmt = db()->prepare("SELECT l.*, d.slug AS district_slug, d.name_uz AS district_name_uz, d.name_ru AS district_name_ru, s.slug AS service_slug
            FROM ad_locations l JOIN districts d ON d.id = l.district_id LEFT JOIN services s ON s.id = l.service_id
            WHERE l.id = :id AND l.status = 'published' AND l.lat IS NOT NULL AND l.lng IS NOT NULL");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Ma'lumotlar o'zgarganini bildiruvchi versiya (ETag uchun). */
    public static function version(): string
    {
        $row = db()->query("SELECT COUNT(*) AS c, COALESCE(MAX(updated_at), '') AS u, COALESCE(MAX(created_at), '') AS cr, COALESCE(SUM(id), 0) AS s
            FROM ad_locations WHERE status = 'published'")->fetch();
        $media = db()->query('SELECT COALESCE(SUM(media_id * (sort_order + 1)), 0) AS m, COUNT(*) AS c FROM ad_location_photos')->fetch();
        return md5(implode('|', [$row['c'], $row['u'], $row['cr'], $row['s'], $media['m'], $media['c'], filemtime(Geo::file()) ?: 0]));
    }

    /** @return array<int, list<array>> location_id => photos */
    public static function photosFor(array $ids): array
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = db()->prepare("SELECT p.location_id, m.id, m.path, m.width, m.height, m.alt_uz, m.alt_ru
            FROM ad_location_photos p JOIN media m ON m.id = p.media_id
            WHERE p.location_id IN ({$placeholders}) ORDER BY p.location_id, p.sort_order, p.media_id");
        $stmt->execute($ids);
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(int)$row['location_id']][] = $row;
        }
        return $result;
    }

    private static function present(array $r, array $photos): array
    {
        $pair = static fn(string $f) => ['uz' => (string)($r[$f . '_uz'] ?? ''), 'ru' => (string)(($r[$f . '_ru'] ?? '') !== '' ? $r[$f . '_ru'] : ($r[$f . '_uz'] ?? ''))];
        // Muqova birinchi bo'lib ko'rsatiladi.
        $cover = (int)($r['cover_media_id'] ?? 0);
        usort($photos, static fn($a, $b) => ((int)$b['id'] === $cover) <=> ((int)$a['id'] === $cover));
        return [
            'id'           => (int)$r['id'],
            'district'     => (string)$r['district_slug'],
            'type'         => (string)($r['service_slug'] ?? ''),
            'name'         => $pair('name'),
            'address'      => $pair('address'),
            'description'  => $pair('description'),
            'lat'          => (float)$r['lat'],
            'lng'          => (float)$r['lng'],
            'width'        => $r['width_m'] !== null ? (float)$r['width_m'] : null,
            'height'       => $r['height_m'] !== null ? (float)$r['height_m'] : null,
            'sides'        => $r['sides'] !== null ? (int)$r['sides'] : null,
            'lighting'     => (int)$r['lighting'] === 1,
            'availability' => in_array($r['availability'], self::AVAILABILITY, true) ? $r['availability'] : 'available',
            'photos'       => array_map(static fn($p) => [
                'thumb'  => media_variant((string)$p['path'], 'thumb'),
                'large'  => media_variant((string)$p['path'], 'large'),
                'full'   => (string)$p['path'],
                'width'  => (int)$p['width'],
                'height' => (int)$p['height'],
                'alt'    => ['uz' => (string)$p['alt_uz'], 'ru' => (string)($p['alt_ru'] !== '' ? $p['alt_ru'] : $p['alt_uz'])],
            ], $photos),
        ];
    }
}

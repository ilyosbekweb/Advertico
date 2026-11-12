<?php
declare(strict_types=1);

/**
 * Jizzax viloyati tuman chegaralari (assets/geo/jizzakh-districts.geojson) bilan ishlash.
 * Manba: geoBoundaries gbOpen UZB ADM2 (OCHA ROCCA), CC BY 3.0 IGO. ~60 m aniqlik bilan soddalashtirilgan.
 */
final class Geo
{
    // Viloyatni o'rab turgan chegara: haqiqiy chegara (lat 39.53–41.24, lng 66.63–69.01) + ~10 km zaxira.
    // Toshkent (69.28), Samarqand (39.65/66.96 — Jizzaxdan tashqari tuman) kabi joylar tumanga tekshiruvida ushlanadi.
    public const LAT_MIN = 39.43;
    public const LAT_MAX = 41.33;
    public const LNG_MIN = 66.52;
    public const LNG_MAX = 69.12;

    /** @var array<string, list<list<list<array{0:float,1:float}>>>>|null slug => polygons */
    private static ?array $districts = null;

    public static function file(): string
    {
        return ROOT_PATH . '/assets/geo/jizzakh-districts.geojson';
    }

    private static function load(): array
    {
        if (self::$districts !== null) {
            return self::$districts;
        }
        self::$districts = [];
        $data = is_file(self::file()) ? json_decode((string)file_get_contents(self::file()), true) : null;
        foreach ($data['features'] ?? [] as $feature) {
            $slug = (string)($feature['properties']['slug'] ?? '');
            $geometry = $feature['geometry'] ?? [];
            if ($slug === '' || empty($geometry['coordinates'])) {
                continue;
            }
            self::$districts[$slug] = $geometry['type'] === 'Polygon' ? [$geometry['coordinates']] : $geometry['coordinates'];
        }
        return self::$districts;
    }

    public static function inRegionBox(float $lat, float $lng): bool
    {
        return $lat >= self::LAT_MIN && $lat <= self::LAT_MAX && $lng >= self::LNG_MIN && $lng <= self::LNG_MAX;
    }

    /** Nuqta tuman ichidami. Tuman geometriyasi bo'lmasa null (tekshirib bo'lmaydi). */
    public static function districtContains(string $slug, float $lat, float $lng): ?bool
    {
        $districts = self::load();
        if (!isset($districts[$slug])) {
            return null;
        }
        return self::inPolygons($lng, $lat, $districts[$slug]);
    }

    /** Nuqta qaysi tumanga tushishini aniqlash. */
    public static function detectDistrict(float $lat, float $lng): ?string
    {
        foreach (self::load() as $slug => $polygons) {
            if (self::inPolygons($lng, $lat, $polygons)) {
                return $slug;
            }
        }
        return null;
    }

    private static function inPolygons(float $x, float $y, array $polygons): bool
    {
        foreach ($polygons as $polygon) {
            if (!self::inRing($x, $y, $polygon[0] ?? [])) {
                continue;
            }
            $inHole = false;
            foreach (array_slice($polygon, 1) as $hole) {
                if (self::inRing($x, $y, $hole)) {
                    $inHole = true;
                    break;
                }
            }
            if (!$inHole) {
                return true;
            }
        }
        return false;
    }

    private static function inRing(float $x, float $y, array $ring): bool
    {
        $inside = false;
        $count = count($ring);
        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            [$xi, $yi] = $ring[$i];
            [$xj, $yj] = $ring[$j];
            if ((($yi > $y) !== ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
        }
        return $inside;
    }
}

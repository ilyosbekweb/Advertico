<?php
declare(strict_types=1);

/**
 * Xavfsiz rasm yuklash:
 *  - faqat JPG / PNG / WEBP / GIF (MIME fayl tarkibidan finfo orqali aniqlanadi, nomidan emas);
 *  - getimagesize() bilan haqiqiy rasm ekanligi tekshiriladi;
 *  - fayl nomi tasodifiy, kengaytma MIME'dan olinadi;
 *  - GD mavjud bo'lsa rasm qayta kodlanadi (EXIF va yashirin yuklama tozalanadi);
 *  - uploads/ papkasida PHP bajarilishi .htaccess orqali taqiqlangan.
 */
final class Uploader
{
    public const MAX_BYTES = 8 * 1024 * 1024;
    private const MAX_PIXELS = 8000;
    private const TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    /** @return array{id:int, path:string, url:string, name:string, width:int, height:int} */
    public static function store(array $file): array
    {
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Fayl hajmi juda katta.',
                UPLOAD_ERR_NO_FILE => 'Fayl tanlanmagan.',
                default => 'Faylni yuklashda xatolik (kod ' . $error . ').',
            });
        }
        $tmp = (string)$file['tmp_name'];
        if (!is_uploaded_file($tmp)) {
            throw new RuntimeException('Noto‘g‘ri yuklash so‘rovi.');
        }
        $size = (int)filesize($tmp);
        if ($size <= 0 || $size > self::MAX_BYTES) {
            throw new RuntimeException('Fayl hajmi 8 MB dan oshmasligi kerak.');
        }

        $mime = (string)(new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if (!isset(self::TYPES[$mime])) {
            throw new RuntimeException('Faqat JPG, PNG, WEBP yoki GIF rasmlar qabul qilinadi.');
        }
        $info = @getimagesize($tmp);
        if (!$info || $info[0] < 1 || $info[1] < 1 || ($info['mime'] ?? '') !== $mime) {
            throw new RuntimeException('Fayl haqiqiy rasm emas yoki shikastlangan.');
        }
        if ($info[0] > self::MAX_PIXELS || $info[1] > self::MAX_PIXELS) {
            throw new RuntimeException('Rasm o‘lchami 8000×8000 pikseldan oshmasligi kerak.');
        }

        $relativeDir = 'uploads/' . date('Y') . '/' . date('m');
        $absoluteDir = ROOT_PATH . '/' . $relativeDir;
        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            throw new RuntimeException('uploads papkasini yaratib bo‘lmadi.');
        }

        $extension = self::TYPES[$mime];
        $basename = bin2hex(random_bytes(10));
        $target = $absoluteDir . '/' . $basename . '.' . $extension;

        if (!self::reencode($tmp, $mime, $target)) {
            if (!move_uploaded_file($tmp, $target)) {
                throw new RuntimeException('Faylni saqlab bo‘lmadi.');
            }
        }
        @chmod($target, 0644);

        $path = $relativeDir . '/' . $basename . '.' . $extension;
        $originalName = mb_substr(preg_replace('/[^\p{L}\p{N}._ -]/u', '', basename((string)($file['name'] ?? ''))) ?: 'rasm', 0, 200);

        // Qayta kodlangandan keyin (aylantirish/kichraytirish) haqiqiy o'lchamlar.
        clearstatcache(true, $target);
        $final = @getimagesize($target) ?: $info;
        $info[0] = $final[0];
        $info[1] = $final[1];
        self::makeVariants($target);

        db()->prepare('INSERT INTO media (path, original_name, mime, size, width, height) VALUES (:p, :o, :m, :s, :w, :h)')
            ->execute([':p' => $path, ':o' => $originalName, ':m' => $mime, ':s' => (int)filesize($target), ':w' => $info[0], ':h' => $info[1]]);
        $id = (int)db()->lastInsertId();
        audit('upload', 'media', $id, ['file' => $originalName]);

        return ['id' => $id, 'path' => $path, 'url' => url($path), 'name' => $originalName, 'width' => $info[0], 'height' => $info[1]];
    }

    /** GD mavjud bo'lsa rasmni qayta kodlaydi. GD yo'q bo'lsa false qaytaradi. */
    private static function reencode(string $source, string $mime, string $target): bool
    {
        if (!extension_loaded('gd')) {
            return false;
        }
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($source),
            'image/png'  => @imagecreatefrompng($source),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
            'image/gif'  => @imagecreatefromgif($source),
            default      => false,
        };
        if (!$image) {
            return false;
        }
        // Telefon fotolari: EXIF yo'nalishi bo'yicha to'g'rilash.
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $orientation = (int)(@exif_read_data($source)['Orientation'] ?? 1);
            $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;
            if ($angle !== 0 && ($rotated = imagerotate($image, $angle, 0))) {
                $image = $rotated;
            }
        }
        // Juda katta rasmlarni 2560 px gacha kichraytirish.
        $image = self::fit($image, self::MAX_STORED);
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }
        $ok = match ($mime) {
            'image/jpeg' => imagejpeg($image, $target, 86),
            'image/png'  => imagepng($image, $target, 7),
            'image/webp' => function_exists('imagewebp') && imagewebp($image, $target, 84),
            'image/gif'  => imagegif($image, $target),
            default      => false,
        };
        return (bool)$ok;
    }

    private const MAX_STORED = 2560;

    /** Rasmni berilgan eng katta tomonga sig'dirish (kattalashtirmaydi). */
    private static function fit(\GdImage $image, int $max): \GdImage
    {
        $w = imagesx($image);
        $h = imagesy($image);
        if ($w <= $max && $h <= $max) {
            return $image;
        }
        $scale = $max / max($w, $h);
        $resized = imagescale($image, max(1, (int)round($w * $scale)), max(1, (int)round($h * $scale)), IMG_BICUBIC);
        if ($resized === false) {
            return $image;
        }
        return $resized;
    }

    /** Galereya uchun WebP variantlar (thumb 480, large 1600). GD yo'q bo'lsa — jim o'tkazib yuboriladi. */
    public static function makeVariants(string $absolutePath): void
    {
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            return;
        }
        $data = @file_get_contents($absolutePath);
        $image = $data !== false ? @imagecreatefromstring($data) : false;
        if (!$image) {
            return;
        }
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        foreach (MEDIA_VARIANTS as $size => $max) {
            $w = imagesx($image);
            $h = imagesy($image);
            $scale = min(1, $max / max($w, $h));
            $copy = imagescale($image, max(1, (int)round($w * $scale)), max(1, (int)round($h * $scale)), IMG_BICUBIC);
            if ($copy) {
                imagewebp($copy, media_variant_path($absolutePath, $size), $size === 'thumb' ? 72 : 80);
            }
        }
    }

    /** Fayl va uning variantlarini diskdan o'chirish (faqat uploads/ ichida). */
    public static function deleteFiles(string $relativePath): void
    {
        $uploadsRoot = realpath(ROOT_PATH . '/uploads');
        foreach (array_merge([$relativePath], array_map(static fn($s) => media_variant_path($relativePath, $s), array_keys(MEDIA_VARIANTS))) as $path) {
            $absolute = realpath(ROOT_PATH . '/' . $path);
            if ($absolute && $uploadsRoot && str_starts_with($absolute, $uploadsRoot . DIRECTORY_SEPARATOR) && is_file($absolute)) {
                @unlink($absolute);
            }
        }
    }

    /** Media qaysi joylarda ishlatilayotganini qaytaradi. */
    public static function usages(int $mediaId): array
    {
        $checks = [
            ['services', 'image_id', 'name_uz', 'Xizmat'],
            ['portfolio_items', 'image_id', 'title_uz', 'Portfolio'],
            ['clients', 'logo_id', 'name', 'Mijoz'],
            ['team_members', 'photo_id', 'name', 'Jamoa'],
            ['seo_pages', 'og_image_id', 'label', 'SEO'],
            ['ad_locations', 'cover_media_id', 'name_uz', 'Reklama nuqtasi (muqova)'],
        ];
        $stmt = db()->prepare('SELECT l.name_uz FROM ad_location_photos p JOIN ad_locations l ON l.id = p.location_id WHERE p.media_id = :id');
        $stmt->execute([':id' => $mediaId]);
        $photoUsages = array_map(static fn($r) => 'Reklama nuqtasi: ' . $r['name_uz'], $stmt->fetchAll());
        $usages = [];
        foreach ($checks as [$table, $column, $label, $type]) {
            $stmt = db()->prepare("SELECT {$label} AS label FROM {$table} WHERE {$column} = :id");
            $stmt->execute([':id' => $mediaId]);
            foreach ($stmt->fetchAll() as $row) {
                $usages[] = $type . ': ' . $row['label'];
            }
        }
        return array_values(array_unique(array_merge($usages, $photoUsages)));
    }
}

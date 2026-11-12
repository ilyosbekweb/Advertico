<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

$lang = (($_POST['lang'] ?? '') === 'ru') ? 'ru' : 'uz';

function fail(int $status, string $uz, string $ru): never
{
    global $lang;
    http_response_code($status);
    echo json_encode(['success' => false, 'message' => $lang === 'ru' ? $ru : $uz], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    fail(405, 'Faqat POST so‘rovi qabul qilinadi.', 'Принимаются только POST-запросы.');
}

// Bot-spam tuzog'i: yashirin maydon to'ldirilgan bo'lsa, so'rovni rad etamiz.
if (!empty($_POST['website'])) {
    fail(400, 'So‘rov rad etildi.', 'Запрос отклонён.');
}

$name = trim((string)($_POST['name'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$serviceInput = trim((string)($_POST['service'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

if ($name === '' || mb_strlen($name) > 120 || $phone === '' || mb_strlen($phone) > 40
    || !preg_match('/^[0-9+()\-\s]{7,40}$/', $phone)) {
    fail(422, 'Ism va telefon raqamini to‘g‘ri kiriting.', 'Укажите корректные имя и номер телефона.');
}
if (mb_strlen($serviceInput) > 160 || mb_strlen($message) > 3000) {
    fail(422, 'Yuborilgan ma’lumot juda uzun.', 'Отправленные данные слишком длинные.');
}

try {
    require dirname(__DIR__) . '/app/bootstrap.php';
    $pdo = db();
    $ip = client_ip();

    // Rate limiting: bitta IP'dan bir soatda N tadan ortiq ariza qabul qilinmaydi.
    $limit = max(1, (int)setting('rate_limit_per_hour', '5'));
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM applications WHERE ip_address = :ip AND created_at > (NOW() - INTERVAL 1 HOUR)');
    $stmt->execute([':ip' => $ip]);
    if ((int)$stmt->fetchColumn() >= $limit) {
        fail(429, 'Juda ko‘p so‘rov yuborildi. Iltimos, keyinroq urinib ko‘ring yoki qo‘ng‘iroq qiling.', 'Слишком много заявок. Попробуйте позже или позвоните нам.');
    }

    // Xizmat: yangi forma slug yuboradi; eski forma (keshdagi sahifa) nom yuborishi mumkin.
    $serviceId = null;
    $serviceName = $serviceInput;
    if ($serviceInput !== '') {
        $stmt = $pdo->prepare('SELECT id, name_uz FROM services WHERE slug = :s OR name_uz = :n OR name_ru = :r LIMIT 1');
        $stmt->execute([':s' => $serviceInput, ':n' => $serviceInput, ':r' => $serviceInput]);
        if ($service = $stmt->fetch()) {
            $serviceId = (int)$service['id'];
            $serviceName = (string)$service['name_uz'];
        }
    }

    // Reklama joyi: faqat nashr qilingan joy qabul qilinadi. Yaroqsiz ID bo'lsa ham ariza yo'qolmaydi —
    // joy bog'lanmasdan saqlanadi.
    $location = null;
    $locationInput = trim((string)($_POST['location_id'] ?? ''));
    if ($locationInput !== '' && ctype_digit($locationInput)) {
        $location = AdLocations::findPublished((int)$locationInput);
        if ($location && $serviceId === null && $location['service_id'] !== null) {
            $stmt = $pdo->prepare('SELECT id, name_uz FROM services WHERE id = :id');
            $stmt->execute([':id' => $location['service_id']]);
            if ($service = $stmt->fetch()) {
                $serviceId = (int)$service['id'];
                $serviceName = (string)$service['name_uz'];
            }
        }
    }

    $stmt = $pdo->prepare('INSERT INTO applications (name, phone, service, service_id, location_id, message, lang, ip_address, user_agent)
        VALUES (:name, :phone, :service, :service_id, :location_id, :message, :lang, :ip, :ua)');
    $stmt->execute([
        ':name'       => $name,
        ':phone'      => $phone,
        ':service'    => mb_substr($serviceName, 0, 160),
        ':service_id' => $serviceId,
        ':location_id' => $location ? (int)$location['id'] : null,
        ':message'    => $message,
        ':lang'       => $lang,
        ':ip'         => $ip,
        ':ua'         => mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
    ]);
    $applicationId = (int)$pdo->lastInsertId();
} catch (Throwable $e) {
    error_log('Advertico contact error: ' . $e->getMessage());
    fail(500, 'Server xatosi. Keyinroq qayta urinib ko‘ring.', 'Ошибка сервера. Попробуйте позже.');
}

// Murojaat bazaga saqlandi; Telegram sozlanmagan yoki ishlamasa ham yo'qolmaydi.
$telegramSent = false;
if (Telegram::isEnabled()) {
    $telegramSent = Telegram::send(Telegram::applicationMessage([
        'id' => $applicationId, 'name' => $name, 'phone' => $phone, 'service' => $serviceName, 'message' => $message,
        'location_id' => $location['id'] ?? null, 'location_name' => $location['name_uz'] ?? null,
        'lat' => $location['lat'] ?? null, 'lng' => $location['lng'] ?? null,
    ]));
    if ($telegramSent) {
        db()->prepare('UPDATE applications SET telegram_sent = 1 WHERE id = :id')->execute([':id' => $applicationId]);
    }
}

echo json_encode(['success' => true, 'telegram_sent' => $telegramSent], JSON_UNESCAPED_UNICODE);

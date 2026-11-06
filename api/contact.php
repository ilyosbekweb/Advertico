<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function respond(int $status, array $data): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['success' => false, 'message' => 'Faqat POST so‘rovi qabul qilinadi.']);
}

// Oddiy bot-spam tuzog‘i: yashirin maydon to‘ldirilgan bo‘lsa, so‘rovni rad etamiz.
if (!empty($_POST['website'])) {
    respond(400, ['success' => false, 'message' => 'So‘rov rad etildi.']);
}

$name = trim((string)($_POST['name'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$service = trim((string)($_POST['service'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

if ($name === '' || mb_strlen($name) > 120 || $phone === '' || mb_strlen($phone) > 40) {
    respond(422, ['success' => false, 'message' => 'Ism va telefon raqamini to‘g‘ri kiriting.']);
}
if (mb_strlen($service) > 120 || mb_strlen($message) > 3000) {
    respond(422, ['success' => false, 'message' => 'Yuborilgan ma’lumot juda uzun.']);
}

$config = require dirname(__DIR__) . '/config.php';
$db = $config['db'];

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $db['host'], $db['name'], $db['charset']);
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $stmt = $pdo->prepare('INSERT INTO applications (name, phone, service, message, ip_address) VALUES (:name, :phone, :service, :message, :ip)');
    $stmt->execute([
        ':name' => $name,
        ':phone' => $phone,
        ':service' => $service,
        ':message' => $message,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
    $applicationId = (int)$pdo->lastInsertId();
} catch (Throwable $e) {
    error_log('Advertico DB error: ' . $e->getMessage());
    respond(500, ['success' => false, 'message' => 'Server xatosi. Keyinroq qayta urinib ko‘ring.']);
}

$telegram = $config['telegram'];
$telegramSent = false;
if (!empty($telegram['bot_token']) && !empty($telegram['chat_id']) &&
    $telegram['bot_token'] !== 'PASTE_TELEGRAM_BOT_TOKEN_HERE' &&
    $telegram['chat_id'] !== 'PASTE_ADMIN_CHAT_ID_HERE') {

    $text = "🆕 <b>Yangi murojaat — Advertico</b>\n\n"
        . "🆔 Raqam: #{$applicationId}\n"
        . "👤 Ism: " . htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n"
        . "📞 Telefon: " . htmlspecialchars($phone, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n"
        . "🛠 Xizmat: " . htmlspecialchars($service, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "\n"
        . "💬 Xabar: " . htmlspecialchars($message !== '' ? $message : 'Kiritilmagan', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $payload = http_build_query([
        'chat_id' => $telegram['chat_id'],
        'text' => $text,
        'parse_mode' => 'HTML',
    ]);
    $ch = curl_init('https://api.telegram.org/bot' . $telegram['bot_token'] . '/sendMessage');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
    ]);
    $telegramResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $telegramData = is_string($telegramResponse) ? json_decode($telegramResponse, true) : null;
    $telegramSent = !$curlError && $httpCode === 200 && !empty($telegramData['ok']);
    if (!$telegramSent) {
        error_log('Advertico Telegram error: ' . ($curlError ?: (string)$telegramResponse));
    }
}

// Murojaat bazaga saqlandi; Telegram sozlanmagan yoki vaqtincha ishlamasa ham yo‘qolmaydi.
respond(200, ['success' => true, 'telegram_sent' => $telegramSent]);

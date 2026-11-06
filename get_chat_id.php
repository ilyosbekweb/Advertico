
<?php
$config = require __DIR__ . '/config.php';

$token = $config['telegram']['bot_token'];

$url = "https://api.telegram.org/bot{$token}/getUpdates";

$response = file_get_contents($url);

if ($response === false) {
    exit('Telegram API bilan bog‘lanib bo‘lmadi.');
}

$data = json_decode($response, true);

if (empty($data['ok'])) {
    exit('Telegram API xatolik qaytardi.');
}

header('Content-Type: text/plain; charset=utf-8');

foreach ($data['result'] ?? [] as $update) {
    $chat = $update['message']['chat'] ?? null;

    if ($chat && in_array($chat['type'], ['group', 'supergroup'], true)) {
        echo "Guruh: " . ($chat['title'] ?? '') . "\n";
        echo "Chat ID: " . $chat['id'] . "\n\n";
    }
}

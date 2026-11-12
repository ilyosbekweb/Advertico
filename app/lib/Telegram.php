<?php
declare(strict_types=1);

final class Telegram
{
    private const PLACEHOLDERS = ['', 'PASTE_TELEGRAM_BOT_TOKEN_HERE', 'PASTE_ADMIN_CHAT_ID_HERE'];

    public static function token(): string
    {
        $token = trim((string)config('telegram.bot_token', ''));
        return in_array($token, self::PLACEHOLDERS, true) ? '' : $token;
    }

    public static function chatId(): string
    {
        $chat = trim(setting('telegram_chat_id', (string)config('telegram.chat_id', '')));
        return in_array($chat, self::PLACEHOLDERS, true) ? '' : $chat;
    }

    public static function isConfigured(): bool
    {
        return self::token() !== '' && self::chatId() !== '';
    }

    public static function isEnabled(): bool
    {
        return setting('telegram_enabled', '1') === '1' && self::isConfigured();
    }

    /** HTML parse_mode bilan xabar yuborish. Xato bo'lsa $error to'ldiriladi. */
    public static function send(string $html, ?string &$error = null): bool
    {
        $error = null;
        if (!self::isConfigured()) {
            $error = 'Telegram sozlanmagan (bot token yoki chat ID yo‘q).';
            return false;
        }
        if (!function_exists('curl_init')) {
            $error = 'PHP cURL kengaytmasi yoqilmagan.';
            return false;
        }
        $ch = curl_init('https://api.telegram.org/bot' . self::token() . '/sendMessage');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'chat_id'    => self::chatId(),
                'text'       => $html,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => 'true',
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = is_string($response) ? json_decode($response, true) : null;
        if ($curlError === '' && $httpCode === 200 && !empty($data['ok'])) {
            return true;
        }
        // Tokenni hech qachon logga yozmaymiz.
        $error = $curlError !== '' ? $curlError : (string)($data['description'] ?? ('HTTP ' . $httpCode));
        error_log('Advertico Telegram error: ' . $error);
        return false;
    }

    /** Ariza uchun xabar matni. */
    public static function applicationMessage(array $app): string
    {
        $h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return "🆕 <b>Yangi murojaat — Advertico</b>\n\n"
            . '🆔 Raqam: #' . (int)$app['id'] . "\n"
            . '👤 Ism: ' . $h($app['name']) . "\n"
            . '📞 Telefon: ' . $h($app['phone']) . "\n"
            . '🛠 Xizmat: ' . $h($app['service'] !== '' ? $app['service'] : 'Tanlanmagan') . "\n"
            . (!empty($app['location_name']) ? '📍 Reklama joyi: ' . $h($app['location_name']) . ' (#' . (int)$app['location_id'] . ')'
                . (isset($app['lat'], $app['lng']) ? "\n🗺 https://maps.google.com/?q=" . rawurlencode($app['lat'] . ',' . $app['lng']) : '') . "\n" : '')
            . '💬 Xabar: ' . $h(($app['message'] ?? '') !== '' ? $app['message'] : 'Kiritilmagan');
    }
}

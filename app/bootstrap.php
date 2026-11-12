<?php
declare(strict_types=1);

/*
 * Ilovaning yagona kirish nuqtasi: public sahifalar, API va admin panel shu faylni yuklaydi.
 */

define('APP_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__));

$configFile = APP_PATH . '/config.php';
if (!is_file($configFile)) {
    throw new RuntimeException('app/config.php topilmadi. app/config.example.php faylidan nusxa oling.');
}

$GLOBALS['__config'] = require $configFile;

$debug = !empty($GLOBALS['__config']['debug']);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
date_default_timezone_set($GLOBALS['__config']['timezone'] ?? 'Asia/Tashkent');
mb_internal_encoding('UTF-8');

require APP_PATH . '/lib/helpers.php';
require APP_PATH . '/lib/Content.php';
require APP_PATH . '/lib/Telegram.php';
require APP_PATH . '/lib/Geo.php';
require APP_PATH . '/lib/AdLocations.php';

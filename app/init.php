<?php
if (!is_dir(ini_get('session.save_path'))) {
    $sessionPath = dirname(__DIR__) . '/sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0700);
    }
    session_save_path($sessionPath);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

date_default_timezone_set('Asia/Manila');

if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 3600);
} 

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/core/controller.php';
require_once __DIR__ . '/core/helper.php';
require_once __DIR__ . '/core/mailer.php';

Helper::loadEnv(__DIR__ . '/../.env');

define('API_SECRET_KEY', 'BPC_API_SECURE_KEY_2026');

$adminBypass = $_ENV['ADMIN_BYPASS_KEY'] ?? '';
define('ADMIN_BYPASS_KEY', $adminBypass);

$envKey = getenv('KIOSK_SECRET_KEY') ?: ($_ENV['KIOSK_SECRET_KEY'] ?? null);

if (isset($_ENV['KIOSK_SECRET_KEY'])) {
    define('KIOSK_SECRET_KEY', $_ENV['KIOSK_SECRET_KEY']);
} else {
    define('KIOSK_SECRET_KEY', '');
}

if (!defined('API_ACCESS')) {
    define('API_ACCESS', false);
}

require_once __DIR__ . '/models/user.php';
require_once __DIR__ . '/models/activitylog.php';
require_once __DIR__ . '/models/notification.php';
require_once __DIR__ . '/models/holiday.php';

if (!function_exists('clean')) {
    function clean($data) { return Helper::clean($data); }
}

if (!function_exists('csrf_field')) {
    function csrf_field() { Helper::csrfInput(); }
}

if (!function_exists('isAdmin')) {
    function isAdmin() { return Helper::isAdmin(); }
}
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() { return Helper::isLoggedIn(); }
}
if (!function_exists('jsonResponse')) {
    function jsonResponse($s, $m, $d=null) { Helper::jsonResponse($s, $m, $d); }
}
if (!function_exists('sendEmail')) {
    function sendEmail($to, $sub, $msg) { return Mailer::send($to, $sub, $msg); }
}

function db() {
    $database = Database::getInstance(); 
    return $database->conn;
}

spl_autoload_register(function ($class) {
    $libraries = [
        'chillerlan\\QRCode\\' => __DIR__ . '/libraries/php-qrcode/src/',
        'chillerlan\\Settings\\' => __DIR__ . '/libraries/php-settings-container/src/',
        'chillerlan\\' => __DIR__ . '/libraries/php-qrcode/src/',

    ];

    foreach ($libraries as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) continue;

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

?>
<?php
require_once 'app/init.php';

$accessKey = $_GET['key'] ?? '';
$expectedKey = defined('KIOSK_SECRET_KEY') ? KIOSK_SECRET_KEY : '';

if (empty($expectedKey) || $accessKey !== $expectedKey) {
    http_response_code(403);
    die("<h1>403 Forbidden</h1>Unauthorized Access: Kiosk key is required.");
}

$holidayModel = new Holiday();
$settings = $holidayModel->getSystemSettings();

$terminal_pin = $settings['qr_interface_pin'] ?? '1111';

$controller = new Controller();
$controller->view('display_view', [
    'terminal_pin' => $terminal_pin
]);
?>
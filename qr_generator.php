<?php
require_once 'app/init.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

$token = $_GET['token'] ?? 'NoToken';
$shouldDownload = isset($_GET['download']) && $_GET['download'] == '1';

ob_clean();

$options = new QROptions([
    'version'    => 5,
    'outputType' => QRCode::OUTPUT_IMAGE_PNG, 
    'eccLevel'   => 0,
    'scale'      => 10,
]);

if ($shouldDownload) {
    header('Content-Description: File Transfer');
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="BPC_Attendance_QR_' . $token . '.png"');
} else {
    header('Content-Type: image/png');
}

try {
    echo (new QRCode($options))->render($token);
} catch (Exception $e) {
    error_log("QR Error: " . $e->getMessage());
}
exit;
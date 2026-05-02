<?php
require_once '../app/init.php'; 
require_once '../app/core/mailer.php';

if (!class_exists('Attendance')) {
    require_once __DIR__ . '/../app/models/attendance.php'; 
}

header('Content-Type: application/json');
if (ob_get_level()) ob_end_clean();

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $qrToken = $input['qr_token'] ?? '';

    if (empty($qrToken)) throw new Exception('No QR data received.');

    $db = Database::getInstance();
   
    $stmt = $db->query(
    "SELECT id, first_name, email FROM users 
     WHERE (qr_token = ? OR faculty_id = ?) 
     AND status = 'active' LIMIT 1", 
    [$qrToken, $qrToken], 
    "ss"
);
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) throw new Exception('Invalid QR Code.');

    $otp = sprintf("%06d", mt_rand(1, 999999));
    $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $db->query("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE id = ?", [$otp, $expiry, $user['id']], "ssi");

    $subject = "Your Attendance Verification Code";
    $message = "Hello " . $user['first_name'] . ",<br><br>Your verification code for attendance is: <b>$otp</b>";
    
    if (Mailer::send($user['email'], $subject, $message)) {
        echo json_encode([
            'success' => true,
            'requires_otp' => true,
            'user_id' => $user['id'],
            'message' => "OTP sent to your email."
        ]);
    } else {
        throw new Exception('Failed to send email. Check your SMTP settings.');
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
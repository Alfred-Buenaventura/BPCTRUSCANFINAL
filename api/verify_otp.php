<?php
require_once '../app/init.php'; 

if (!class_exists('Attendance')) {
    require_once __DIR__ . '/../app/models/attendance.php'; 
}

header('Content-Type: application/json');
if (ob_get_level()) ob_end_clean();

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $userId = $input['user_id'] ?? '';
    $otpEntered = $input['otp_code'] ?? '';

    if (empty($userId) || empty($otpEntered)) {
        throw new Exception('Please enter the verification code.');
    }

    $db = Database::getInstance();
    
    $stmt = $db->query("SELECT id, first_name, otp_code, otp_expiry FROM users WHERE id = ? AND status = 'active' LIMIT 1", [$userId], "i");
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        throw new Exception('User session not found. Please scan again.');
    }

    if ($user['otp_code'] !== $otpEntered) {
        throw new Exception('Invalid code. Please check your email.');
    }

    if (strtotime($user['otp_expiry']) < time()) {
        throw new Exception('Verification code has expired. Please scan your QR again.');
    }

    $db->query("UPDATE users SET otp_code = NULL, otp_expiry = NULL WHERE id = ?", [$userId], "i");

    $attendanceModel = new Attendance();
    $result = $attendanceModel->logAttendance($userId, 'QR-OTP');

    echo json_encode([
        'success' => $result['success'],
        'message' => $result['message'],
        'user_name' => $user['first_name']
    ]);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
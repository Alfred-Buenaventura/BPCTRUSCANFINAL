<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

define('API_ACCESS', true);
session_start();
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../app/init.php';
header('Content-Type: application/json');

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput);

if (!$data || !isset($data->user_id)) {
    echo json_encode(['success' => false, 'message' => "Invalid input."]);
    exit;
}

$db = Database::getInstance(); 
$scannedId = (int)$data->user_id; 

$isSynced = isset($data->timestamp) && !empty($data->timestamp);
$effectiveTime = $isSynced ? strtotime($data->timestamp) : time();

$today = date('Y-m-d', $effectiveTime);
$now = date('H:i:s', $effectiveTime);
$dayOfWeek = date('l', $effectiveTime);

// maps the fingerprint id to the user id
$check = $db->query("SELECT user_id FROM user_fingerprints WHERE id = ?", [$scannedId], "i");
$res = $check->get_result()->fetch_assoc();
$userId = $res ? $res['user_id'] : $scannedId;

$userStmt = $db->query("SELECT * FROM users WHERE id = ? AND status = 'active'", [$userId], "i");
$user = $userStmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => "User not found."]);
    exit;
}

// find the schedule of the user
$schedQuery = "SELECT id FROM class_schedules 
               WHERE user_id = ? AND day_of_week = ? AND status = 'approved' 
               AND (ABS(TIMESTAMPDIFF(MINUTE, start_time, ?)) <= 60 
                    OR (start_time <= ? AND end_time >= ?))
               LIMIT 1";
$schedStmt = $db->query($schedQuery, [$userId, $dayOfWeek, $now, $now, $now], "issss");
$currentSched = $schedStmt->get_result()->fetch_assoc();
$scheduleId = $currentSched ? $currentSched['id'] : null;

$statusText = "";
$isWarning = false;

// fetches the exisiting records for today
$lastRecordStmt = $db->query("SELECT * FROM attendance_records WHERE user_id = ? AND date = ? ORDER BY id DESC LIMIT 1", [$userId, $today], "is");
$lastRecord = $lastRecordStmt->get_result()->fetch_assoc();

$hour = (int)date('H', $effectiveTime);
$updateCol = '';

if ($lastRecord) {
    // AM/Morning sessions
    if ($hour < 12) {
        if (empty($lastRecord['am_in']) || $lastRecord['am_in'] == '00:00:00') {
            $updateCol = 'am_in';
        } elseif (empty($lastRecord['am_out']) || $lastRecord['am_out'] == '00:00:00') {
            $updateCol = 'am_out';
        } else {
            $updateCol = 'pm_in'; 
        }
    } else {
        // PM/Afternoon sessions
        if (empty($lastRecord['pm_in']) || $lastRecord['pm_in'] == '00:00:00') {
            $updateCol = 'pm_in';
        } else {
            $updateCol = 'pm_out';
        }
    }

    $compareCol = $updateCol;
    if ($updateCol == 'am_out') $compareCol = 'am_in';
    if ($updateCol == 'pm_out') $compareCol = 'pm_in';

    $existingTime = !empty($lastRecord[$compareCol]) ? strtotime($lastRecord[$compareCol]) : 0;
    if ($existingTime > 0 && (abs($effectiveTime - $existingTime) < 60)) {
        echo json_encode([
            'success' => true, 
            'message' => "Scan ignored to prevent duplicates", 
            'data' => [
                "name" => $user['first_name'] . ' ' . $user['last_name'],
                "status" => "Duplicate Scan", 
                "time" => date('h:i A', $effectiveTime),
                "is_warning" => true
            ]
        ]);
        exit;
    }

    $db->query("UPDATE attendance_records SET $updateCol = ?, method = 'Fingerprint' WHERE id = ?", [$now, $lastRecord['id']], "si");
    $statusText = str_replace('_', ' ', strtoupper($updateCol));

} else {
    $finalStatus = $scheduleId ? 'Present' : 'Unscheduled';
    if ($scheduleId) {
        $sData = $db->query("SELECT start_time FROM class_schedules WHERE id = ?", [$scheduleId], "i")->get_result()->fetch_assoc();
        if ($sData && ($effectiveTime - strtotime($sData['start_time'])) / 60 > 15) $finalStatus = 'Late';
    }

    $initialCol = ($hour < 12) ? 'am_in' : 'pm_in';
    $db->query("INSERT INTO attendance_records (user_id, date, $initialCol, schedule_id, status, method) VALUES (?, ?, ?, ?, ?, 'Fingerprint')", 
               [$userId, $today, $now, $scheduleId, $finalStatus], "issis");
    
    $statusText = str_replace('_', ' ', strtoupper($initialCol));
}

if (!$isWarning && !empty($user['email']) && (!isset($user['email_notifications_enabled']) || $user['email_notifications_enabled'])) {
    $formattedTime = date('h:i A', $effectiveTime);
    $subject = "Attendance Notification: $statusText Recorded";
    
    $emailBody = "
    <html>
    <body style='font-family: Arial, sans-serif; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;'>
            <div style='background: #059669; color: white; padding: 20px; text-align: center;'>
                <h2 style='margin: 0;'>BPC TruScan Attendance</h2>
            </div>
            <div style='padding: 30px;'>
                <p>Hello <strong>{$user['first_name']}</strong>,</p>
                <p>Your attendance has been recorded for today, <strong>" . date('M d, Y', $effectiveTime) . "</strong>.</p>
                <div style='background: #f3f4f6; padding: 15px; border-radius: 6px; text-align: center; margin: 20px 0;'>
                    <span style='font-size: 1.2rem; color: #1f2937;'>Status: <strong>$statusText</strong></span><br>
                    <span style='font-size: 1.5rem; color: #059669;'>Time: <strong>$formattedTime</strong></span>
                </div>
            </div>
            <div style='background: #f9fafb; padding: 15px; text-align: center; font-size: 0.75rem; color: #9ca3af;'>
                &copy; " . date('Y') . " Bulacan Polytechnic College
            </div>
        </div>
    </body>
    </html>";

    sendEmail($user['email'], $subject, $emailBody);
}

echo json_encode([
    'success' => true, 
    'message' => "Attendance processed", 
    'data' => [
        "name" => $user['first_name'] . ' ' . $user['last_name'],
        "status" => $statusText,
        "time" => date('h:i A', $effectiveTime),
        "is_warning" => $isWarning
    ]
]);
?>
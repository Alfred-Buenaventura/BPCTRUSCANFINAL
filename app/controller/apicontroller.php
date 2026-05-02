<?php
require_once __DIR__ . '/../core/controller.php';

class ApiController extends Controller {

    public function __construct() {
    }

    private function verifyApiKey() {
    $providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

    if (!defined('API_SECRET_KEY') || empty(API_SECRET_KEY) || !hash_equals(API_SECRET_KEY, $providedKey)) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            'success' => false, 
            'message' => 'Unauthorized: Invalid or Missing API Key.'
        ]);
        exit;
    }
}

    public function recordAttendance() {
    $this->verifyApiKey();
    header('Content-Type: application/json');
    
    $data = json_decode(file_get_contents('php://input'));

    if (!$data || !isset($data->user_id)) {
        echo json_encode(['success' => false, 'message' => "Invalid user ID."]);
        exit;
    }

    $userId = (int)$data->user_id;
    $db = Database::getInstance();

    $userQuery = $db->query("SELECT * FROM users WHERE id = ?", [$userId], "i");
    $user = $userQuery->get_result()->fetch_assoc();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => "User not found."]);
        exit;
    }

    $today = date('Y-m-d');
    $now = date('H:i:s');
    $status = "";

    $stmt = $db->query("SELECT id, am_in, pm_out FROM attendance_records WHERE user_id = ? AND date = ?", [$userId, $today], "is");
    $record = $stmt->get_result()->fetch_assoc();

    if ($record) {
        $db->query("UPDATE attendance_records SET pm_out = ? WHERE id = ?", [$now, $record['id']], "si");
        $status = "Time Out";

    } else {
        $timeInStatus = "On-time";
        $dayOfWeek = date('l'); 

        $graceStmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'late_threshold_minutes'");
        $graceRow = $graceStmt->get_result()->fetch_assoc();
        $graceMinutes = $graceRow ? (int)$graceRow['setting_value'] : 15;

        $scheduleStmt = $db->query(
            "SELECT MIN(start_time) AS first_class_start 
             FROM class_schedules 
             WHERE user_id = ? AND day_of_week = ? AND status = 'approved'",
            [$userId, $dayOfWeek], "is"
        );
        $schedule = $scheduleStmt->get_result()->fetch_assoc();

        if ($schedule && $schedule['first_class_start']) {
            $firstClassStart = strtotime($schedule['first_class_start']);
            $currentTime = strtotime($now);
            $gracePeriodSeconds = $graceMinutes * 60; 

            if ($currentTime > ($firstClassStart + $gracePeriodSeconds)) {
                $timeInStatus = "Late";
            }
        }

        $db->query("INSERT INTO attendance_records (user_id, date, am_in, status) VALUES (?, ?, ?, ?)", 
            [$userId, $today, $now, $timeInStatus], "isss");
        
        $status = ($timeInStatus === "Late") ? "Time In (Late)" : "Time In";
    }

    echo json_encode([
        'success' => true,
        'message' => "Attendance recorded",
        'data' => [
            "type"   => "attendance",
            "name"   => $user['first_name'] . ' ' . $user['last_name'],
            "status" => $status,
            "time"   => date('h:i A', strtotime($now)),
            "date"   => date('l, F j, Y'),
            "full_timestamp" => date('c')
        ]
    ]);
    exit;
}

    // this function is the one that fetches the fingerprint templates from the database to be used for the bridge app
    public function getFingerprintTemplates() {
        $this->verifyApiKey(); // SECURED
        header('Content-Type: application/json');
        $db = Database::getInstance();
        
        $sql = "SELECT id, fingerprint_data FROM users WHERE fingerprint_data IS NOT NULL AND status = 'active'";
        $result = $db->query($sql);

        if ($result) {
            $templates = [];
            $res = $result->get_result();
            while ($row = $res->fetch_assoc()) {
                $templates[] = [
                    'id' => $row['id'], 
                    'fingerprint_template' => $row['fingerprint_data'] 
                ];
            }
            echo json_encode(['success' => true, 'message' => "Templates fetched", 'data' => $templates]);
        } else {
            echo json_encode(['success' => false, 'message' => "Database error"]);
        }
        exit;
    }

    // handles the notification reading for a single notif
    public function markNotificationRead() {
        $this->requireLogin();
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) { 
            echo json_encode(['success'=>false, 'message' => 'Unauthorized']); 
            exit; 
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $notifId = $data['notification_id'] ?? null;
        
        if (!$notifId) {
            echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
            exit;
        }
        
        $db = Database::getInstance();
        $db->query("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$notifId, $_SESSION['user_id']], "ii");
        
        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
        exit;
    }
    
    // for all notifications
    public function markAllNotificationsRead() {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        return;
    }

    try {
        $userId = $_SESSION['user_id'];
        $db = Database::getInstance();

        $query = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
        $db->query($query, [$userId], "i");

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

public function deleteAllNotifications() {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false]); return;
    }

    $db = Database::getInstance();
    $db->query("DELETE FROM notifications WHERE user_id = ?", [$_SESSION['user_id']], "i");
    echo json_encode(['success' => true]);
}

public function deleteNotification() {
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents('php://input'));
    
    if (!isset($data->notification_id) || !isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }

    $db = Database::getInstance();
    $notifId = (int)$data->notification_id;
    $userId = (int)$_SESSION['user_id'];

    $db->query("DELETE FROM notifications WHERE id = ? AND user_id = ?", [$notifId, $userId], "ii");

    echo json_encode(['success' => true]);
    exit;
}

public function updateDtrBatch() {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $db = Database::getInstance();
    $facultyId = $data['faculty_id'] ?? null;
    $month = (int)$data['month'];
    $year = (int)$data['year'];

    $userRow = $db->query("SELECT id FROM users WHERE faculty_id = ?", [$facultyId], "s")->get_result()->fetch_assoc();
    $userId = $userRow['id'];

    try {
        foreach ($data['updates'] as $item) {
    $date = sprintf('%04d-%02d-%02d', $year, $month, (int)$item['day']);

    $holidayCheck = $db->query("SELECT id FROM holidays WHERE holiday_date = ?", [$date], "s")->get_result();
    if ($holidayCheck->num_rows > 0) {
        continue;
    }

    $ai = !empty($item['am_in']) ? $item['am_in'] : null;
    $ao = !empty($item['am_out']) ? $item['am_out'] : null;

    $normalizePM = function($val) {
        if (empty($val) || $val == '00:00:00') return null;
        $ts = strtotime($val);
        $hour = (int)date('H', $ts);
        if ($hour > 0 && $hour < 12) {
            return date('H:i:s', strtotime($val . ' PM'));
        }
        return date('H:i:s', $ts);
    };

    $pi = $normalizePM($item['pm_in'] ?? null);
    $po = $normalizePM($item['pm_out'] ?? null);

    $check = $db->query("SELECT id FROM attendance_records WHERE user_id = ? AND date = ?", [$userId, $date], "is")->get_result();

    if ($check->num_rows > 0) {
        $db->query("UPDATE attendance_records SET am_in=?, am_out=?, pm_in=?, pm_out=?, method='Manual' WHERE user_id=? AND date=?", 
                   [$ai, $ao, $pi, $po, $userId, $date], "ssssis");
    } else {
        $db->query("INSERT INTO attendance_records (user_id, date, am_in, am_out, pm_in, pm_out, method) VALUES (?, ?, ?, ?, ?, ?, 'Manual')", 
                   [$userId, $date, $ai, $ao, $pi, $po], "isssss");
    }
}
        echo json_encode(['success' => true]);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
}
}
<?php
require_once __DIR__ . '/../core/controller.php';
require_once __DIR__ . '/../libraries/SimpleXLSXGen.php';

use Shuchkin\SimpleXLSXGen;

class AttendanceController extends Controller {

public function index() {
    $this->requireLogin(); 
    $attModel = $this->model('Attendance');
    $userModel = $this->model('User');
        
    $data = [
        'isAdmin' => ($_SESSION['role'] === 'Admin'),
        'error' => ''
    ];

    if ($data['isAdmin']) {
        $data['pageTitle'] = 'Attendance Reports';
        $data['pageSubtitle'] = 'Manage and monitor personnel logs';
    } else {
        $data['pageTitle'] = 'My Attendance';
        $data['pageSubtitle'] = 'View your personal time records';
    }

    $filters = [
        'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
        'end_date'   => $_GET['end_date']   ?? date('Y-m-d'),
        'search'     => $_GET['search']     ?? '',
        'user_id'    => $_GET['user_id']    ?? ''
    ];

    if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
        $this->exportHistoryExcel();
    }

    if ($data['isAdmin']) {
        $data['allUsers'] = $userModel->getAllStaff();
        $data['stats'] = $attModel->getStats(); 
    } else {
        $filters['user_id'] = $_SESSION['user_id'];
        $data['stats'] = $attModel->getStats($_SESSION['user_id']);
    }

    $data['records'] = $attModel->getRecords($filters);
    $data['filters'] = $filters;

    $this->view('attendance_view', $data);
}

public function history() {
    $this->requireLogin();
    $attModel = $this->model('Attendance');
    $userModel = $this->model('User'); 

    $isAdmin = ($_SESSION['role'] === 'Admin');

    $filters = [
        'start_date'  => $_GET['start_date'] ?? date('Y-m-01'),
        'end_date'    => $_GET['end_date']   ?? date('Y-m-d'),
        'status_type' => $_GET['status_type'] ?? '',
        'user_id'     => ($isAdmin) ? ($_GET['user_id'] ?? 'all') : $_SESSION['user_id']
    ];

    if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
        if (ob_get_length()) ob_end_clean();
        $this->exportHistoryExcel();
        exit(); 
    }

    $allRecords = $attModel->getUserHistory($filters['user_id'], $filters);

    $summary = ['present' => 0, 'late' => 0, 'absent' => 0, 'office' => 0];
    foreach ($allRecords as $rec) {
        if (stripos($rec['status'], 'Late') !== false) $summary['late']++;
        elseif ($rec['status'] === 'Present') $summary['present']++;
        elseif ($rec['status'] === 'Absent') $summary['absent']++;
        if (isset($rec['duty_type']) && (stripos($rec['duty_type'], 'Office') !== false)) $summary['office']++;
    }

    $data = [
        'pageTitle'    => 'Attendance History',
        'pageSubtitle' => 'View a comprehensive record logs of all user attendance records.',
        'records'      => $allRecords,
        'summary'      => $summary,
        'filters'      => $filters,
        'isAdmin'      => $isAdmin,
        'allUsers'     => $isAdmin ? $userModel->getAllActive() : [] 
    ];

    $this->view('attendance_history_view', $data);
}

private function calculateClampedHours($log, $dateStr) {
    if (empty($log['time_in']) || empty($log['time_out'])) return 0;
    if (empty($log['sched_start']) || empty($log['sched_end'])) return 0;

    $actualIn = strtotime("$dateStr " . $log['time_in']);
    $actualOut = strtotime("$dateStr " . $log['time_out']);
    $schedStart = strtotime("$dateStr " . $log['sched_start']);
    $schedEnd = strtotime("$dateStr " . $log['sched_end']);

    $effectiveIn = max($actualIn, $schedStart);
    $effectiveOut = min($actualOut, $schedEnd);

    $durationSeconds = $effectiveOut - $effectiveIn;
    if ($durationSeconds <= 0) return 0;

    return round($durationSeconds / 60) * 60;
}

public function getAttendanceSummary() {
    $this->requireLogin();
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    try {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'];
        $isAdmin = ($_SESSION['role'] === 'Admin');
        $type = $_GET['type'] ?? 'entries';
        $today = date('Y-m-d');
        
        $whereClauses = [];
        $params = [];
        $types = "";

        if ($type === 'entries') {
            $whereClauses[] = "(a.am_in > '00:00:00' OR a.pm_in > '00:00:00' OR a.time_in > '00:00:00')";
        } elseif ($type === 'exits') {
            $whereClauses[] = "(a.am_out > '00:00:00' OR a.pm_out > '00:00:00' OR a.time_out > '00:00:00')";
        } elseif ($type === 'present') {
            $whereClauses[] = "(a.am_in > '00:00:00' OR a.pm_in > '00:00:00' OR a.time_in > '00:00:00')";
            $whereClauses[] = "(a.am_out = '00:00:00' OR a.am_out IS NULL)";
            $whereClauses[] = "(a.pm_out = '00:00:00' OR a.pm_out IS NULL)";
            $whereClauses[] = "(a.time_out = '00:00:00' OR a.time_out IS NULL)";
        } elseif ($type === 'late') {
            $whereClauses[] = "a.status LIKE '%Late%'";
        }

        if (!$isAdmin) {
            $whereClauses[] = "a.user_id = ?";
            $whereClauses[] = "DATE(a.date) = ?";
            $params = [$userId, $today];
            $types = "is";
        } else {
            $whereClauses[] = "DATE(a.date) = ?";
            $params = [$today];
            $types = "s";
        }

        $whereSql = "WHERE " . implode(" AND ", $whereClauses);
        $sql = "SELECT DISTINCT a.*, u.faculty_id, u.first_name, u.last_name, u.role 
                FROM attendance_records a 
                JOIN users u ON a.user_id = u.id 
                $whereSql ORDER BY a.id DESC";

        $stmt = $db->query($sql, $params, $types);
        $records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($records as &$u) {
            if ($type === 'exits') {
                $time = ($u['pm_out'] > '00:00:00') ? $u['pm_out'] : (($u['am_out'] > '00:00:00') ? $u['am_out'] : $u['time_out']);
                $u['display_time'] = date('h:i A', strtotime($time));
            } elseif ($type === 'present') {
                $u['display_time'] = ($u['status'] === 'Unscheduled') ? '<span style="color:#6366f1;">Unscheduled Duty</span>' : '<span style="color:#059669;">On-Site</span>';
            } else {
                $time = ($u['am_in'] > '00:00:00') ? $u['am_in'] : (($u['pm_in'] > '00:00:00') ? $u['pm_in'] : $u['time_in']);
                $u['display_time'] = date('h:i A', strtotime($time));
            }
        }
        echo json_encode(['success' => true, 'users' => $records]);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
    exit;
}

    

public function submitFeedback() {
    $this->requireLogin();
    $userModel = $this->model('User');
    $db = Database::getInstance();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $this->verifyCsrfToken();
    }
        
    $userId = $_SESSION['user_id'];
    $userName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    $date = clean($_POST['record_date']);
    $message = clean($_POST['message']);
        
    $sql = "INSERT INTO attendance_feedbacks (user_id, target_date, message, status) VALUES (?, ?, ?, 'Pending')";
    $result = $db->query($sql, [$userId, $date, $message], "iss");

    if ($result) {
        $admins = $userModel->getAdmins();
        $subject = "ATTENDANCE DISCREPANCY REPORT: " . $userName;
            
        $emailBody = "
        <div style='font-family: sans-serif; max-width: 600px; margin: 20px auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;'>
            <div style='background-color: #ef4444; color: white; padding: 25px; text-align: center;'>
                <h2 style='margin: 0;'>New Attendance Feedback</h2>
            </div>
            <div style='padding: 30px; color: #1e293b;'>
                <p>A faculty member has reported a discrepancy in their logs:</p>
                <table style='width: 100%; margin-top: 20px;'>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Faculty:</td><td>$userName</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Target Date:</td><td>" . date('M d, Y', strtotime($date)) . "</td></tr>
                </table>
                <div style='background: #f8fafc; padding: 20px; border-radius: 8px; margin-top: 20px; border: 1px solid #f1f5f9;'>
                    <p style='margin: 0; font-style: italic;'>\"$message\"</p>
                </div>
            </div>
        </div>";

        foreach ($admins as $admin) {
            Mailer::send($admin['email'], $subject, $emailBody);
        }
    }
        
header('Content-Type: application/json');
echo json_encode(['success' => (bool)$result]);
exit;
}

public function exportHistoryExcel() {
    $this->requireLogin();
    if (ob_get_length()) ob_end_clean();

    $attModel = $this->model('Attendance');
    $userModel = $this->model('User');
    $isAdmin = ($_SESSION['role'] === 'Admin');

    $startDate = $_GET['start_date'] ?? '2026-02-01'; 
    $targetUserId = ($isAdmin) ? ($_GET['user_id'] ?? 'all') : $_SESSION['user_id'];

    $monthStart = date('Y-m-01', strtotime($startDate));
    $monthEnd = date('Y-m-t', strtotime($startDate));

    $lastDay = (int)date('t', strtotime($monthStart));

    $staffList = ($targetUserId === 'all') ? $userModel->getAllActive() : [$userModel->findById($targetUserId)];

    $filters = ['start_date' => $monthStart, 'end_date' => $monthEnd];
    if ($targetUserId !== 'all') $filters['user_id'] = $targetUserId;
    $rawRecords = $attModel->getRecords($filters);

    $processed = [];
    foreach ($rawRecords as $rec) {
        $uID = $rec['user_id'];
        $dayNum = (int)date('d', strtotime($rec['date']));

        $tIn = $rec['time_in'] ?? ($rec['logs'][0]['time_in'] ?? null);
        $tOut = $rec['time_out'] ?? ($rec['logs'][0]['time_out'] ?? null);

        if (($tIn && $tIn !== '00:00:00') || ($tOut && $tOut !== '00:00:00')) {
            $formattedIn = ($tIn && $tIn !== '00:00:00') ? date('g:i A', strtotime($tIn)) : '---';
            $formattedOut = ($tOut && $tOut !== '00:00:00') ? date('g:i A', strtotime($tOut)) : '---';
            $processed[$uID][$dayNum] = "$formattedIn - $formattedOut";
        }
    }

    $data = [];
    $titleText = (empty($rawRecords)) ? "DEBUG: NO DATA FETCHED FOR " . $monthStart : "ATTENDANCE HISTORY - " . strtoupper(date('F Y', strtotime($monthStart)));

    $data[] = ['<style bgcolor="#148038" color="#FFFFFF" align="center"><b>' . $titleText . '</b></style>'];
    $data[] = []; $data[] = [];
    
    $headerRow = ['<style bgcolor="#D3D3D3"><b>ID</b></style>', '<style bgcolor="#D3D3D3"><b>NAME</b></style>'];
    for ($d = 1; $d <= $lastDay; $d++) { $headerRow[] = '<style bgcolor="#D3D3D3"><b>' . date('M d', strtotime("$monthStart + " . ($d-1) . " days")) . '</b></style>'; }
    $data[] = $headerRow;

    foreach ($staffList as $staff) {
        if (!$staff) continue;
        $row = [$staff['faculty_id'], strtoupper($staff['last_name'] . ', ' . $staff['first_name'])];
        for ($d = 1; $d <= $lastDay; $d++) {
            $row[] = $processed[$staff['id']][$d] ?? '---';
        }
        $data[] = $row;
    }

    $xlsx = SimpleXLSXGen::fromArray($data);
    $lastCol = $this->getNameFromNumber($lastDay + 1);
    $xlsx->mergeCells('A1:' . $lastCol . '3')->setColWidth(1, 15)->setColWidth(2, 35);
    $xlsx->downloadAs("Attendance_Audit_" . date('Y_m', strtotime($monthStart)) . ".xlsx");
    exit();
}

private function getNameFromNumber($num) {
    $numeric = $num % 26;
    $letter = chr(65 + $numeric);
    $num2 = intval($num / 26);
    if ($num2 > 0) {
        return $this->getNameFromNumber($num2 - 1) . $letter;
    }
    return $letter;
}

public function printDtr() {
    $this->requireLogin();
    $userId = $_GET['user_id'] ?? $_SESSION['user_id'];

    if (!Helper::isAdmin() && $userId != $_SESSION['user_id']) { 
        die('Access Denied'); 
    }

    $userModel = $this->model('User');
    $attModel = $this->model('Attendance');
    $holidayModel = $this->model('Holiday');
    $scheduleModel = $this->model('Schedule');
    
    $baseDate = $_GET['start_date'] ?? date('Y-m-01');
    $fullMonthStart = date('Y-m-01', strtotime($baseDate));
    $fullMonthEnd   = date('Y-m-t', strtotime($baseDate));
    
    $monthName = date('F', strtotime($fullMonthStart));
    $year = date('Y', strtotime($fullMonthStart));
    $lastDay = (int)date('t', strtotime($fullMonthStart));

    $user = $userModel->findById($userId);
    $settings = $holidayModel->getSystemSettings();
    $holidays = $attModel->getHolidaysInRange($fullMonthStart, $fullMonthEnd);
    $logs = $attModel->getUserHistory($userId, ['start_date' => $fullMonthStart, 'end_date' => $fullMonthEnd]);
    $approvedSchedules = $scheduleModel->getByUser($userId, 'approved');

    $dtrRecords = [];
    for ($day = 1; $day <= $lastDay; $day++) {
        $currentDate = sprintf("%s-%02d-%02d", $year, date('m', strtotime($fullMonthStart)), $day);
        $dayOfWeek = date('l', strtotime($currentDate));
        
        $dayLogs = array_filter($logs, function($l) use ($currentDate) {
            return date('Y-m-d', strtotime($l['date'])) === $currentDate;
        });

        $dtrRecords[$day] = [
            'am_in' => '', 'am_out' => '', 'pm_in' => '', 'pm_out' => '',
            'credited_seconds' => 0,
            'remarks' => $holidays[$currentDate] ?? ''
        ];

        if (!empty($dayLogs)) {
            $firstLog = end($dayLogs);

            $ai = (!empty($firstLog['am_in']) && $firstLog['am_in'] != '00:00:00') ? $firstLog['am_in'] : '';
            $ao = (!empty($firstLog['am_out']) && $firstLog['am_out'] != '00:00:00') ? $firstLog['am_out'] : '';
            $pi = (!empty($firstLog['pm_in']) && $firstLog['pm_in'] != '00:00:00') ? $firstLog['pm_in'] : '';
            $po = (!empty($firstLog['pm_out']) && $firstLog['pm_out'] != '00:00:00') ? $firstLog['pm_out'] : '';

            if (empty($ai) && empty($ao) && empty($pi) && empty($po)) {
                $rawIn = (!empty($firstLog['time_in']) && $firstLog['time_in'] != '00:00:00') ? strtotime($firstLog['time_in']) : null;
                $rawOut = (!empty($firstLog['time_out']) && $firstLog['time_out'] != '00:00:00') ? strtotime($firstLog['time_out']) : null;

                if ($rawIn) {
                    if (date('H', $rawIn) < 12) $ai = date('H:i:s', $rawIn);
                    else $pi = date('H:i:s', $rawIn);
                }
                if ($rawOut) {
                    if (date('H', $rawOut) < 12) $ao = date('H:i:s', $rawOut);
                    else $po = date('H:i:s', $rawOut);
                }
            }

            foreach (['pi' => 'pm_in', 'po' => 'pm_out'] as $varName => $slot) {
                $val = $$varName;
                if (!empty($val)) {
                    $timestamp = strtotime($val);
                    $hour = (int)date('H', $timestamp);
                    if ($hour > 0 && $hour < 12) {
                        $dtrRecords[$day][$slot] = date('H:i:s', strtotime($val . " PM"));
                    } else {
                        $dtrRecords[$day][$slot] = date('H:i:s', $timestamp);
                    }
                }
            }

            $dtrRecords[$day]['am_in'] = !empty($ai) ? date('H:i:s', strtotime($ai)) : '';
            $dtrRecords[$day]['am_out'] = !empty($ao) ? date('H:i:s', strtotime($ao)) : '';

            $todaySchedules = array_filter($approvedSchedules, function($s) use ($dayOfWeek) {
                return $s['day_of_week'] === $dayOfWeek;
            });

            foreach ($todaySchedules as $sched) {
                $schedStart = strtotime($sched['start_time']);
                $schedEnd = strtotime($sched['end_time']);

                $calcIn = !empty($dtrRecords[$day]['am_in']) ? strtotime($dtrRecords[$day]['am_in']) : null;
                $calcOut = !empty($dtrRecords[$day]['pm_out']) ? strtotime($dtrRecords[$day]['pm_out']) : null;

                if ($calcIn && $calcOut) {
                    $overlapStart = max($schedStart, $calcIn);
                    $overlapEnd = min($schedEnd, $calcOut);

                    if ($overlapEnd > $overlapStart) {
                        $startClean = strtotime(date('H:i:00', $overlapStart));
                        $dtrRecords[$day]['credited_seconds'] += ($overlapEnd - $startClean);
                    }
                }
            }
        }
    }

    $data = [
        'user' => $user,
        'monthName' => $monthName,
        'year' => $year,
        'lastDay' => $lastDay,
        'dtrRecords' => $dtrRecords,
        'settings' => $settings,
        'schedules' => $approvedSchedules
    ];

    extract($data);
    require_once __DIR__ . '/../views/print_dtr_view.php';
    exit();
}
}
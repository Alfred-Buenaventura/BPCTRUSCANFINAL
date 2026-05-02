<?php
require_once __DIR__ . '/../core/database.php';

class Attendance {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getTodayRecord($userId) {
        $today = date('Y-m-d');
        // gets the attendance log for one person for today
        $sql = "SELECT * FROM attendance_records WHERE user_id = ? AND DATE(date) = ? LIMIT 1";
        $stmt = $this->db->query($sql, [$userId, $today], "is");
        return $stmt->get_result()->fetch_assoc();
    }

    public function getFullHistory() {
    $db = Database::getInstance();
    
    // place the sql query right here
    $sql = "SELECT 
                u.student_id, 
                u.name, 
                a.status, 
                a.method, 
                a.log_date, 
                a.log_time 
            FROM attendance_records a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.log_date DESC, a.log_time DESC";
            
    return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
}

    public function getUserHistory($userId = null, $filters = []) {
    // using r.* to grab all the morning and afternoon time columns
    $sql = "SELECT r.*, u.first_name, u.last_name, u.faculty_id, 
                   cs.subject as duty_subject, cs.room as duty_room, cs.type as duty_type
            FROM attendance_records r 
            JOIN users u ON r.user_id = u.id 
            LEFT JOIN class_schedules cs ON r.schedule_id = cs.id 
            WHERE 1=1"; 
    
    $params = [];
    $types = "";

    if (!empty($userId) && $userId !== 'all') {
        $sql .= " AND r.user_id = ?";
        $params[] = $userId;
        $types .= "i";
    }

    if (!empty($filters['status_type'])) {
        $sql .= " AND r.status = ?";
        $params[] = $filters['status_type'];
        $types .= "s";
    }
    
    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
        $sql .= " AND r.date BETWEEN ? AND ?";
        $params[] = $filters['start_date'];
        $params[] = $filters['end_date'];
        $types .= "ss";
    }

    // updated: sorting by am_in now instead of time_in
    $sql .= " ORDER BY r.date DESC, r.am_in ASC";

    $stmt = $this->db->query($sql, $params, $types);
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

    public function getRecords($filters) {
        // big query for the admin dashboard to see everyone's logs grouped by date
        $sql = "SELECT ar.*, 
                       u.faculty_id, u.first_name, u.last_name, u.role,
                       s.start_time as sched_start, s.end_time as sched_end,
                       s.subject
                FROM attendance_records ar
                JOIN users u ON ar.user_id = u.id
                LEFT JOIN class_schedules s ON ar.schedule_id = s.id
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $sql .= " AND ar.date BETWEEN ? AND ?";
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
            $types .= "ss";
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND ar.user_id = ?";
            $params[] = $filters['user_id'];
            $types .= "i";
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.faculty_id LIKE ?)";
            $search = "%" . $filters['search'] . "%";
            $params[] = $search; $params[] = $search; $params[] = $search;
            $types .= "sss";
        }

        $sql .= " ORDER BY ar.date DESC, ar.time_in ASC";
        
        $res = $this->db->query($sql, $params, $types)->get_result();
        
        $records = [];
        while ($row = $res->fetch_assoc()) {
            $key = $row['date'] . '_' . $row['user_id'];
            
            if (!isset($records[$key])) {
                $records[$key] = [
                    'user_id'    => $row['user_id'],
                    'name'       => $row['first_name'] . ' ' . $row['last_name'],
                    'faculty_id' => $row['faculty_id'],
                    'date'       => $row['date'],
                    'status'     => $row['status'],
                    'logs'       => []
                ];
            }
            
            // --- critical fix: checking all 6 columns to see if there's actual data ---
            $hasData = false;
            $timeCols = ['am_in', 'am_out', 'pm_in', 'pm_out', 'time_in', 'time_out'];
            foreach ($timeCols as $col) {
                if (!empty($row[$col]) && $row[$col] != '00:00:00') {
                    $hasData = true;
                    break;
                }
            }

            if ($hasData) {
                $records[$key]['logs'][] = [
                    'subject'     => $row['subject'] ?? 'General Duty',
                    // passing all columns to the view so the accordion can show them
                    'am_in'       => $row['am_in'],
                    'am_out'      => $row['am_out'],
                    'pm_in'       => $row['pm_in'],
                    'pm_out'      => $row['pm_out'],
                    'time_in'     => $row['time_in'],
                    'time_out'    => $row['time_out'],
                    'sched_start' => $row['sched_start'], 
                    'sched_end'   => $row['sched_end']    
                ];
            }
        }
        
        return array_values($records);
    }

    public function getStats($userId = null) {
    $today = date('Y-m-d');
    $stats = ['entries' => 0, 'exits' => 0, 'present_total' => 0, 'late' => 0];
    $filter = $userId ? " AND user_id = " . intval($userId) : "";

    // helper strings to check the columns
    $hasIn = " ( (am_in > '00:00:00') OR (pm_in > '00:00:00') OR (time_in > '00:00:00') ) ";
    $hasOut = " ( (am_out > '00:00:00') OR (pm_out > '00:00:00') OR (time_out > '00:00:00') ) ";

    // 1. entries: anyone who arrived at least once today
    $sqlEntries = "SELECT COUNT(DISTINCT user_id) as c FROM attendance_records 
                   WHERE DATE(date) = ? AND $hasIn $filter";
    
    // 2. exits: anyone who left at least once today
    $sqlExits = "SELECT COUNT(DISTINCT user_id) as c FROM attendance_records 
                 WHERE DATE(date) = ? AND $hasOut $filter";

    // 3. people on-site (the logic fix):
    // comparing the latest arrival vs latest departure time. if arrival is later, they are inside.
    $sqlPresent = "SELECT COUNT(DISTINCT user_id) as c FROM attendance_records 
                   WHERE DATE(date) = ? 
                   AND GREATEST(COALESCE(am_in, 0), COALESCE(pm_in, 0), COALESCE(time_in, 0)) > 
                       GREATEST(COALESCE(am_out, 0), COALESCE(pm_out, 0), COALESCE(time_out, 0)) 
                   $filter";

    // 4. late logs
    $sqlLate = "SELECT COUNT(*) as c FROM attendance_records 
                WHERE DATE(date) = ? AND status LIKE '%Late%' $filter";

    // running this with prepared statements to keep the date format consistent
    $stats['entries'] = $this->db->query($sqlEntries, [$today], "s")->get_result()->fetch_assoc()['c'] ?? 0;
    $stats['exits'] = $this->db->query($sqlExits, [$today], "s")->get_result()->fetch_assoc()['c'] ?? 0;
    $stats['present_total'] = $this->db->query($sqlPresent, [$today], "s")->get_result()->fetch_assoc()['c'] ?? 0;
    $stats['late'] = $this->db->query($sqlLate, [$today], "s")->get_result()->fetch_assoc()['c'] ?? 0;

    return $stats;
}

    public function countActiveToday() {
        // counting how many unique users logged in today
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(DISTINCT user_id) as c FROM attendance_records WHERE DATE(date) = ? AND time_in IS NOT NULL AND time_in != ''";
        $res = $this->db->query($sql, [$today], "s")->get_result();
        return $res->fetch_assoc()['c'] ?? 0;
    }

    public function getDetailedStatsByType($type) {
        // getting specific user lists for those dashboard popups
        $today = date('Y-m-d');
        $hasIn  = " (ar.time_in IS NOT NULL AND ar.time_in != '' AND ar.time_in != '00:00:00') ";
        $hasOut = " (ar.time_out IS NOT NULL AND ar.time_out != '' AND ar.time_out != '00:00:00') ";
        $noOut  = " (ar.time_out IS NULL OR ar.time_out = '' OR ar.time_out = '00:00:00') ";

        $sql = "SELECT u.faculty_id, u.first_name, u.last_name, u.role, ar.time_in, ar.time_out 
                FROM attendance_records ar
                JOIN users u ON ar.user_id = u.id
                WHERE DATE(ar.date) = ?";
        
        switch ($type) {
            case 'entries': $sql .= " AND $hasIn"; break;
            case 'exits': $sql .= " AND $hasOut"; break;
            case 'present': $sql .= " AND $hasIn AND $noOut"; break;
            case 'late': $sql .= " AND ar.status LIKE '%Late%'"; break;
        }
        
        $sql .= " GROUP BY u.id";
        return $this->db->query($sql, [$today], "s")->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getHolidaysInRange($startDate, $endDate) {
        // helper to find holidays in a date range for reports
        $holidays = [];
        $sql = "SELECT holiday_date, description FROM holidays WHERE holiday_date BETWEEN ? AND ?";
        $res = $this->db->query($sql, [$startDate, $endDate], "ss")->get_result();
        while($row = $res->fetch_assoc()){
            $holidays[$row['holiday_date']] = $row['description'];
        }
        return $holidays;
    }

    public function getDailySummary($userId) {
        $today = date('Y-m-d');
        $dayOfWeek = date('l'); 
        
        // this query looks for the very first arrival and very last departure in the am/pm slots
        $sqlSummary = "SELECT 
                LEAST(
                    COALESCE(NULLIF(am_in, '00:00:00'), '23:59:59'),
                    COALESCE(NULLIF(pm_in, '00:00:00'), '23:59:59'),
                    COALESCE(NULLIF(time_in, '00:00:00'), '23:59:59')
                ) as first_in, 
                GREATEST(
                    COALESCE(NULLIF(am_out, '00:00:00'), '00:00:00'),
                    COALESCE(NULLIF(pm_out, '00:00:00'), '00:00:00'),
                    COALESCE(NULLIF(time_out, '00:00:00'), '00:00:00')
                ) as last_out
            FROM attendance_records 
            WHERE user_id = ? AND date = ?";
        
        $stmtSummary = $this->db->query($sqlSummary, [$userId, $today], "is");
        $summary = $stmtSummary->get_result()->fetch_assoc();

        // fix: checking if summary exists before trying to use it
        $fIn = ($summary && isset($summary['first_in']) && $summary['first_in'] !== '23:59:59') ? $summary['first_in'] : null;
        $lOut = ($summary && isset($summary['last_out']) && $summary['last_out'] !== '00:00:00') ? $summary['last_out'] : null;

        // grabbing the status from the first record of the day
        $statusRes = $this->db->query("SELECT status FROM attendance_records WHERE user_id = ? AND date = ? ORDER BY id ASC LIMIT 1", [$userId, $today], "is");
        $statusRow = $statusRes->get_result()->fetch_assoc();
        $currentStatus = $statusRow['status'] ?? null;

        // if no record exists, checking if they were actually supposed to be on duty
        if (empty($fIn)) {
            $schedRes = $this->db->query("SELECT COUNT(*) as c FROM class_schedules WHERE user_id = ? AND day_of_week = ? AND status = 'approved'", [$userId, $dayOfWeek], "is");
            $schedCount = $schedRes->get_result()->fetch_assoc()['c'] ?? 0;
            $currentStatus = ($schedCount > 0) ? 'Absent' : 'Not Present';
        }
        
        return [
            'time_in'  => $fIn,
            'time_out' => $lOut,
            'status'   => $currentStatus
        ];
    }

// unified logic for logging attendance (time in/out). this merges what used to be in the record_attendance api
public function logAttendance($userId, $method = 'Biometric') {
    $today = date('Y-m-d');
    $now = date('H:i:s');
    $hour = (int)date('H');
    $dayOfWeek = date('l');

    // 1. Fetch Schedule
    $schedQuery = "SELECT id, start_time FROM class_schedules WHERE user_id = ? AND day_of_week = ? AND status = 'approved' AND (ABS(TIMESTAMPDIFF(MINUTE, start_time, ?)) <= 60 OR (start_time <= ? AND end_time >= ?)) LIMIT 1";
    $schedStmt = $this->db->query($schedQuery, [$userId, $dayOfWeek, $now, $now, $now], "issss");
    $currentSched = $schedStmt->get_result()->fetch_assoc();
    $scheduleId = $currentSched ? $currentSched['id'] : null;

    // 2. Fetch existing record for today
    $lastRecordStmt = $this->db->query("SELECT * FROM attendance_records WHERE user_id = ? AND date = ? ORDER BY id DESC LIMIT 1", [$userId, $today], "is");
    $lastRecord = $lastRecordStmt->get_result()->fetch_assoc();

    if ($lastRecord) {
        // Determine which specific slot to update (AM vs PM)
        $updateCol = '';
        if ($hour < 12) {
            if (empty($lastRecord['am_in']) || $lastRecord['am_in'] == '00:00:00') $updateCol = 'am_in';
            elseif (empty($lastRecord['am_out']) || $lastRecord['am_out'] == '00:00:00') $updateCol = 'am_out';
            else $updateCol = 'pm_in';
        } else {
            if (empty($lastRecord['pm_in']) || $lastRecord['pm_in'] == '00:00:00') $updateCol = 'pm_in';
            else $updateCol = 'pm_out';
        }

        // Prevention of double-scans (1-minute buffer)
        $compareCol = ($updateCol == 'am_out') ? 'am_in' : (($updateCol == 'pm_out') ? 'pm_in' : $updateCol);
        $existingTime = !empty($lastRecord[$compareCol]) ? strtotime($lastRecord[$compareCol]) : 0;
        if ($existingTime > 0 && (abs(time() - $existingTime) < 60)) {
            return ['success' => false, 'message' => 'Duplicate scan ignored.'];
        }

        $this->db->query("UPDATE attendance_records SET $updateCol = ?, method = ? WHERE id = ?", [$now, $method, $lastRecord['id']], "ssi");
        return ['success' => true, 'message' => str_replace('_', ' ', strtoupper($updateCol)) . " Recorded", 'type' => 'Out'];
    } else {
        // Create new record for the day
        $finalStatus = $scheduleId ? 'Present' : 'Unscheduled';
        if ($scheduleId && (time() - strtotime($currentSched['start_time'])) / 60 > 15) $finalStatus = 'Late';

        $initialCol = ($hour < 12) ? 'am_in' : 'pm_in';
        $this->db->query("INSERT INTO attendance_records (user_id, date, $initialCol, schedule_id, status, method) VALUES (?, ?, ?, ?, ?, ?)", 
                         [$userId, $today, $now, $scheduleId, $finalStatus, $method], "ississ");
        return ['success' => true, 'message' => str_replace('_', ' ', strtoupper($initialCol)) . " Recorded", 'type' => 'In'];
    }
}
}
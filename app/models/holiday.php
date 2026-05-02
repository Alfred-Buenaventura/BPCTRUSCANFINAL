<?php
require_once __DIR__ . '/../core/database.php';

class Holiday {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance(); 
    }

    public function getAll($filters = []) {
        // pulls all registered holidays with options to search and filter by date
        $sql = "SELECT * FROM holidays WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($filters['search'])) {
            $sql .= " AND description LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
            $types .= "s";
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $sql .= " AND holiday_date BETWEEN ? AND ?";
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
            $types .= "ss";
        }

        $sql .= " ORDER BY holiday_date ASC";
        
        return $this->db->query($sql, $params, $types)->get_result()->fetch_all(MYSQLI_ASSOC); 
    }

    public function create($date, $desc, $type) {
        // inserts a new institution-wide holiday entry
        $sql = "INSERT INTO holidays (holiday_date, description, type) VALUES (?, ?, ?)";
        return $this->db->query($sql, [$date, $desc, $type], "sss"); 
    }

    public function delete($id) {
        $sql = "DELETE FROM holidays WHERE id = ?";
        return $this->db->query($sql, [$id], "i"); 
    }

    public function getInRange($startDate, $endDate) {
        // quickly fetches holidays for attendance calculations over a specific period
        $sql = "SELECT holiday_date, description FROM holidays WHERE holiday_date BETWEEN ? AND ?";
        $res = $this->db->query($sql, [$startDate, $endDate], "ss")->get_result();
        $holidays = [];
        while($row = $res->fetch_assoc()){
            $holidays[$row['holiday_date']] = $row['description'];
        }
        return $holidays; 
    }

    public function getSystemSettings() {
    $stmt = $this->db->query("SELECT * FROM system_settings", [], "");
    
    if (!$stmt) return [];
    
    $result = $stmt->get_result();
    $settings = [];
    
    while($row = $result->fetch_assoc()) {
        $settings[trim($row['setting_key'])] = $row['setting_value'];
    }
    return $settings;
}

    public function updateSystemSetting($key, $value) {
        $sql = "INSERT INTO system_settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?";
        return $this->db->query($sql, [$key, $value, $value], "sss");
    }

    public function getUpcoming($limit = 3) {
    // Fetches holidays that haven't passed yet, ordered by date
    $sql = "SELECT * FROM holidays WHERE holiday_date >= CURDATE() ORDER BY holiday_date ASC LIMIT ?";
    $stmt = $this->db->query($sql, [$limit], "i");
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
}
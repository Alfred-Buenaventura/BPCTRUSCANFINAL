<?php
require_once __DIR__ . '/../core/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getDetailedReport($filter = 'all') {
        // provides a complete user list filtered by fingerprint status for reports
        $sql = "SELECT first_name, last_name, faculty_id, role, email, status, fingerprint_registered 
                FROM users WHERE status = 'active'";
        
        if ($filter === 'registered') {
            $sql .= " AND fingerprint_registered = 1";
        } elseif ($filter === 'pending') {
            $sql .= " AND fingerprint_registered = 0";
        }

        $sql .= " ORDER BY role ASC, last_name ASC";
        
        return $this->db->query($sql)->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function findUserByUsername($username) {
        // locates a user via their username or unique faculty id for login
        $sql = "SELECT * FROM users WHERE (username = ? OR faculty_id = ?) AND status = 'active'";
        $stmt = $this->db->query($sql, [$username, $username], "ss");
        return $stmt->get_result()->fetch_assoc();
    }

    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ? AND status = 'active'";
        $stmt = $this->db->query($sql, [$id], "i");
        return $stmt->get_result()->fetch_assoc();
    }

    public function getRegisteredFingers($userId) {
        $sql = "SELECT id, finger_name, created_at FROM user_fingerprints WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->query($sql, [$userId], "i");
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addFingerprint($userId, $fingerprintData, $fingerName) {
        // saves biometric data and marks the user's account as fully registered
        $check = $this->db->query("SELECT id FROM user_fingerprints WHERE user_id = ? AND finger_name = ?", [$userId, $fingerName], "is");
        
        if ($check->get_result()->num_rows > 0) {
            $sql = "UPDATE user_fingerprints SET fingerprint_data = ?, created_at = NOW() WHERE user_id = ? AND finger_name = ?";
            $this->db->query($sql, [$fingerprintData, $userId, $fingerName], "sis");
        } else {
            $sql = "INSERT INTO user_fingerprints (user_id, finger_name, fingerprint_data) VALUES (?, ?, ?)";
            $this->db->query($sql, [$userId, $fingerName, $fingerprintData], "iss");
        }

        $this->db->query("UPDATE users SET fingerprint_registered=1, fingerprint_registered_at=NOW() WHERE id=?", [$userId], "i");
        return true;
    }

    public function updateProfile($id, $firstName, $lastName, $middleName, $email, $phone, $emailNotif, $weeklySum) {
        // updates core profile fields and personal notification preferences
        $sql = "UPDATE users SET 
                first_name=?, 
                last_name=?, 
                middle_name=?, 
                email=?, 
                phone=?, 
                email_notifications_enabled=?, 
                weekly_summary_enabled=? 
                WHERE id=?";
        
        return $this->db->query($sql, [
            $firstName, $lastName, $middleName, $email, $phone, 
            $emailNotif, $weeklySum, $id
        ], "sssssiii");
    }
    
    public function updateProfileImage($id, $imagePath) {
        $sql = "UPDATE users SET profile_image = ? WHERE id = ?";
        return $this->db->query($sql, [$imagePath, $id], "si");
    }

    public function update($id, $fname, $lname, $mname, $email, $phone) {
    $sql = "UPDATE users SET first_name=?, last_name=?, middle_name=?, email=?, phone=? WHERE id=?";
    return $this->db->query($sql, [$fname, $lname, $mname, $email, $phone, $id], "sssssi");
}

    public function exists($facultyId) {
        $sql = "SELECT id FROM users WHERE faculty_id = ?";
        $res = $this->db->query($sql, [$facultyId], "s");
        return $res->get_result()->num_rows > 0;
    }

    public function create($data) {
    $qrToken = $data['qr_token']; 

    $sql = "INSERT INTO users (faculty_id, username, password, first_name, last_name, middle_name, email, phone, role, force_password_change, qr_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";
    
    $this->db->query($sql, [
        $data['faculty_id'],
        $data['username'],
        $data['password'],
        $data['first_name'],
        $data['last_name'],
        $data['middle_name'],
        $data['email'],
        $data['phone'],
        $data['role'],
        $qrToken
    ], "ssssssssss");
    
    return $this->db->conn->insert_id;
}

    public function findByQrToken($token) {
        $sql = "SELECT * FROM users WHERE qr_token = ? AND status = 'active'";
        $stmt = $this->db->query($sql, [$token], "s");
        return $stmt->get_result()->fetch_assoc();
    }

    public function findUserById($id) {
        $sql = "SELECT id, faculty_id, first_name, last_name, email, role FROM users WHERE id = ?";
        $res = $this->db->query($sql, [$id], "i");
        return $res->get_result()->fetch_assoc();
    }

    public function getAllActive() {
    $sql = "SELECT id, faculty_id, first_name, last_name, middle_name, email, phone, role FROM users WHERE status = 'active' ORDER BY last_name ASC";
    $stmt = $this->db->query($sql);
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

    public function getAllArchived() {
    $sql = "SELECT id, faculty_id, first_name, last_name, middle_name, email, phone, role FROM users WHERE status = 'archived' ORDER BY last_name ASC";
    
    $stmt = $this->db->query($sql);
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

    public function getAllStaff() {
    return $this->db->query("SELECT id, faculty_id, first_name, last_name 
                             FROM users 
                             WHERE status='active' 
                             AND role NOT LIKE '%Admin%' 
                             ORDER BY first_name")->get_result()->fetch_all(MYSQLI_ASSOC);
}

    public function getPendingUsers() {
    return $this->db->query("SELECT * FROM users 
                             WHERE status='active' 
                             AND fingerprint_registered=0 
                             AND role NOT LIKE '%Admin%' 
                             ORDER BY created_at DESC")->get_result()->fetch_all(MYSQLI_ASSOC);
}

    public function getRegisteredUsers() {
    return $this->db->query("SELECT * FROM users 
                             WHERE status='active' 
                             AND fingerprint_registered=1 
                             AND role NOT LIKE '%Admin%' 
                             ORDER BY first_name ASC")->get_result()->fetch_all(MYSQLI_ASSOC);
}

    public function getStats() {
        // returns a breakdown of active admins vs non-admin accounts
        $sql = "SELECT
            COUNT(*) as total_active,
            SUM(CASE WHEN role != 'Admin' THEN 1 ELSE 0 END) as non_admin_active,
            SUM(CASE WHEN role = 'Admin' THEN 1 ELSE 0 END) as admin_active
        FROM users WHERE status = 'active'";
        return $this->db->conn->query($sql)->fetch_assoc();
    }

    public function updateStatus($userId, $status) {
    $sql = "UPDATE users SET status = ? WHERE id = ?";
    return $this->db->query($sql, [$status, $userId], "si");
}

public function delete($userId) {
    $this->db->query("DELETE FROM class_schedules WHERE user_id=?", [$userId], "i");
    $this->db->query("DELETE FROM attendance_records WHERE user_id=?", [$userId], "i");
    $this->db->query("DELETE FROM notifications WHERE user_id=?", [$userId], "i");
    $this->db->query("DELETE FROM activity_logs WHERE user_id=?", [$userId], "i");
    $this->db->query("DELETE FROM user_fingerprints WHERE user_id=?", [$userId], "i");

    $sql = "DELETE FROM users WHERE id = ?";
    return $this->db->query($sql, [$userId], "i");
}

    public function countActive() {
        $res = $this->db->conn->query("SELECT COUNT(*) as c FROM users WHERE status='active'");
        return $res->fetch_assoc()['c'] ?? 0;
    }

    public function countActiveStaff() {
    // Counts all active personnel who are not administrators
    $sql = "SELECT COUNT(*) as c FROM users WHERE status = 'active' AND role NOT LIKE '%Admin%'";
    $res = $this->db->conn->query($sql);
    return $res->fetch_assoc()['c'] ?? 0;
}

    public function countPendingFingerprint() {
    // Counts only staff members who still need to scan
    $res = $this->db->conn->query("SELECT COUNT(*) as c FROM users 
                                   WHERE status='active' 
                                   AND fingerprint_registered=0 
                                   AND role NOT LIKE '%Admin%'");
    return $res->fetch_assoc()['c'] ?? 0;
}

    public function getFingerprintStatus($userId) {
        $stmt = $this->db->query("SELECT fingerprint_registered FROM users WHERE id = ?", [$userId], "i");
        $row = $stmt->get_result()->fetch_assoc();
        return $row['fingerprint_registered'] ?? 0;
    }

public function updateQrToken($userId, $token) {
    $sql = "UPDATE users SET qr_token = ? WHERE id = ?";
    return $this->db->query($sql, [$token, $userId], "si");
}

public function countMissingSchedules() {
    $sql = "SELECT COUNT(DISTINCT u.id) as c 
            FROM users u 
            LEFT JOIN class_schedules s ON u.id = s.user_id 
            WHERE u.status = 'active' 
            AND u.role NOT LIKE '%Admin%' 
            AND s.id IS NULL";
    $res = $this->db->conn->query($sql);
    return $res->fetch_assoc()['c'] ?? 0;
}

}
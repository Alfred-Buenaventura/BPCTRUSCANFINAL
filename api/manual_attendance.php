<?php
require_once '../app/init.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $db = Database::getInstance();
    
    // checks if user exists
    $userQuery = $db->query("SELECT id FROM users WHERE id = ?", [$data['user_id']], "i");
    if (!$userQuery->get_result()->fetch_assoc()) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    // inserts the record with 'Manual Entry' status
    $sql = "INSERT INTO attendance_records (user_id, date, time_in, time_out, status) VALUES (?, ?, ?, ?, 'Manual Entry')";
    $success = $db->query($sql, [
        $data['user_id'], 
        $data['date'], 
        $data['time_in'], 
        $data['time_out']
    ], "isss");

    echo json_encode(['success' => (bool)$success]);
}
?>
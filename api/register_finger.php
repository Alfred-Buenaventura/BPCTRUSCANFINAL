<?php
// headers for the AJAX request
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json");

require_once __DIR__ . '/../app/init.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id']) || !isset($data['template'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing user_id or template"]);
    exit;
}

$userModel = new User();
$userId = $data['user_id'];
$template = $data['template'];
$position = isset($data['position']) ? $data['position'] : 'Unknown Finger';

try {
    // attempt to save to the database
    if ($userModel->addFingerprint($userId, $template, $position)) {
        echo json_encode(["status" => "success", "message" => "$position enrolled successfully"]);
    } else {
        throw new Exception("Model failed to save fingerprint data.");
    }
} catch (Exception $e) {
    // error handling
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
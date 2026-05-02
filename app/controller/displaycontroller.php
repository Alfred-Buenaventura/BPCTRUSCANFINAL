<?php
require_once __DIR__ . '/../core/controller.php';

class DisplayController extends Controller {

    public function index() {
    $accessKey = $_GET['key'] ?? '';
    
    // Authorization check using the secret kiosk key
    if (empty(KIOSK_SECRET_KEY) || !hash_equals(KIOSK_SECRET_KEY, $accessKey)) {
        http_response_code(403);
        die("Unauthorized Access.");
    }

    // Initialize the model to fetch settings from the database
    $holidayModel = $this->model('Holiday');
    $settings = $holidayModel->getSystemSettings();
    
    // Retrieve the dynamic PIN, defaulting to '1234' if not set in DB
    $dbPin = $settings['qr_interface_pin'] ?? null;

    $data = [
        'terminal_pin' => $dbPin
    ];

    $this->view('display_view', $data);
}
    
    public function markRead() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) { 
            echo json_encode(['success' => false, 'message' => 'Unauthorized']); 
            exit; 
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $notifId = $data['notification_id'] ?? null;

        if ($notifId) {
            $db = Database::getInstance();
            $db->query(
                "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", 
                [$notifId, $_SESSION['user_id']], 
                "ii"
            );
        }
        
        echo json_encode(['success' => true]);
        exit;
    }
}
?>
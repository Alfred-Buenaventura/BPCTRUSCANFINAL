<?php
require_once __DIR__ . '/../core/controller.php';

class ProfileController extends Controller {

    public function index() {
        $this->requireLogin();
        
        // Initialize Models
        $userModel = $this->model('User');
        $logModel = $this->model('ActivityLog');
        $attendanceModel = $this->model('Attendance');
        $holidayModel = $this->model('Holiday'); // Used for System Settings/PIN
        
        $userId = $_SESSION['user_id'];
        $user = $userModel->findById($userId);
        $isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin');

        // 1. HANDLE TERMINAL PIN UPDATE (Admin Only)
        if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_terminal_pin'])) {
            $this->verifyCsrfToken();
            $newPin = trim($_POST['new_terminal_pin']);
            
            // Validation: Ensure it is exactly 4 digits
            if (preg_match('/^[0-9]{4}$/', $newPin)) {
                $holidayModel->updateSystemSetting('qr_interface_pin', $newPin);
                $this->setFlash('Terminal PIN updated successfully!', 'success', 'profile.php');
            } else {
                $this->setFlash('Invalid PIN. Please enter exactly 4 digits.', 'error', 'profile.php');
            }
        }

        // 2. HANDLE SECURE PHOTO UPLOAD (AJAX Action)
        if (isset($_GET['action']) && $_GET['action'] === 'upload_photo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            if (!isset($_FILES['croppedImage'])) { 
                echo json_encode(['success' => false, 'message' => 'No image received.']); 
                exit; 
            }

            $file = $_FILES['croppedImage'];
            
            // Validation: Size (10MB max)
            if ($file['size'] > 10485760) { 
                echo json_encode(['success' => false, 'message' => 'File exceeds 10MB limit.']); 
                exit; 
            }

            // Validation: Mime Type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            $allowedMimes = ['image/png', 'image/jpeg', 'image/jpg'];

            if (!in_array($mimeType, $allowedMimes) || !getimagesize($file['tmp_name'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid file format. Only JPG and PNG are allowed.']);
                exit;
            }

            $uploadDir = __DIR__ . '/../../public/uploads/profile_pics/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $extension = ($mimeType === 'image/png') ? '.png' : '.jpg';
            $fileName = $userId . '_' . time() . $extension;
            
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                $dbPath = 'public/uploads/profile_pics/' . $fileName;
                $userModel->updateProfileImage($userId, $dbPath);
                $_SESSION['profile_image'] = $dbPath; 
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Upload failed on server.']);
            }
            exit;
        }

        // 3. HANDLE PROFILE UPDATE (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
            $this->verifyCsrfToken();
            
            $firstName = trim($_POST['first_name']);
            $lastName = trim($_POST['last_name']);
            $middleName = trim($_POST['middle_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $emailNotif = isset($_POST['email_notifications']) ? 1 : 0;
            $weeklySum = isset($_POST['weekly_summary']) ? 1 : 0;

            if ($userModel->updateProfile($userId, $firstName, $lastName, $middleName, $email, $phone, $emailNotif, $weeklySum)) {
                $_SESSION['full_name'] = $firstName . ' ' . $lastName;
                $_SESSION['first_name'] = $firstName;
                $this->setFlash('Profile updated successfully!', 'success', 'profile.php');
            } else {
                $this->setFlash('Failed to update profile settings.', 'error', 'profile.php');
            }
        }

        // 4. FETCH FINAL DATA FOR THE VIEW
        $settings = $holidayModel->getSystemSettings();
        $updatedUser = $userModel->findById($userId);
        $_SESSION['profile_image'] = $updatedUser['profile_image'];

        $data = [
            'pageTitle'    => 'My Profile', 
            'pageSubtitle' => 'View and edit your information', 
            'user'         => $updatedUser,
            'isAdmin'      => $isAdmin, // Fixes "Undefined variable $isAdmin"
            'terminal_pin' => $settings['qr_interface_pin'] ?? '1234', // For PIN Modal
            'qr_token'     => $updatedUser['qr_token'] ?? null,
            'activities'   => $logModel->getRecentLogs(10, $userId),
            'stats'        => $attendanceModel->getStats($userId),
            'error'        => '', 
            'success'      => ''
        ];

        $this->view('profile_view', $data);
    }
}
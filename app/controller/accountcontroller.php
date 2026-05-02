<?php
require_once __DIR__ . '/../core/controller.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;


class AccountController extends Controller {
  
    private $userModel;

    public function __construct() {
        $this->userModel = new User(); 
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullPhone = $this->processPhoneInput($_POST['country_code'] ?? '+63', $_POST['phone'] ?? '');
            $cleanDigits = preg_replace('/\D/', '', $_POST['phone'] ?? '');
            if (strlen($cleanDigits) !== 10) {
                $_SESSION['error'] = "Please enter a valid 10-digit mobile number.";
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit();
            }

            $data = [
                'faculty_id' => trim($_POST['faculty_id']),
                'username'   => trim($_POST['username']),
                'password'   => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'first_name' => trim($_POST['first_name']),
                'last_name'  => trim($_POST['last_name']),
                'middle_name'=> trim($_POST['middle_name'] ?? ''),
                'email'      => trim($_POST['email']),
                'phone'      => $fullPhone, 
                'role'       => 'staff'
            ];

            if ($this->userModel->create($data)) {
                $_SESSION['success'] = "Registration successful!";
                header("Location: login.php");
            } else {
                $_SESSION['error'] = "Registration failed.";
                header("Location: " . $_SERVER['HTTP_REFERER']);
            }
            exit();
        }
    }

    public function index() {
        $this->requireLogin();
        if ($_SESSION['role'] !== 'Admin') {
            header('Location: index.php');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_csv'])) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === 0) {
            $this->handleCsvImport($_FILES['csv_file'], $this->model('User'), $this->model('ActivityLog'), $this->model('Notification'));
            exit;
        } else {
            $this->setFlash("Please select a valid CSV file.", 'error');
        }
    }

        if (isset($_GET['action']) && $_GET['action'] === 'download_template') {

        if (ob_get_level()) ob_end_clean(); 

        $this->downloadTemplate();
        exit();
    }
    
        if (isset($_GET['action']) && $_GET['action'] === 'regenerate_qr') {
        $this->regenerateQr();
        exit;
        }
        
        $logModel = $this->model('ActivityLog');
        $notifModel = $this->model('Notification');
        
        $data = [
            'pageTitle' => 'Account Management',
            'pageSubtitle' => 'Manage user accounts individually or import in bulk via CSV',
            'activeTab' => $_GET['tab'] ?? 'csv',
            'flashMessage' => $_SESSION['flash_message'] ?? null,
            'flashType' => $_SESSION['flash_type'] ?? null
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();

            // this handles the bulk csv import feature of the system
            if (isset($_FILES['csvFile'])) {
                $this->handleCsvImport($_FILES['csvFile'], $this->userModel, $logModel, $notifModel);
                exit();
            }

            // for the individual account creation feature
            if (isset($_POST['create_user'])) {
                $this->handleCreateUser($_POST, $this->userModel, $logModel, $notifModel);
                exit();
            }

            // user editing feature
            if (isset($_POST['edit_user'])) {
                $formattedPhone = $this->processPhoneInput($_POST['country_code'] ?? '+63', $_POST['phone'] ?? '');

                $this->userModel->update(
                    $_POST['user_id'], 
                    clean($_POST['first_name']), 
                    clean($_POST['last_name']), 
                    clean($_POST['middle_name']), 
                    clean($_POST['email']), 
                    $formattedPhone
                );

                $logModel->log($_SESSION['user_id'], 'User Updated', "Updated user ID: " . $_POST['user_id']);
                $this->setFlash('User information updated successfully!', 'success', 'create_account.php?tab=view');
                exit();
            }

            // user archiving, delete, and restore
            if (isset($_POST['archive_user'])) {
                $this->userModel->updateStatus($_POST['user_id'], 'archived');
                $logModel->log($_SESSION['user_id'], 'User Archived', "Archived user ID: " . $_POST['user_id']);
                $this->setFlash('User archived successfully!', 'success', 'create_account.php?tab=view');
                exit();
            }
            if (isset($_POST['restore_user'])) {
                $this->userModel->updateStatus($_POST['user_id'], 'active');
                $logModel->log($_SESSION['user_id'], 'User Restored', "Restored user ID: " . $_POST['user_id']);
                $this->setFlash('User restored successfully!', 'success', 'create_account.php?tab=view');
                exit();
            }
            if (isset($_POST['delete_user'])) {
                $this->userModel->delete($_POST['user_id']);
                $logModel->log($_SESSION['user_id'], 'User Deleted', "Permanently deleted user ID: " . $_POST['user_id']);
                $this->setFlash('User permanently deleted!', 'success', 'create_account.php?tab=view');
                exit();
            }
        }

        $data['stats'] = $this->userModel->getStats();
        $data['activeUsers'] = $this->userModel->getAllActive();
        $data['archivedUsers'] = $this->userModel->getAllArchived();

        $this->view('account_view', $data);
    }

    private function handleCreateUser($post, $userModel, $logModel, $notifModel) {
    try {
        $facultyId = clean($post['faculty_id']);
        if ($userModel->exists($facultyId)) {
            $this->setFlash("Account with Faculty ID ($facultyId) already exists.", 'error', 'create_account.php?tab=create');
            return;
        }

        $token = bin2hex(random_bytes(16)); 

        $userData = [
            'faculty_id' => $facultyId,
            'username'   => strtolower($facultyId),
            'password'   => password_hash('@defaultpass123', PASSWORD_DEFAULT),
            'qr_token'   => $token,
            'first_name' => clean($post['first_name']),
            'last_name'  => clean($post['last_name']),
            'middle_name'=> clean($post['middle_name']),
            'email'      => clean($post['email']),
            'phone'      => $this->processPhoneInput($post['country_code'] ?? '+63', $post['phone'] ?? ''),
            'role'       => clean($post['role'])
        ];

        if ($userModel->create($userData)) {
    $token = $userData['qr_token']; 
    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/';
    $qrPath = $uploadDir . 'qr_' . $token . '.png'; 
    
    $options = new QROptions([
        'version' => 5, 
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel' => 0, 
        'scale' => 10, 
        'imageBase64' => false,
    ]);
    
    (new QRCode($options))->render($token, $qrPath);

            $subject = "Official Welcome to BPC TruScan";
            $emailBody = "
                <div style='width: 100%; background-color: #f1f5f9; padding: 40px 0; font-family: sans-serif;'>
                    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; padding: 40px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                        
                        <div style='text-align: center; margin-bottom: 30px;'>
                            <div style='color: #10b981; font-size: 40px; margin-bottom: 10px;'><i class='fa-solid fa-circle-check'></i></div>
                            <h1 style='color: #1e293b; margin: 0; font-size: 24px; font-weight: 800;'>Welcome to the Team!</h1>
                            <p style='color: #64748b; font-size: 16px; margin-top: 8px;'>Bulacan Polytechnic College Attendance System</p>
                        </div>

                        <div style='color: #334155; line-height: 1.6; font-size: 15px;'>
                            <p>Dear <strong>{$userData['first_name']}</strong>,</p>
                            <p>We are pleased to inform you that your official account for <strong>BPC TruScan</strong> has been successfully created. This system will be your primary tool for recording daily attendance and managing your professional schedules.</p>
                        </div>
                        
                        <div style='background: #f8fafc; border: 1px solid #f1f5f9; padding: 20px; border-radius: 12px; margin: 25px 0;'>
                            <h4 style='margin: 0 0 10px 0; color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 1px;'>Login Credentials</h4>
                            <p style='margin: 5px 0;'><strong>Username:</strong> <code style='background: #e2e8f0; padding: 2px 6px; border-radius: 4px;'>{$userData['username']}</code></p>
                            <p style='margin: 5px 0;'><strong>Temp Password:</strong> <code style='background: #e2e8f0; padding: 2px 6px; border-radius: 4px;'>@defaultpass123</code></p>
                            <p style='margin: 10px 0 0 0; font-size: 12px; color: #ef4444;'>*You will be required to change this password upon your first login.</p>
                        </div>

                        <div style='text-align: center; border-top: 1px solid #f1f5f9; padding-top: 30px;'>
                            <h3 style='color: #1e293b; margin-bottom: 10px;'>Your Emergency QR Code</h3>
                            <p style='font-size: 14px; color: #64748b; margin-bottom: 20px;'>Please keep a copy of this QR code. It serves as your secondary authentication method should the biometric scanners be unavailable.</p>
                            
                            <img src='cid:qr_code' alt='Attendance QR' style='width: 200px; height: 200px; border: 8px solid #f8fafc; border-radius: 12px;'>
                            
                            <p style='margin-top: 20px; font-size: 12px; color: #94a3b8;'>Internal Faculty Use Only • Do not share this email.</p>
                        </div>
                    </div>
                    <div style='text-align: center; margin-top: 25px; color: #94a3b8; font-size: 12px;'>
                        <p>&copy; " . date('Y') . " BPC MIS Department. All rights reserved.</p>
                    </div>
                </div>";

           if (file_exists($qrPath)) {
        $sent = Mailer::sendWithQR($userData['email'], "Official Welcome to BPC TruScan", $emailBody, realpath($qrPath));
    }
            
            if ($sent) {
                $newUser = $userModel->findUserByUsername($userData['username']);
                $newId = $newUser['id'] ?? ($newUser['user_id'] ?? null);
                
                if ($newId) {
                    $notifModel->create($newId, "Welcome! Your attendance QR code is ready in your profile.");
                }

                $logModel->log($_SESSION['user_id'], 'User Created', "Account created for $facultyId");
                $this->setFlash("Account for {$userData['first_name']} created and Email sent!", 'success', 'create_account.php?tab=view');
            } else {
                $this->setFlash("Account created, but SMTP REJECTED the email. Check your Gmail App Password.", 'warning', 'create_account.php?tab=view');
            }
        }
    } catch (Exception $e) {
        $this->setFlash('Error: ' . $e->getMessage(), 'error', 'create_account.php?tab=create');
    }
}

    private function handleCsvImport($file, $userModel, $logModel, $notifModel) {
    ini_set('auto_detect_line_endings', true);
    $handle = fopen($file['tmp_name'], 'r');
    fgetcsv($handle);
    
    $imported = 0; $skipped = 0;

    while (($data = fgetcsv($handle)) !== false) {
        if (count($data) < 7) { $skipped++; continue; }
        
        $facultyId = clean($data[0]);
        if ($userModel->exists($facultyId)) { $skipped++; continue; }

        $qrToken = bin2hex(random_bytes(16));

        $userData = [
            'faculty_id'  => $facultyId,
            'last_name'   => clean($data[1]),
            'first_name'  => clean($data[2]),
            'middle_name' => clean($data[3]),
            'username'    => clean($data[4]), 
            'role'        => clean($data[5]),
            'email'       => clean($data[6]),
            'phone'       => $data[7] ?? '',
            'password'    => password_hash('@defaultpass123', PASSWORD_DEFAULT),
            'qr_token'    => $qrToken
        ];

        if ($userModel->create($userData)) {
            $imported++;
            
            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/';
            $qrPath = $uploadDir . 'qr_' . $qrToken . '.png';
            
            $options = new QROptions([
                'version' => 5, 
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => 0, 
                'scale' => 10, 
                'imageBase64' => false,
            ]);

            (new QRCode($options))->render($qrToken, $qrPath);

            if (file_exists($qrPath)) {
                $firstName = htmlspecialchars($userData['first_name']);
                $username = htmlspecialchars($userData['username']);
        
        $emailBody = "
                <div style='width: 100%; background-color: #f1f5f9; padding: 40px 0; font-family: sans-serif;'>
                    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; padding: 40px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                        
                        <div style='text-align: center; margin-bottom: 30px;'>
                            <div style='color: #10b981; font-size: 40px; margin-bottom: 10px;'><i class='fa-solid fa-circle-check'></i></div>
                            <h1 style='color: #1e293b; margin: 0; font-size: 24px; font-weight: 800;'>Welcome to the Team!</h1>
                            <p style='color: #64748b; font-size: 16px; margin-top: 8px;'>Bulacan Polytechnic College Attendance System</p>
                        </div>

                        <div style='color: #334155; line-height: 1.6; font-size: 15px;'>
                            <p>Dear <strong>{$userData['first_name']}</strong>,</p>
                            <p>We are pleased to inform you that your official account for <strong>BPC TruScan</strong> has been successfully created. This system will be your primary tool for recording daily attendance and managing your professional schedules.</p>
                        </div>
                        
                        <div style='background: #f8fafc; border: 1px solid #f1f5f9; padding: 20px; border-radius: 12px; margin: 25px 0;'>
                            <h4 style='margin: 0 0 10px 0; color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 1px;'>Login Credentials</h4>
                            <p style='margin: 5px 0;'><strong>Username:</strong> <code style='background: #e2e8f0; padding: 2px 6px; border-radius: 4px;'>{$userData['username']}</code></p>
                            <p style='margin: 5px 0;'><strong>Temp Password:</strong> <code style='background: #e2e8f0; padding: 2px 6px; border-radius: 4px;'>@defaultpass123</code></p>
                            <p style='margin: 10px 0 0 0; font-size: 12px; color: #ef4444;'>*You will be required to change this password upon your first login.</p>
                        </div>

                        <div style='text-align: center; border-top: 1px solid #f1f5f9; padding-top: 30px;'>
                            <h3 style='color: #1e293b; margin-bottom: 10px;'>Your Emergency QR Code</h3>
                            <p style='font-size: 14px; color: #64748b; margin-bottom: 20px;'>Please keep a copy of this QR code. It serves as your secondary authentication method should the biometric scanners be unavailable.</p>
                            
                            <img src='cid:qr_code' alt='Attendance QR' style='width: 200px; height: 200px; border: 8px solid #f8fafc; border-radius: 12px;'>
                            
                            <p style='margin-top: 20px; font-size: 12px; color: #94a3b8;'>Internal Faculty Use Only • Do not share this email.</p>
                        </div>
                    </div>
                    <div style='text-align: center; margin-top: 25px; color: #94a3b8; font-size: 12px;'>
                        <p>&copy; " . date('Y') . " BPC MIS Department. All rights reserved.</p>
                    </div>
                </div>";
                Mailer::sendWithQR($userData['email'], "Attendance QR", $emailBody, realpath($qrPath));
            }
        }
    }
    fclose($handle);
    $this->setFlash("Imported $imported users. Skipped $skipped.", 'success', 'create_account.php?tab=view');
}

    private function processPhoneInput($countryCode, $phoneNumber) {
        $countryCode = trim($countryCode);
        if (strpos($countryCode, '+') !== 0) {
            $countryCode = '+' . preg_replace('/\D/', '', $countryCode);
        }

        $cleanNumber = preg_replace('/\D/', '', $phoneNumber);

        if (strlen($cleanNumber) === 11 && strpos($cleanNumber, '0') === 0) {
            $cleanNumber = substr($cleanNumber, 1);
        }
        
        return $countryCode . $cleanNumber;
    }

    public function downloadTemplate() {
    $this->requireAdmin();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="bpc_template.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    fputcsv($output, ['Faculty ID', 'Last Name', 'First Name', 'Middle Name', 'Username', 'Role', 'Email', 'Phone']);

    fputcsv($output, ['FAC001', 'Dela Cruz', 'Juan', 'P.', 'jdelacruz', 'Teacher', 'juan@bpc.edu.ph', '09123456789']);
    
    fclose($output);
    exit();
}

    private function generateQrImage($token) {
    try {
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/';
        $filePath = $uploadDir . 'qr_' . $token . '.png';

        $options = new QROptions([
            'version'      => 5,
            'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'     => 0,
            'scale'        => 10,
            'imageBase64'  => false,
        ]);

        $qrcode = new QRCode($options);
        $qrcode->render($token, $filePath);

        return (file_exists($filePath) && filesize($filePath) > 0);
    } catch (Exception $e) {
        return false;
    }
}

    public function regenerateQr() {
    $this->requireAdmin(); 
    header('Content-Type: application/json');

    $userId = $_POST['user_id'] ?? null;
    $userModel = $this->model('User');

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Missing User ID']);
        exit;
    }

    $newToken = bin2hex(random_bytes(16)); 

    if ($userModel->updateQrToken($userId, $newToken)) {
        $this->generateQrImage($newToken); 
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }
    exit; 
}
}
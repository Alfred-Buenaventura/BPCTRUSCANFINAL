<?php
require_once __DIR__ . '/../core/controller.php';
require_once __DIR__ . '/../core/mailer.php';
require_once dirname(__DIR__) . '/init.php';


    use chillerlan\QRCode\QRCode;
    use chillerlan\QRCode\QROptions;

class AccountAdminController extends Controller {

    public function create() {
    $this->requireAdmin();
    $userModel = $this->model('User');
    $logModel = $this->model('ActivityLog');
    

    $data = [
        'pageTitle' => 'Admin Management', 
        'pageSubtitle' => 'Configure high-level system administrative access', 
        'error' => '', 
        'success' => ''
    ];

    $allActiveUsers = $userModel->getAllActive();

    $data['admins'] = array_filter($allActiveUsers, function($u) {
        return $u['role'] === 'Admin' || $u['role'] === 'Schedule Admin';
    });

    $data['stats']['admin_active'] = count($data['admins']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->verifyCsrfToken();
        
        $defaultPass = "@adminpass123"; 
        $qrToken = bin2hex(random_bytes(16)); 

        $adminData = [
            'faculty_id' => clean($_POST['faculty_id']),
            'username'   => strtolower(clean($_POST['faculty_id'])),
            'password'   => password_hash($defaultPass, PASSWORD_DEFAULT),
            'first_name' => clean($_POST['first_name']),
            'last_name'  => clean($_POST['last_name']),
            'middle_name'=> clean($_POST['middle_name'] ?? ''),
            'email'      => clean($_POST['email']),
            'phone'      => clean($_POST['phone'] ?? ''),
            'role'       => clean($_POST['role']),
            'qr_token'   => $qrToken 
        ];

        if ($userModel->exists($adminData['faculty_id'])) {
            $data['error'] = "Admin ID already exists.";
        } else if ($userModel->create($adminData)) {
            
            // generates the qr code and saves them to the appropriate directory
            $options = new QROptions(['version' => 5, 'outputType' => QRCode::OUTPUT_IMAGE_PNG, 'eccLevel' => 0, 'scale' => 10]);
            $tempPath = __DIR__ . '/../../public/uploads/admin_qr/' . $adminData['username'] . '.png';
            (new QRCode($options))->render($qrToken, $tempPath);

            $subject = "Administrative Access Granted: BPC TruScan";
            $emailBody = "
                <div style='width: 100%; background-color: #f1f5f9; padding: 40px 0; font-family: sans-serif;'>
                    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; padding: 40px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                        
                        <div style='text-align: center; margin-bottom: 30px;'>
                            <div style='color: #059669; font-size: 40px; margin-bottom: 10px;'><i class='fa-solid fa-shield-check'></i></div>
                            <h1 style='color: #1e293b; margin: 0; font-size: 24px; font-weight: 800;'>Administrative Access Initialized</h1>
                            <p style='color: #64748b; font-size: 16px; margin-top: 8px;'>Bulacan Polytechnic College | System Administration</p>
                        </div>

                        <div style='color: #334155; line-height: 1.6; font-size: 15px;'>
                            <p>Dear <strong>{$adminData['first_name']}</strong>,</p>
                            <p>Your administrative account for the <strong>BPC TruScan</strong> system has been successfully provisioned. You have been granted <strong>{$adminData['role']}</strong> privileges.</p>
                        </div>
                        
                        <div style='background: #f8fafc; border: 1px solid #f1f5f9; padding: 20px; border-radius: 12px; margin: 25px 0;'>
                            <h4 style='margin: 0 0 10px 0; color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 1px;'>Admin Credentials</h4>
                            <p style='margin: 5px 0;'><strong>Username:</strong> <code style='background: #e2e8f0; padding: 2px 6px; border-radius: 4px;'>{$adminData['username']}</code></p>
                            <p style='margin: 5px 0;'><strong>Temp Password:</strong> <code style='background: #e2e8f0; padding: 2px 6px; border-radius: 4px;'>@adminpass123</code></p>
                            <p style='margin: 10px 0 0 0; font-size: 12px; color: #ef4444;'>*Administrative security policy requires a password change upon first login.</p>
                        </div>

                        <div style='text-align: center; border-top: 1px solid #f1f5f9; padding-top: 30px;'>
                            <h3 style='color: #1e293b; margin-bottom: 10px;'>Your Administrative QR Code</h3>
                            <p style='font-size: 14px; color: #64748b; margin-bottom: 20px;'>Keep a copy of this identifier for physical terminal authentication when biometric scanning is bypassed.</p>
                            
                            <img src='cid:qr_code' alt='Attendance QR' style='width: 200px; height: 200px; border: 8px solid #f8fafc; border-radius: 12px;'>
                            
                            <p style='margin-top: 20px; font-size: 12px; color: #94a3b8;'>Sensitive Data • Authorized Personnel Only • BPC MIS</p>
                        </div>
                    </div>
                    <div style='text-align: center; margin-top: 25px; color: #94a3b8; font-size: 12px;'>
                        <p>&copy; " . date('Y') . " BPC MIS Department. All rights reserved.</p>
                    </div>
                </div>";

            Mailer::sendWithQR($adminData['email'], $subject, $emailBody, $tempPath, 'qr_code');

            $_SESSION['flash_created_admin'] = [
                'name' => $adminData['first_name'] . ' ' . $adminData['last_name'],
                'role' => $adminData['role'],
                'faculty_id' => $adminData['faculty_id']
            ];

            $logModel->log($_SESSION['user_id'], 'Admin Created', "Added system administrator: {$adminData['faculty_id']}");
            if (file_exists($tempPath)) unlink($tempPath);

            header("Location: " . URLROOT . "/accountadmin/create");
            exit();
        }
    }
    $this->view('admin_create_view', $data);
}
}
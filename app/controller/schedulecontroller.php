<?php
require_once __DIR__ . '/../core/controller.php';
require_once __DIR__ . '/../models/schedule.php';
require_once __DIR__ . '/../models/user.php';

class ScheduleController extends Controller {

    public function index() {
    $this->requireLogin();
    
    $scheduleModel = new Schedule();
    $userModel = new User();
    $logModel = $this->model('ActivityLog');
    $notificationModel = $this->model('Notification');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_conflict'])) {
        header('Content-Type: application/json');
        
        $checks = [];
        if (isset($_POST['schedules']) && is_array($_POST['schedules'])) {
            $checks = $_POST['schedules'];
        } else {
            $checks[] = [
                'day' => $_POST['day'],
                'start' => $_POST['start'],
                'end' => $_POST['end'],
                'room' => $_POST['room'],
                'id' => $_POST['id'] ?? null
            ];
        }

        foreach ($checks as $check) {
            $conflict = $scheduleModel->checkOverlap(
                $check['day'], 
                $check['start'], 
                $check['end'], 
                $check['room'],
                $check['id'] ?? null
            );

            if ($conflict) {
                echo json_encode([
                    'has_conflict' => true,
                    'conflict_details' => $conflict
                ]);
                exit;
            }
        }

        echo json_encode(['has_conflict' => false]);
        exit;
    }

    $isAdmin = Helper::anyAdmin();
    
    $data = [
        'pageTitle' => 'Schedule Management', 
        'pageSubtitle' => 'Manage and monitor duty schedules',
        'isAdmin' => $isAdmin,
        'error' => '',
        'success' => '',
        'searchQuery' => $_GET['search'] ?? '', 
        'allUsers' => [],
        'groupedApprovedSchedules' => [], 
        'groupedPendingSchedules' => [],
        'myGroupedSchedule' => [],       
        'myPendingSchedule' => [],
        'rooms' => $scheduleModel->getRooms(),
        'pendingCount' => 0
    ];

    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrfToken();

            if (isset($_POST['bulk_action_type']) && !empty($_POST['selected_schedules'])) {
                $this->requireAdmin();
                $action = $_POST['bulk_action_type'];
                $selectedIds = $_POST['selected_schedules'];
                $processedCount = 0;

                foreach ($selectedIds as $id) {
                    $schedule = $scheduleModel->findById($id);
                    if (!$schedule) continue;

                    if ($action === 'approve') {
                        if ($scheduleModel->updateStatus($id, 'approved')) {
                            $this->sendApprovalEmail($schedule['user_id'], [$schedule]);
                            $this->model('Notification')->create(
                                $schedule['user_id'], 
                                "Schedule Approved", 
                                "Your duty for {$schedule['day_of_week']} ({$schedule['subject']}) was approved.", 
                                "success"
                            );
                            $processedCount++;
                        }
                    } else {
                        if ($scheduleModel->delete($id, $schedule['user_id'], true)) {
                            $this->sendDeclineEmail($schedule['user_id'], [$schedule]);
                            $this->model('Notification')->create(
                                $schedule['user_id'], 
                                "Schedule Declined", 
                                "Your schedule for {$schedule['day_of_week']} was declined.", 
                                "error"
                            );
                            $processedCount++;
                        }
                    }
                }
                $_SESSION['success_modal'] = "Successfully processed $processedCount schedules. All users have been notified.";
                header("Location: schedule_management.php");
                exit;
            }

            elseif (isset($_POST['add_schedule'])) {
                $userId = $_POST['user_id'] ?? $_SESSION['user_id'];
                $schedules = [];

                if (isset($_POST['day_of_week']) && is_array($_POST['day_of_week'])) {
                    for ($i = 0; $i < count($_POST['day_of_week']); $i++) {
                        $schedules[] = [ 
                            'day' => $_POST['day_of_week'][$i], 
                            'subject' => $_POST['subject'][$i], 
                            'start' => $_POST['start_time'][$i], 
                            'end' => $_POST['end_time'][$i], 
                            'room' => $_POST['room'][$i],
                            'type' => $_POST['type'][$i] ?? 'Class' 
                        ];
                    }

                    if ($scheduleModel->create($userId, $schedules, $isAdmin)) {
                        if (!$isAdmin) {
                            $this->notifyAdminsOfPendingSchedule($userId, $schedules);
                            $_SESSION['success_modal'] = "Schedule submitted! You will be notified once an administrator reviews it.";
                        } else {
                            $_SESSION['success_modal'] = "Schedule registered and approved successfully.";
                        }
                        $logModel->log($userId, 'Schedule Submitted', ($isAdmin ? "Admin created approved" : "Faculty submitted pending") . " schedule.");
                        header("Location: schedule_management.php");
                        exit;
                    } else { 
                        $data['error'] = "Failed to register schedule(s)."; 
                    }
                }
            }

            elseif (isset($_POST['approve_schedule'])) {
                $this->requireAdmin(); 
                $schedId = $_POST['schedule_id'];
                $schedule = $scheduleModel->findById($schedId); 

                if ($schedule && $scheduleModel->updateStatus($schedId, 'approved')) {
                    $this->sendApprovalEmail($schedule['user_id'], [$schedule]);
                    $notificationModel->create($schedule['user_id'], "Schedule Approved", "Your duty for {$schedule['day_of_week']} ({$schedule['subject']}) has been approved.", "success");
                    $logModel->log($_SESSION['user_id'], 'Schedule Approved', "Approved session ID: $schedId");
                    
                    $_SESSION['success_modal'] = "Schedule approved. Notification and email sent to user.";
                    header("Location: schedule_management.php");
                    exit;
                }
            }

            elseif (isset($_POST['delete_schedule']) || isset($_POST['decline_schedule'])) {
                $schedId = $_POST['schedule_id_delete'] ?? $_POST['schedule_id'];
                $schedule = $scheduleModel->findById($schedId);
                $ownerId = $_POST['user_id_delete'] ?? ($schedule['user_id'] ?? null);

                if ($scheduleModel->delete($schedId, $ownerId, $isAdmin)) {
                    if ($isAdmin && $schedule && isset($_POST['decline_schedule'])) {
                        $this->sendDeclineEmail($ownerId, [$schedule]);
                        $notificationModel->create($ownerId, "Schedule Declined", "Your schedule for {$schedule['day_of_week']} was declined.", "error");
                        $_SESSION['success_modal'] = "Schedule declined. User has been notified.";
                    } else {
                        $_SESSION['success_modal'] = "Schedule removed successfully.";
                    }
                    header("Location: schedule_management.php");
                    exit;
                }
            }

            elseif (isset($_POST['edit_schedule'])) {
                $schedId = $_POST['schedule_id_edit'];
                if ($scheduleModel->update($schedId, $_POST['day_of_week_edit'], $_POST['subject_edit'], $_POST['start_time_edit'], $_POST['end_time_edit'], $_POST['room_edit'], $_POST['type_edit'])) {
                    $data['success'] = "Schedule updated successfully.";
                    $logModel->log($_SESSION['user_id'], 'Schedule Updated', "Updated session ID: $schedId");
                }
            }
        }
        
    } catch (Exception $e) {
        $data['error'] = 'System error: ' . $e->getMessage();
    }

    if ($isAdmin) {
        $data['allUsers'] = $userModel->getAllActive(); 
        $data['groupedApprovedSchedules'] = $scheduleModel->getGroupedSchedulesByStatus('approved', $data['searchQuery']);
        $data['groupedPendingSchedules'] = $scheduleModel->getGroupedSchedulesByStatus('pending');
        $data['pendingCount'] = count($data['groupedPendingSchedules']);
    } else {
        $userId = $_SESSION['user_id'];
        $rawSchedules = $scheduleModel->getByUser($userId, 'approved');
        $myGrouped = ['daily_schedules' => [], 'stats' => ['total_hours' => 0, 'day_totals' => []]];
        
        foreach ($rawSchedules as $s) {
            $day = $s['day_of_week'];
            $duration = (strtotime($s['end_time']) - strtotime($s['start_time'])) / 3600;
            if (!isset($myGrouped['stats']['day_totals'][$day])) $myGrouped['stats']['day_totals'][$day] = 0;
            $myGrouped['stats']['day_totals'][$day] += $duration;
            $myGrouped['stats']['total_hours'] += $duration;
            $myGrouped['daily_schedules'][] = $s;
        }
        $data['myGroupedSchedule'] = $myGrouped;
        $data['myPendingSchedule'] = $scheduleModel->getByUser($userId, 'pending');
        $data['userStats'] = $scheduleModel->getUserStats($userId);
        $data['selectedUserInfo'] = $userModel->findById($userId);
    }

    $this->view('schedule_view', $data);
}


private function sendAutoDeclineEmail($user, $scheduleDetails) {
    $firstName = htmlspecialchars($user['first_name']);
    $emailSubject = "Schedule Request Update - BPC Attendance System";
    $day = htmlspecialchars($scheduleDetails['day_of_week']);
    $time = date('g:i A', strtotime($scheduleDetails['start_time'])) . ' - ' . date('g:i A', strtotime($scheduleDetails['end_time']));
    $room = htmlspecialchars($scheduleDetails['room']);
    $emailBody = "<!DOCTYPE html><html><head><style>body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; } .container { max-width: 600px; margin: 0 auto; padding: 20px; } .header { background: #dc2626; color: white; padding: 25px; text-align: center; border-radius: 8px 8px 0 0; } .content { background: #ffffff; padding: 40px 30px; border: 1px solid #e5e7eb; border-top: none; } .warning-box { background: #fef2f2; border-left: 4px solid #dc2626; padding: 20px; margin: 25px 0; border-radius: 4px; color: #7f1d1d; } .footer { background: #f3f4f6; padding: 25px; text-align: center; font-size: 13px; color: #6b7280; border-radius: 0 0 8px 8px; border: 1px solid #e5e7eb; border-top: none; }</style></head><body><div class='container'><div class='header'><h2 style='margin:0; font-weight:600;'>Schedule Update</h2></div><div class='content'><p>Dear <strong>{$firstName}</strong>,</p><p>We are writing to inform you regarding your recent schedule request.</p><div class='warning-box'><p style='margin-top:0; font-weight:bold;'>Notice of Schedule Unavailability</p><p style='margin-bottom:0;'>The schedule slot you requested for <strong>{$day}</strong> at <strong>{$time}</strong> in <strong>{$room}</strong> is no longer available as it has been allocated to another faculty member.</p></div><p>Consequently, your pending request for this specific slot has been automatically removed from the system.</p></div><div class='footer'><p><strong>Bulacan Polytechnic College</strong></p></div></div></body></html>";
    return sendEmail($user['email'], $emailSubject, $emailBody);
}

private function sendApprovalEmail($userOrId, $schedules) {
    if (!is_array($userOrId)) {
        $userModel = $this->model('User');
        $user = $userModel->findById($userOrId);
    } else {
        $user = $userOrId;
    }

    if (!$user || empty($user['email'])) return false;

    $firstName = htmlspecialchars($user['first_name']);
    $emailSubject = "Schedule Approved - BPC Attendance System";

    $emailBody = "<!DOCTYPE html><html><head><style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #059669; color: white; padding: 25px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #ffffff; padding: 30px; border: 1px solid #e5e7eb; }
        .schedule-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .schedule-table th { background: #f0fdf4; color: #166534; padding: 12px; text-align: left; border-bottom: 2px solid #059669; font-size: 13px; text-transform: uppercase; }
        .schedule-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-radius: 0 0 8px 8px; border: 1px solid #e5e7eb; border-top: none; }
        .success-badge { display: inline-block; background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 50px; font-weight: bold; font-size: 12px; }
    </style></head><body>
    <div class='container'>
        <div class='header'><h2 style='margin: 0;'>Schedule Approved</h2></div>
        <div class='content'>
            <p>Dear <strong>{$firstName}</strong>,</p>
            <p>Your duty schedule has been <span class='success-badge'>APPROVED</span>. Below are the details:</p>
            <table class='schedule-table'>
                <thead><tr><th>Day</th><th>Subject</th><th>Time</th><th>Room</th></tr></thead>
                <tbody>";

    foreach ($schedules as $s) {
        $day = htmlspecialchars($s['day'] ?? $s['day_of_week']);
        $subject = htmlspecialchars($s['subject']);
        $startTime = date('g:i A', strtotime($s['start_time']));
        $endTime = date('g:i A', strtotime($s['end_time']));
        $room = htmlspecialchars($s['room']);
        $emailBody .= "<tr><td><strong>{$day}</strong></td><td>{$subject}</td><td>{$startTime} - {$endTime}</td><td>{$room}</td></tr>";
    }

    $emailBody .= "</tbody></table>
            <p style='color: #64748b; font-size: 0.9rem;'>Please ensure you are present at your designated locations at the scheduled times.</p>
        </div>
        <div class='footer'><p><strong>Bulacan Polytechnic College</strong><br>Attendance Monitoring System</p></div>
    </div></body></html>";

    return sendEmail($user['email'], $emailSubject, $emailBody);
}

private function sendDeclineEmail($userOrId, $schedules) {
    if (!is_array($userOrId)) {
        $userModel = $this->model('User');
        $user = $userModel->findById($userOrId);
    } else {
        $user = $userOrId;
    }

    if (!$user || empty($user['email'])) return false;
        $days = array_unique(array_column($schedules, 'day_of_week'));
    if (empty($days)) $days = array_unique(array_column($schedules, 'day'));
        $daysString = implode(', ', $days);

        $firstName = htmlspecialchars($user['first_name']);
    $emailSubject = "Schedule Declined - Action Required";
    
        $emailBody = "<!DOCTYPE html><html><head><style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc2626; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #ffffff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; }
            .warning-box { background: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; color: #991b1b; }
            .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-radius: 0 0 8px 8px; border: 1px solid #e5e7eb; border-top: none; }
            </style></head><body>
        <div class='container'>
            <div class='header'><h2 style='margin: 0;'>Schedule Declined</h2></div>
            <div class='content'>
                <p>Dear <strong>{$firstName}</strong>,</p>
                <div class='warning-box'>
                    <p style='margin: 0;'><strong>Your schedule submission for {$daysString} has been declined.</strong></p>
                </div>
                <p>Please log in to your dashboard to review the feedback and submit an updated schedule. If you have any questions, please contact the administrator.</p>
            </div>
            <div class='footer'><p><strong>Bulacan Polytechnic College</strong><br>Attendance Monitoring System</p></div>
        </div></body></html>";

    return sendEmail($user['email'], $emailSubject, $emailBody);
}

private function notifyAdminsOfPendingSchedule($userId, $schedules) {
    $userModel = $this->model('User');
    $db = Database::getInstance();
    $submitter = $userModel->findById($userId);
        if (!$submitter) return;
            $submitterName = $submitter['first_name'] . ' ' . $submitter['last_name'];
            $scheduleCount = count($schedules);
            $days = array_unique(array_column($schedules, 'day'));
            $daysString = implode(', ', $days);
            $message = "{$submitterName} has submitted {$scheduleCount} schedule(s) for {$daysString} pending your approval.";
            $stmt = $db->query("SELECT id FROM users WHERE role = 'Admin' AND status = 'active'", [], "");
            $admins = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($admins as $admin) { Notification::create($admin['id'], $message, 'warning'); }
    }
}
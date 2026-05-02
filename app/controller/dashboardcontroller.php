<?php
require_once __DIR__ . '/../core/controller.php';

class DashboardController extends Controller {

    public function index() {
        $this->requireLogin();

        $data = [];
        $userModel = $this->model('User');
        $logModel = $this->model('ActivityLog');
        $attModel = $this->model('Attendance');
        $holidayModel = $this->model('Holiday'); // Loaded to fetch system and holiday data
        
        $data['pageTitle'] = 'Dashboard';
        $role = $_SESSION['role'] ?? 'Staff';
        $data['role'] = $role;

        if ($role === 'Admin') {
            $data['pageSubtitle'] = 'Welcome back, System Administrator!';
            $data['totalUsers'] = $userModel->countActive();
            $data['pendingRegistrations'] = $userModel->countPendingFingerprint();
            $data['activeToday'] = $attModel->countActiveToday();
            $data['activityLogs'] = $logModel->getRecentLogs(5);
            $data['isAdmin'] = true;
        } 
        // Specialized logic for the Schedule Administrator
        elseif ($role === 'Schedule Admin') {
            $data['pageSubtitle'] = 'Welcome back, Schedule Administrator!';
            
            // Operational Metrics for oversight
            $data['staffCount'] = $userModel->countActiveStaff();
            $data['missingSchedules'] = $userModel->countMissingSchedules();
            $data['upcomingHolidays'] = $holidayModel->getUpcoming(3);
            $data['settings'] = $holidayModel->getSystemSettings();
            
            $data['activityLogs'] = $logModel->getRecentLogs(5, $_SESSION['user_id']);
            $data['isAdmin'] = false;
            $data['isScheduleAdmin'] = true; // Flag for view-side conditional logic
        } 
        else {
            $firstName = $_SESSION['first_name'] ?? 'User';
            $data['pageSubtitle'] = "Welcome back, " . htmlspecialchars($firstName) . "!";
            $data['fingerprint_registered'] = $userModel->getFingerprintStatus($_SESSION['user_id']);
            $data['attendance'] = $attModel->getDailySummary($_SESSION['user_id']); 
            $data['activityLogs'] = $logModel->getRecentLogs(5, $_SESSION['user_id']);
            $data['stats'] = $attModel->getStats($_SESSION['user_id']); 
            $data['isAdmin'] = false;
        }

        $this->view('dashboard_view', $data);
    }
}
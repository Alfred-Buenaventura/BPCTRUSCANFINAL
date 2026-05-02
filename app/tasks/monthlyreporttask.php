<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'db5019021856.hosting-data.io'; 
$user = 'dbu226629'; 
$pass = '@bpctruscanpass.'; 
$name = 'dbs14972169';

$conn = new mysqli($host, $user, $pass, $name);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

echo "<h1>BPC TruScan: Comprehensive Monthly Audit</h1><hr>";

$smtp_user = "bpcattendancemonitoringsystem@gmail.com";
$smtp_pass = "vdlroohafikinkbe"; 
putenv("SMTP_USER=$smtp_user"); putenv("SMTP_PASS=$smtp_pass");
$_ENV['SMTP_USER'] = $smtp_user; $_SERVER['SMTP_USER'] = $smtp_user;
$_ENV['SMTP_PASS'] = $smtp_pass; $_SERVER['SMTP_PASS'] = $smtp_pass;

require_once __DIR__ . '/../core/mailer.php';

$userResult = $conn->query("SELECT id, first_name, email FROM users WHERE email_notifications_enabled = 1");

while($row = $userResult->fetch_assoc()) {
    $userId = $row['id'];
    $firstName = $row['first_name'];
    $email = $row['email'];

    $logSql = "SELECT * FROM attendance_records 
               WHERE user_id = $userId 
               AND date BETWEEN '2026-02-01' AND '2026-02-28'";
    
    $logRes = $conn->query($logSql);
    
    $totalLogs = $logRes->num_rows;
    $lateCount = 0;
    $unscheduledCount = 0;
    $sumHours = 0.0;
    
    while($log = $logRes->fetch_assoc()) {
        $hourVal = $log['total_hours'] ?? $log['hours_rendered'] ?? $log['credited_hours'] ?? 0;
        $sumHours += (float)$hourVal;

        if (stripos($log['status'] ?? '', 'Late') !== false) $lateCount++;
        if (stripos($log['status'] ?? '', 'Unscheduled') !== false) $unscheduledCount++;
    }

    $presentDays = $totalLogs - $unscheduledCount;
    $absences = max(0, 20 - $presentDays);

    if ($totalLogs == 0) continue;

    $subject = "Official Attendance Audit: February 2026";
    $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e1e4e8; border-radius: 12px; overflow: hidden;'>
            <div style='background: #1a73e8; color: white; padding: 20px; text-align: center;'>
                <h1 style='margin:0; font-size: 20px;'>Monthly Attendance Summary</h1>
                <p style='margin:5px 0 0;'>February 1, 2026 - February 28, 2026</p>
            </div>
            <div style='padding: 20px;'>
                <p>Hello <b>$firstName</b>,</p>
                <p>Your biometric attendance data has been audited. Here is your summary for the month:</p>
                
                <table style='width: 100%; border-collapse: collapse; margin-top: 15px;'>
                    <tr style='background: #f8f9fa;'>
                        <td style='padding: 12px; border: 1px solid #eee;'><b>Total Rendered Time</b></td>
                        <td style='padding: 12px; border: 1px solid #eee; text-align: right;'><b>" . number_format($sumHours, 2) . " Hours</b></td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; border: 1px solid #eee;'>Total Duty Days Logged</td>
                        <td style='padding: 12px; border: 1px solid #eee; text-align: right;'>$presentDays</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; border: 1px solid #eee; color: #d93025;'>Tardiness Incidences</td>
                        <td style='padding: 12px; border: 1px solid #eee; text-align: right; color: #d93025;'>$lateCount</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; border: 1px solid #eee; color: #cc0000;'>Estimated Absences</td>
                        <td style='padding: 12px; border: 1px solid #eee; text-align: right; color: #cc0000;'>$absences</td>
                    </tr>
                </table>
            </div>
            <div style='background: #f1f3f4; padding: 15px; text-align: center; font-size: 12px; color: #5f6368;'>
                BPC TruScan System | Bulacan Polytechnic College
            </div>
        </div>";

    if (Mailer::send($email, $subject, $message)) {
        echo "<b style='color:green;'>SUCCESS:</b> Comprehensive report sent to $firstName.<br>";
    }
}
$conn->close();
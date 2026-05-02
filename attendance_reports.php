<?php
session_start();
require_once 'app/init.php';
require_once 'app/controllers/attendancecontroller.php';

$controller = new AttendanceController();
$action = $_GET['action'] ?? 'index';

if ($action === 'print_dtr') {
    $controller->printDtr();
} else {
    $controller = new AttendanceController();
    $controller->index();
}
?>
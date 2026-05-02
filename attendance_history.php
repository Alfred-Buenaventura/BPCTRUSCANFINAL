<?php
session_start();
require_once 'app/init.php';
require_once 'app/controllers/attendancecontroller.php';

$controller = new AttendanceController();

if (isset($_GET['action']) && $_GET['action'] === 'exportExcel') {
    $controller->exportHistoryExcel();
} else {
    $controller->history();
}
?>
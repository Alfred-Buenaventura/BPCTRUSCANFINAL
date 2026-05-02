<?php
// detects if the system is on localhost or a ionos hosting server
$isLocal = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || $_SERVER['SERVER_NAME'] == 'localhost';

define('URLROOT', 'https://bpctruscan.com');

if ($isLocal) {
    // --- LOCALHOST (XAMPP) SETTINGS ---
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'bpc_attendance');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('BASE_URL', '/bpc_attendance/');
} else {
    // --- LIVE SERVER (IONOS) SETTINGS ---
    define('DB_HOST', 'db5019021856.hosting-data.io');
    define('DB_NAME', 'dbs14972169'); 
    define('DB_USER', 'dbu226629');
    define('DB_PASS', '@bpctruscanpass.');
    define('BASE_URL', 'https://bpctruscan.com');
}

<?php
class Database {
    private static $instance = null;
    public $conn;

    private $host;
    private $user;
    private $pass;
    private $dbname;

    private function __construct() {
    $this->host   = defined('DB_HOST') ? DB_HOST : ($_ENV['DB_HOST'] ?? 'localhost');
    $this->user   = defined('DB_USER') ? DB_USER : ($_ENV['DB_USER'] ?? 'root');
    $this->pass   = defined('DB_PASS') ? DB_PASS : ($_ENV['DB_PASS'] ?? '');
    $this->dbname = defined('DB_NAME') ? DB_NAME : ($_ENV['DB_NAME'] ?? 'bpc_attendance');

    $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
    
    if ($this->conn->connect_error) {
        error_log("Database Connection Failed: " . $this->conn->connect_error);
        die("System connection error. Please contact the administrator.");
    }
    
    $this->conn->set_charset("utf8mb4");
}

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function query($sql, $params = [], $types = "") {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Query Prepare Failed: " . $this->conn->error);
        }

        if (!empty($params)) {
            if (empty($types)) {
                $types = str_repeat('s', count($params)); 
            }
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
             throw new Exception("Query Execute Failed: " . $stmt->error);
        }

        return $stmt;
    }
}
?>
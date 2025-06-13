<?php
class Database {
    private static $instance = null;
    private $conn;
    private $host = 'localhost';
    private $db_name = 'dbstorage23360859455';
    private $username = 'dbusr23360859455';
    private $password = 'Y9bPjtQ9GjkY';
    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("Veritabanı bağlantı hatası: " . $e->getMessage());
        }
    }
    // Singleton instance'ı almak için
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    // Veritabanı bağlantısını almak için
    public function getConnection() {
        return $this->conn;
    }
    // Singleton pattern için clone'lamayı engelle
    private function __clone() {}
    // Singleton pattern için unserialize'i engelle
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
?> 
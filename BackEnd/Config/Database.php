<?php
class Database {
    private $host     = "localhost";
    private $db_name  = "kantinku";
    private $username = "root";
    private $password = "";
    private $conn     = null;

    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $exception) {
                die("Gagal Terhubung ke Database MySQL: " . $exception->getMessage());
            }
        }
        return $this->conn;
    }
}
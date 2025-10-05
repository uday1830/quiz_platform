<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'quiz_platform');

class Database {
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }

            $this->conn->set_charset("utf8mb4");

        } catch(Exception $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}

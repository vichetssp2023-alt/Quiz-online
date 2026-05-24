<?php
class Database {
    private $host = "localhost";
    private $db_name = "quiz_online_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
                PDO::ATTR_EMULATE_PREPARES => false, 
            ];

            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", 
                $this->username, 
                $this->password, 
                $options
            );

        } catch(PDOException $exception) {
            error_log($exception->getMessage()); 
            die("សុំទោស! មានបញ្ហាបច្ចេកទេសក្នុងការតភ្ជាប់មូលដ្ឋានទិន្នន័យ។");
        }
        return $this->conn;
    }
}
?>
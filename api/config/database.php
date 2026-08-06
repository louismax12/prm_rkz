<?php

// Konfigurasi dari kwitansi
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'prm_rkz'); // Menggunakan DB prm_rkz
define('DB_USER', 'root');
define('DB_PASS', '123456789');

class Database {
    private $host = DB_HOST;
    private $port = DB_PORT;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    // get the database connection
    public function getConnection() {
        $this->conn = null;

        try {
            // Menggunakan PDO (didukung penuh di PHP 5.4.2)
            $this->conn = new PDO("mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, $this->username, $this->password);
            // set error mode untuk memunculkan exception jika gagal
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>

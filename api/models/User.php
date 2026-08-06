<?php
class User {
    private $conn;
    private $table_name = "prm_users";

    public $id;
    public $username;
    public $password_hash;
    public $nama_lengkap;
    public $role;

    public function __construct($db){
        $this->conn = $db;
    }

    // Fungsi login untuk mencari user berdasarkan username
    function login($username, $password){
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $hashedInput = md5($password);
            
            if(strcasecmp(trim($hashedInput), trim($row['password_hash'])) === 0) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->nama_lengkap = $row['nama_lengkap'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }
}
?>

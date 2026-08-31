<?php
class User {
    private $conn;

    public $id;
    public $username;
    public $password_hash;
    public $nama_lengkap;
    public $role;

    public function __construct($db){
        $this->conn = $db;
    }

    function login($username, $password){
        $safeUsername = trim((string)$username);
        $safePassword = trim((string)$password);

        if($safeUsername === '' || $safePassword === '') {
            return false;
        }

        $query = "SELECT NIP AS username, password AS password_hash, Nama AS nama_lengkap, Bagian, level FROM datadasar WHERE NIP = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $safeUsername);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$row) {
            return false;
        }

        $storedPassword = isset($row['password_hash']) ? trim((string)$row['password_hash']) : '';
        $plainMatches = $storedPassword !== '' && strcasecmp($safePassword, $storedPassword) === 0;
        $md5Matches = $storedPassword !== '' && strcasecmp(md5($safePassword), $storedPassword) === 0;

        if($plainMatches || $md5Matches) {
            $this->id = $safeUsername;
            $this->username = $safeUsername;
            $this->nama_lengkap = !empty($row['nama_lengkap']) ? $row['nama_lengkap'] : $safeUsername;
            $this->role = $this->resolveRole($row, $safeUsername);
            return true;
        }

        return false;
    }

    private function resolveRole($row, $username) {
        $bagian = isset($row['Bagian']) ? strtolower((string)$row['Bagian']) : '';
        $level = isset($row['level']) ? (int)$row['level'] : 0;

        if ($username === 'admin' || strpos($bagian, 'direksi') !== false) {
            return 'admin';
        }

        if ($bagian === 'keuangan' || strpos($bagian, 'keuangan') !== false) {
            return 'kasir';
        }

        if ($level >= 7) {
            return 'admin';
        }

        return 'erm';
    }
}
?>

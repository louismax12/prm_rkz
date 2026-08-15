<?php
include_once 'api/config/database.php';
include_once 'api/models/User.php';

$database = new Database();
$db = $database->getConnection('hrd');
$user = new User($db);

$username = '03690';
$password = '895623';

echo "Testing login for $username / $password\n";
if($user->login($username, $password)) {
    echo "Login Sukses!\n";
    echo "Nama Lengkap: " . $user->nama_lengkap . "\n";
    echo "Role: " . $user->role . "\n";
} else {
    echo "Login Gagal!\n";
    
    // Check what is in the DB
    $query = "SELECT NIP AS username, password AS password_hash, Nama AS nama_lengkap, Bagian, level FROM datadasar WHERE NIP = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row) {
        echo "Data ditemukan di DB:\n";
        print_r($row);
    } else {
        echo "Data tidak ditemukan di DB.\n";
    }
}
?>

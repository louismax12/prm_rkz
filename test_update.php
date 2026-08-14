<?php
require 'local/api/config/database.php';
require 'local/api/models/Paket.php';

$database = new Database();
$db = $database->getConnection();
$paket = new Paket($db);

$paket->id = 1;
$paket->nama = "Test Paket";
$paket->tipe_paket = "Rawat Jalan";
$paket->total_sesi = 10;
$paket->masa_berlaku_hari = 30;

if($paket->update()) {
    echo "Update successful\n";
} else {
    echo "Update failed\n";
    print_r($paket->conn->errorInfo());
}
?>

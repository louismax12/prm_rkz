<?php
require 'config/database.php';
$db = (new Database())->getConnection();

// Check prm_kapasitas for those patients
$sql = "SELECT k.id, k.no_erm, k.id_paket, p.nama, k.tanggal_beli 
        FROM dbold.prm_kapasitas k 
        LEFT JOIN dbold.prm_master_paket p ON k.id_paket = p.id 
        WHERE k.no_erm IN ('260805034', '260805007', '260805034  ', '260805007  ')";
$stmt = $db->query($sql);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>

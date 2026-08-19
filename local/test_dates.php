<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection(); // dbold

// Insert a dummy package for RM '260805849  '
$query = "INSERT INTO prm_kapasitas (no_erm, nomor_register, id_paket, sisa, tanggal_beli, tanggal_expired, status)
          VALUES ('260805849  ', 'REG-TEST-123', 1, 5, '2026-08-01', '2026-09-01', 'AKTIF')";
if($db->query($query)) {
    echo "Successfully added dummy package for RM 260805849!\n";
} else {
    echo "Failed to add dummy package.\n";
}
?>

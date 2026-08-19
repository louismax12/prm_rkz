<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

// Delete existing dummy data for this RM
$db->query("DELETE FROM prm_kapasitas WHERE no_erm = '260805849  '");
$db->query("DELETE FROM prm_catatan WHERE no_erm = '260805849  '");

// Insert a fresh dummy package for RM '260805849  ' with full 10 sessions
$query = "INSERT INTO prm_kapasitas (no_erm, nomor_register, id_paket, sisa, tanggal_beli, tanggal_expired, status)
          VALUES ('260805849  ', 'REG-TEST-123', 1, 10, '2026-08-01', '2026-09-01', 'AKTIF')";
if($db->query($query)) {
    echo "Berhasil me-reset data dummy menjadi utuh 10 sesi.\n";
} else {
    echo "Gagal.\n";
}
?>

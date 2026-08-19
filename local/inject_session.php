<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection(); // dbold

// Insert a dummy session for id_kapasitas = 7 (which we inserted earlier)
$query = "INSERT INTO prm_catatan (id_kapasitas, id_tindakan, no_erm, no_register_kunjungan, tanggal_paket, sesi_ke)
          VALUES (7, 1, '260805849  ', 'REG-TEST-123', '2026-08-16 10:00:00', 1)";
if($db->query($query)) {
    echo "Successfully added dummy session for RM 260805849!\n";
} else {
    echo "Failed to add dummy session.\n";
}
?>

<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

// Hapus kapasitas yang no_erm nya kosong (karena error Kasir tadi)
$db->query("DELETE FROM prm_kapasitas WHERE TRIM(no_erm) = ''");

// Hapus juga status processed-nya di kasir_processed agar bisa diulangi
$db->query("DELETE FROM prm_kasir_processed WHERE id_transaksi = '12113452' OR id_transaksi = '03413     '");

echo "Berhasil menghapus data rusak.\n";
?>

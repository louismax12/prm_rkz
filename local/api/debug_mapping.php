<?php
require 'config/database.php';
$db = (new Database())->getConnection();

echo "--- prm_master_paket ---\n";
$stmt = $db->query("SELECT * FROM dbold.prm_master_paket ORDER BY id DESC LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- prm_kasir_paket_mapping ---\n";
$stmt = $db->query("SELECT * FROM dbold.prm_kasir_paket_mapping ORDER BY id DESC LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- prm_kapasitas ---\n";
$stmt = $db->query("SELECT id, no_erm, id_paket, tanggal_beli FROM dbold.prm_kapasitas ORDER BY id DESC LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>

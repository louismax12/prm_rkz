<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

$q = "INSERT IGNORE INTO prm_kasir_paket_mapping (nama_paket_kasir, id_paket_master)
      SELECT DISTINCT t.nama, 1 
      FROM dbold.fisiosfjual f
      JOIN dbold.m_tindakan2026 t ON f.FCRBARANG = t.kode
      WHERE t.asaltabel = 'SFMASBIA' AND f.FCRTAMBAH = 'T'";
      
try {
    $db->exec($q);
    echo "Semua data kasir berhasil dipetakan secara otomatis!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

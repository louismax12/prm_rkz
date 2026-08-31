<?php
require 'local/api/config/database.php';
$db = new Database();
$conn = $db->getConnection();

$ids = [1468472]; // Using the valid ID we found earlier

$inQuery = implode(',', array_fill(0, count($ids), '?'));
$qInfo = "SELECT 
            f.ID as id_transaksi,
            f.FCRID as nomor_register, 
            f.FCRCUST as no_erm,
            f.FCRDATE as tanggal_beli,
            t.nama as nama_paket_kasir
          FROM dbold.fisiosfjual f
          JOIN dbold.m_tindakan2026 t ON f.FCRBARANG = t.kode
          WHERE f.ID IN ($inQuery) AND t.asaltabel = 'SFMASBIA'
          GROUP BY f.ID";
$stmtInfo = $conn->prepare($qInfo);
$stmtInfo->execute($ids);
$transaksiList = $stmtInfo->fetchAll(PDO::FETCH_ASSOC);

echo "Transaksi List count: " . count($transaksiList) . "\n";
print_r($transaksiList);

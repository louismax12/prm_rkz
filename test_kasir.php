<?php
require 'local/api/config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Fetch one valid ID from getHistory query logic
$q = "SELECT f.ID FROM dbold.fisiosfjual f
      JOIN dbold.m_tindakan2026 t ON f.FCRBARANG = t.kode
      WHERE t.asaltabel = 'SFMASBIA' AND f.FCRTAMBAH = 'T' LIMIT 1";
$stmt = $conn->query($q);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo "Found ID: " . $row['ID'] . "\n";
    $id = $row['ID'];
    
    // Test the processKasir query
    $qInfo = "SELECT 
                f.ID as id_transaksi,
                f.FCRID as nomor_register, 
                f.FCRCUST as no_erm,
                f.FCRDATE as tanggal_beli,
                t.nama as nama_paket_kasir
              FROM dbold.fisiosfjual f
              JOIN dbold.m_tindakan2026 t ON f.FCRBARANG = t.kode
              WHERE f.ID IN (?) AND t.asaltabel = 'SFMASBIA'
              GROUP BY f.ID";
    $stmtInfo = $conn->prepare($qInfo);
    $stmtInfo->execute([$id]);
    $res = $stmtInfo->fetchAll(PDO::FETCH_ASSOC);
    print_r($res);
} else {
    echo "No valid ID found in getHistory logic.\n";
}

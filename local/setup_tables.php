<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

$q1 = "CREATE TABLE IF NOT EXISTS prm_kasir_processed (
    id_transaksi VARCHAR(100) PRIMARY KEY,
    processed_at DATETIME,
    processed_by VARCHAR(100)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;";

$q2 = "CREATE TABLE IF NOT EXISTS prm_kasir_paket_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_paket_kasir VARCHAR(255) NOT NULL,
    id_paket_master INT NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;";

// Gunakan DELETE dan SET FOREIGN_KEY_CHECKS
$q3 = "SET FOREIGN_KEY_CHECKS = 0;";
$q4 = "TRUNCATE TABLE prm_catatan;";
$q5 = "TRUNCATE TABLE prm_kapasitas;";
$q6 = "TRUNCATE TABLE prm_kasir_processed;";
$q7 = "SET FOREIGN_KEY_CHECKS = 1;";

// Insert Dummy Mapping jika belum ada
$q8 = "INSERT IGNORE INTO prm_kasir_paket_mapping (nama_paket_kasir, id_paket_master) VALUES 
('EM - LATIHAN MANUAL', 1),
('EXCB - LATIHAN BERAT', 2),
('MSMYO - MYOMED', 3)";

try {
    $db->exec($q1);
    $db->exec($q2);
    $db->exec($q3);
    $db->exec($q4);
    $db->exec($q5);
    $db->exec($q6);
    $db->exec($q7);
    $db->exec($q8);
    echo "Tabel berhasil dibuat dan database telah dikosongkan/direset!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

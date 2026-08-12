<?php
require_once __DIR__ . '/config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection(); // asumsikan koneksi ke `askes`

    $sql = "CREATE TABLE IF NOT EXISTS prm_kasir_processed (
        id_transaksi INT(11) PRIMARY KEY,
        processed_at DATETIME NOT NULL,
        processed_by VARCHAR(50) DEFAULT 'System'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $db->exec($sql);
    echo "Tabel prm_kasir_processed berhasil dibuat atau sudah ada.\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

// Tambahkan HANSAPLAST ke mapping (dipetakan ke ID 1 sebagai dummy sementara)
$q1 = "INSERT IGNORE INTO prm_kasir_paket_mapping (nama_paket_kasir, id_paket_master) VALUES ('HANSAPLAST', 1)";

try {
    $db->exec($q1);
    echo "Berhasil memetakan HANSAPLAST!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

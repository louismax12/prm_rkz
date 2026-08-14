<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

try {
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $db->exec("TRUNCATE TABLE prm_catatan;");
    $db->exec("TRUNCATE TABLE prm_kapasitas;");
    $db->exec("TRUNCATE TABLE prm_kasir_processed;");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "Pembersihan (reset) Menu Pasien berhasil!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

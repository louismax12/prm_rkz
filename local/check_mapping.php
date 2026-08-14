<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

$q1 = "INSERT IGNORE INTO prm_kasir_paket_mapping (nama_paket_kasir, id_paket_master) VALUES ('MYELOGRAPHY', 1)";

try {
    $db->exec($q1);
    
    $stmt = $db->query("SELECT * FROM prm_kasir_paket_mapping WHERE nama_paket_kasir LIKE '%MYELOGRAPHY%'");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

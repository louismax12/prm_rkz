<?php
require 'local/api/config/database.php';
$db = new Database();
$conn = $db->getConnection();

$stmt1 = $conn->query("SHOW TABLES LIKE 'prm_kasir_processed'");
echo "Current DB prm_kasir_processed: " . count($stmt1->fetchAll()) . "\n";

$stmt2 = $conn->query("SHOW TABLES FROM dbold LIKE 'prm_kasir_processed'");
echo "dbold prm_kasir_processed: " . count($stmt2->fetchAll()) . "\n";

$stmt3 = $conn->query("SELECT id_transaksi FROM prm_kasir_processed LIMIT 1");
if ($stmt3) {
    print_r($stmt3->fetchAll());
}

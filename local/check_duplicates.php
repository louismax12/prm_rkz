<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

try {
    // Cek apakah ada duplikasi kode di m_tindakan2026
    $stmt = $db->query("SELECT kode, COUNT(*) as c FROM dbold.m_tindakan2026 GROUP BY kode HAVING c > 1");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

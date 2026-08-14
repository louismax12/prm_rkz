<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

try {
    $stmt = $db->query("SELECT nama FROM dbold.m_tindakan2026 WHERE nama LIKE '%MYELOGRAPHY%'");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

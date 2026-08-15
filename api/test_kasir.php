<?php
require 'config/database.php';
$db = new Database();
$conn = $db->getConnection();
$query = "SELECT * FROM dbold.m_tindakan2026 WHERE nama LIKE '%PAKET HD%' LIMIT 10";
$stmt = $conn->query($query);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>

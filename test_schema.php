<?php
require 'local/api/config/database.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query("SHOW CREATE TABLE prm_kapasitas");
print_r($stmt->fetch(PDO::FETCH_ASSOC));

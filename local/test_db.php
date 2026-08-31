<?php
require 'local/api/config/database.php';
$db = new Database();
try {
    $conn = $db->getConnection('hrd');
    if ($conn) {
        $stmt = $conn->query('SELECT NIP, password FROM datadasar LIMIT 1');
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "DB Connected! Users found: " . count($res) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

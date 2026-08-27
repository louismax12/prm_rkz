<?php
require 'api/config/database.php';
$db = new Database();
$conn = $db->getConnection();
function print_schema($conn, $table) {
    echo "--- $table ---\n";
    $stmt = $conn->query("SHOW COLUMNS FROM $table");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    $stmt = $conn->query("SHOW INDEX FROM $table");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
print_schema($conn, 'dbold.admpacust');
print_schema($conn, 'dbold.poliumumupcust');
print_schema($conn, 'dbold.fisiosfjual');

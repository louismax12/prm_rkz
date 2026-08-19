<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

echo "Isi prm_kapasitas:\n";
$stmt = $db->query("SELECT * FROM prm_kapasitas");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>

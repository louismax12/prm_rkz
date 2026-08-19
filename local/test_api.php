<?php
include_once 'api/config/database.php';
include_once 'api/models/Kapasitas.php';

$database = new Database();
$db = $database->getConnection();
$kapasitas = new Kapasitas($db);

$stmt = $kapasitas->readByVisitDate('2026-08-16');
echo "Records found: " . $stmt->rowCount() . "\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>

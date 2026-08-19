<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

$q = "SELECT FCRRMV, FCRRMUNIT, FCRCUST, FCRJUMLAH, FCRNETTO, FCRNAMA FROM kasir_jual_h WHERE FCRNAMA LIKE '%TENG LE MING%' LIMIT 1";
$stmt = $db->query($q);
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>

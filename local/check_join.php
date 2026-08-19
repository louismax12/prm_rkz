<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

$q = "SELECT FCRNOKWIT, FCRID FROM kasir_jual_h WHERE FCRNAMA LIKE '%TENG LE MING%' LIMIT 1";
$stmt = $db->query($q);
print_r($stmt->fetch(PDO::FETCH_ASSOC));

$q2 = "SELECT FCRID, FCRNOKWIT FROM fisiosfjual WHERE FCRNAMA LIKE '%TENG LE MING%' LIMIT 1";
$stmt2 = $db->query($q2);
print_r($stmt2->fetch(PDO::FETCH_ASSOC));
?>

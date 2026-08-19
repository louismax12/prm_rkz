<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

$q = "SELECT FCRID, FCRCUST, FCRNAMA, FCRDOKTER FROM fisiosfjual WHERE FCRNAMA LIKE '%TENG LE MING%' LIMIT 1";
$stmt = $db->query($q);
print_r($stmt->fetch(PDO::FETCH_ASSOC));

$q2 = "SELECT f.ID, f.FCRCUST, f.FCRDOKTER, f.FCRNAMA FROM kasir_jual_h f WHERE f.FCRNAMA LIKE '%TENG LE MING%' LIMIT 1";
try {
    $stmt2 = $db->query($q2);
    print_r($stmt2->fetch(PDO::FETCH_ASSOC));
} catch(Exception $e) {}
?>

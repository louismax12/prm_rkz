<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

echo "Sample No. Register dari fisiosfjual (FCRID / FCRFH / FCRNOKWIT):\n";
$stmt = $db->query("SELECT FCRID, FCRFH, FCRNOKWIT FROM fisiosfjual ORDER BY FCRTANGGAL DESC LIMIT 3");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}

echo "\nSample No. Register dari kasir_jual_h (FCRNOKWIT / FCRID):\n";
try {
    $stmt2 = $db->query("SELECT FCRID, FCRNOKWIT FROM kasir_jual_h ORDER BY FCRTANGGAL DESC LIMIT 3");
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {}

echo "\nSample No. Register dari poliumumupcust (fnoreg):\n";
try {
    $stmt3 = $db->query("SELECT fnoreg FROM poliumumupcust ORDER BY fdate_in DESC LIMIT 3");
    while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {}
?>

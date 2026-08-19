<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection(); // dbold

echo "Tables matching kasir_jual%:\n";
$stmt = $db->query("SHOW TABLES LIKE 'kasir_jual%'");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo "  " . $row[0] . "\n";
}

echo "Tables matching poli%:\n";
$stmt = $db->query("SHOW TABLES LIKE 'poli%'");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo "  " . $row[0] . "\n";
}
?>

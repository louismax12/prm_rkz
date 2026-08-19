<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

echo "Check Tables in dbold:\n";
$stmt = $db->query("SHOW TABLES LIKE '%FCRRM%'");
while($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo $row[0] . "\n";
}

echo "\nCheck Columns in kasir_jual_h:\n";
$stmt2 = $db->query("DESCRIBE kasir_jual_h");
while($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    if(strpos($row['Field'], 'FCR') !== false) {
        echo $row['Field'] . "\n";
    }
}
?>

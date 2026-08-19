<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection('askes'); // use any connection

function describeTable($db, $dbname, $tablename) {
    echo "=== Table: $dbname.$tablename ===\n";
    $stmt = $db->query("DESCRIBE `$dbname`.`$tablename`");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    echo "\n";
}

describeTable($db, 'dbold', 'fisiosfjual');
describeTable($db, 'dbold', 'kasir_jual_h');
describeTable($db, 'dbold', 'poliumumupjual');
?>

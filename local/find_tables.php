<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection('askes'); // use any connection

$stmt = $db->query("SHOW DATABASES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $dbname = $row[0];
    echo "Checking database: $dbname\n";
    try {
        $stmt_tables = $db->query("SHOW TABLES FROM `$dbname` LIKE '%jual%'");
        while ($tableRow = $stmt_tables->fetch(PDO::FETCH_NUM)) {
            echo "  Found table: " . $tableRow[0] . "\n";
        }
    } catch(Exception $e) {
        // ignore
    }
}
?>

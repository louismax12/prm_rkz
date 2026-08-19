<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

$q = "DESCRIBE fisiosfjual";
$stmt = $db->query($q);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . "\n";
}

echo "\nData sample:\n";
$q2 = "SELECT FCRID, FCRNOKWIT, ID FROM fisiosfjual LIMIT 3";
$stmt2 = $db->query($q2);
while($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>

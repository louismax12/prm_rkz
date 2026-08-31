<?php
require 'local/api/config/database.php';
$db = new Database();
$conn = $db->getConnection();

$q = "SELECT ID, FCRRSP, FCRID FROM dbold.fisiosfjual WHERE FCRRSP = '12096491  ' OR FCRID = '12096491  ' OR ID = 12096491 OR ID = '12096491'";
$stmt = $conn->query($q);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

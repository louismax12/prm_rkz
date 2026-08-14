<?php
require 'local/api/config/database.php';

$database = new Database();
$db = $database->getConnection();

$result = $db->query("DESCRIBE prm_master_paket");
print_r($result->fetchAll(PDO::FETCH_ASSOC));
?>

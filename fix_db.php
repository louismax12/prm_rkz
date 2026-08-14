<?php
require 'local/api/config/database.php';

$database = new Database();
$db = $database->getConnection();

$db->query("ALTER TABLE prm_master_paket MODIFY COLUMN tipe_paket VARCHAR(50)");
echo "Table altered successfully\n";
?>

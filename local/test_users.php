<?php
$db = new PDO('mysql:host=192.168.2.12;port=3306;dbname=keuangan', 'anugrah', 'anugrah'); 
$stmt = $db->query('SELECT * FROM prm_users'); 
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>

<?php 
$db = new PDO('mysql:host=localhost;dbname=prm_rkz', 'root', '123456789');
$stmt = $db->query('DESCRIBE prm_kapasitas');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>

<?php
$db = new PDO('mysql:host=localhost;dbname=prm_rkz', 'root', '123456789');
$db->query("INSERT INTO prm_master_paket (nama, tipe_paket, total_sesi, masa_berlaku_hari) VALUES ('Paket Fisioterapi Intensif', 'Fisioterapi', 10, 30), ('Paket Pemulihan Stroke', 'Neurologi', 20, 60), ('Paket Terapi Wicara Dasar', 'Wicara', 5, 14)");
echo 'OK';
?>

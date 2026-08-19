<?php
include_once 'api/config/database.php';
$database = new Database();
$db = $database->getConnection();

$vdate = '2026-08-16';

$query = "SELECT FCRCUST, FCRNAMA FROM fisiosfjual WHERE DATE(FCRTANGGAL) = :vdate
          UNION
          SELECT FCRCUST, FCRNAMA FROM kasir_jual_h WHERE DATE(FCRTANGGAL) = :vdate
          UNION
          SELECT fnorm as FCRCUST, fnama as FCRNAMA FROM poliumumupcust WHERE DATE(fdate_in) = :vdate";

$stmt = $db->prepare($query);
$stmt->execute([':vdate' => $vdate]);
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "'" . $row['FCRCUST'] . "' => '" . $row['FCRNAMA'] . "'\n";
}
?>

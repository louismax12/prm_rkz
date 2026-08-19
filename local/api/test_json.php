<?php
$_GET['date'] = '2026-08-16';
include_once 'config/database.php';
include_once 'models/Kapasitas.php';
include_once 'models/Catatan.php';
include_once 'controllers/PasienController.php';

$controller = new PasienController();
ob_start();
$controller->getAll();
$output = ob_get_clean();
echo "JSON Output:\n";
echo $output;
?>

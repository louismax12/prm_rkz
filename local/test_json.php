<?php
$_GET['date'] = '2026-08-16';
include_once 'api/config/database.php';
include_once 'api/models/Kapasitas.php';
include_once 'api/controllers/PasienController.php';

$controller = new PasienController();
ob_start();
$controller->getAll();
$output = ob_get_clean();
echo "JSON Output:\n";
echo $output;
?>

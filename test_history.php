<?php
$_GET['page'] = 1;
require 'local/api/config/database.php';
require 'local/api/controllers/KasirController.php';
$c = new KasirController();
$c->getHistory();

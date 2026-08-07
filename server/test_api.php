<?php 
include_once 'api/helpers/JWT.php';
$payload = array("id" => 1, "username" => "admin", "role" => "admin", "exp" => time() + 3600);
$token = JWT::encode($payload);

$opts = [
    "http" => [
        "method" => "POST",
        "header" => "Content-Type: application/json\r\nAuthorization: Bearer " . $token . "\r\n",
        "content" => json_encode(["no_erm"=>"ERM001", "id_paket"=>12]),
        "ignore_errors" => true
    ]
];
$context = stream_context_create($opts);
$res = file_get_contents("http://localhost:8002/api/index.php/kasir?action=beli_paket", false, $context);
echo "8002 POST response: " . $res . "\n"; 
?>

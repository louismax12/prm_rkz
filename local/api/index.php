<?php
// header headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// simple router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

// mencari posisi 'index.php' di dalam array URI untuk menentukan letak endpoint
$endpoint = '';
$indexPos = array_search('index.php', $uri);
if ($indexPos !== false && isset($uri[$indexPos + 1])) {
    $endpoint = $uri[$indexPos + 1];
}

$action = isset($_GET['action']) ? $_GET['action'] : ''; // parameter tambahan untuk action

// --- Middleware JWT Sederhana ---
$userPayload = null;
if ($endpoint !== 'auth') { // auth tidak perlu token
    $authHeader = '';

    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $authHeader = $headers['authorization'];
        }
    }

    if (empty($authHeader)) {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
    }

    if(preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        include_once 'helpers/JWT.php';
        $token = $matches[1];
        $userPayload = JWT::decode($token);
        
        if(!$userPayload) {
            http_response_code(401);
            echo json_encode(array("message" => "Akses ditolak. Token tidak valid atau kadaluarsa."));
            exit;
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Akses ditolak. Token tidak ditemukan."));
        exit;
    }
}
// --------------------------------

if ($endpoint === 'auth') {
    include_once 'controllers/AuthController.php';
    $controller = new AuthController();
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if ($requestMethod == 'POST' && $action == 'login') {
        $controller->login();
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Bad Request"));
    }
} elseif ($endpoint === 'paket') {
    include_once 'controllers/PaketController.php';
    $controller = new PaketController();
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if ($requestMethod == 'GET') {
        $controller->readAll();
    } else {
        http_response_code(405);
        echo json_encode(array("message" => "Method Not Allowed"));
    }
} elseif ($endpoint === 'pasien') {
    include_once 'controllers/PasienController.php';
    $controller = new PasienController();
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if ($requestMethod == 'GET' && $action == 'kapasitas_aktif') {
        $controller->getKapasitasAktif();
    } elseif ($requestMethod == 'GET' && $action == 'riwayat_sesi') {
        $controller->getRiwayatSesi();
    } elseif ($requestMethod == 'POST' && $action == 'gunakan_sesi') {
        // Cek Role (Kasir dan Admin boleh memotong sesi)
        if(in_array($userPayload['role'], ['admin', 'erm', 'kasir'])) {
            $controller->gunakanSesi();
        } else {
            http_response_code(403);
            echo json_encode(array("message" => "Anda tidak memiliki hak akses untuk memotong sesi."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Bad Request or Action Not Found"));
    }
} elseif ($endpoint === 'kasir') {
    include_once 'controllers/KasirController.php';
    $controller = new KasirController();
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    if ($requestMethod == 'POST' && $action == 'beli_paket') {
        if(in_array($userPayload['role'], ['admin', 'kasir'])) {
            $controller->beliPaket();
        } else {
            http_response_code(403);
            echo json_encode(array("message" => "Anda tidak memiliki hak akses untuk modul kasir."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Bad Request or Action Not Found"));
    }
} elseif ($endpoint === 'master') {
    if(in_array($userPayload['role'], ['admin'])) {
        include_once 'controllers/MasterController.php';
        $controller = new MasterController();
        $controller->handleRequest();
    } else {
        http_response_code(403);
        echo json_encode(array("message" => "Anda tidak memiliki hak akses untuk modul master data."));
    }
} elseif ($endpoint === 'audit') {
    if(in_array($userPayload['role'], ['admin', 'manajemen'])) {
        include_once 'controllers/AuditController.php';
        $controller = new AuditController();
        $controller->handleRequest();
    } else {
        http_response_code(403);
        echo json_encode(array("message" => "Anda tidak memiliki hak akses untuk modul laporan & audit."));
    }
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Endpoint Not Found"));
}
?>

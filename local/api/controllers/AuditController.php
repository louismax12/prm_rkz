<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Catatan.php';

class AuditController {
    private $db;
    private $catatan;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection('keuangan');
        $this->catatan = new Catatan($this->db);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = isset($_GET['action']) ? $_GET['action'] : '';

        if ($method === 'GET') {
            if ($action === 'summary') {
                $this->getSummary();
            } elseif ($action === 'logs') {
                $this->getLogs();
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Aksi GET tidak valid."]);
            }
        } else {
            http_response_code(405);
            echo json_encode(["message" => "Metode tidak diizinkan."]);
        }
    }

    private function getSummary() {
        $stats = $this->catatan->getSummaryStats();
        http_response_code(200);
        echo json_encode($stats);
    }

    private function getLogs() {
        $stmt = $this->catatan->getAllHistory();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $logs_arr = array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $log_item = array(
                    "id" => $id,
                    "no_erm" => $no_erm,
                    "no_register_kunjungan" => $no_register_kunjungan,
                    "tanggal_paket" => $tanggal_paket,
                    "sesi_ke" => $sesi_ke,
                    "nama_tindakan" => $nama_tindakan ? $nama_tindakan : "-",
                    "nama_paket" => $nama_paket ? $nama_paket : "-"
                );
                array_push($logs_arr, $log_item);
            }
            
            http_response_code(200);
            echo json_encode($logs_arr);
        } else {
            http_response_code(200);
            echo json_encode([]);
        }
    }
}
?>

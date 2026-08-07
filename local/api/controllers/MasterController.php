<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Paket.php';

class MasterController {
    private $db;
    private $paket;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection('keuangan');
        $this->paket = new Paket($this->db);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = isset($_GET['action']) ? $_GET['action'] : '';

        if ($method === 'POST') {
            $data = json_decode(file_get_contents("php://input"));
            
            if ($action === 'add_paket') {
                $this->addPaket($data);
            } elseif ($action === 'update_paket') {
                $this->updatePaket($data);
            } elseif ($action === 'delete_paket') {
                $this->deletePaket($data);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Aksi POST tidak valid."]);
            }
        } else {
            http_response_code(405);
            echo json_encode(["message" => "Metode tidak diizinkan."]);
        }
    }

    private function addPaket($data) {
        if (!empty($data->nama) && !empty($data->tipe_paket) && !empty($data->total_sesi) && !empty($data->masa_berlaku_hari)) {
            $this->paket->nama = $data->nama;
            $this->paket->tipe_paket = $data->tipe_paket;
            $this->paket->total_sesi = $data->total_sesi;
            $this->paket->masa_berlaku_hari = $data->masa_berlaku_hari;

            if ($this->paket->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Paket berhasil ditambahkan."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Gagal menambahkan paket."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Data tidak lengkap."]);
        }
    }

    private function updatePaket($data) {
        if (!empty($data->id) && !empty($data->nama) && !empty($data->tipe_paket) && !empty($data->total_sesi) && !empty($data->masa_berlaku_hari)) {
            $this->paket->id = $data->id;
            $this->paket->nama = $data->nama;
            $this->paket->tipe_paket = $data->tipe_paket;
            $this->paket->total_sesi = $data->total_sesi;
            $this->paket->masa_berlaku_hari = $data->masa_berlaku_hari;

            if ($this->paket->update()) {
                http_response_code(200);
                echo json_encode(["message" => "Paket berhasil diupdate."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Gagal mengupdate paket."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Data tidak lengkap untuk update."]);
        }
    }

    private function deletePaket($data) {
        if (!empty($data->id)) {
            $this->paket->id = $data->id;
            
            if ($this->paket->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Paket berhasil dihapus."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Gagal menghapus paket. Paket mungkin masih digunakan."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID tidak diberikan."]);
        }
    }
}
?>

<?php
class PaketController {
    private $db;
    private $paket;

    public function __construct() {
        include_once 'config/database.php';
        include_once 'models/Paket.php';

        $database = new Database();
        $this->db = $database->getConnection();
        $this->paket = new Paket($this->db);
    }

    public function readAll() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 5;
        $offset = ($page - 1) * $limit;

        // Jika request via app.js ingin membaca semua tanpa batas (misal untuk dropdown)
        // Kita tangkap lewat parameter ?all=true
        if(isset($_GET['all']) && $_GET['all'] == 'true') {
            $stmt = $this->paket->read();
        } else {
            $stmt = $this->paket->readPaged($offset, $limit);
        }
        
        $num = $stmt->rowCount();

        $total_records = $this->paket->countAll();
        $total_pages = ceil($total_records / $limit);

        if($num > 0){
            $paket_arr = array();
            $paket_arr["records"] = array();
            $paket_arr["total_records"] = $total_records;
            $paket_arr["total_pages"] = $total_pages;
            $paket_arr["current_page"] = $page;

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                $paket_item = array(
                    "id" => $id,
                    "nama" => $nama,
                    "tipe_paket" => $tipe_paket,
                    "total_sesi" => $total_sesi,
                    "masa_berlaku_hari" => $masa_berlaku_hari
                );
                array_push($paket_arr["records"], $paket_item);
            }

            http_response_code(200);
            echo json_encode($paket_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Tidak ada paket yang ditemukan."));
        }
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
                echo json_encode(array("message" => "Aksi POST tidak valid."));
            }
        } else {
            http_response_code(405);
            echo json_encode(array("message" => "Metode tidak diizinkan."));
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
                echo json_encode(array("message" => "Paket berhasil ditambahkan."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Gagal menambahkan paket."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data tidak lengkap."));
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
                echo json_encode(array("message" => "Paket berhasil diupdate."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Gagal mengupdate paket."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data tidak lengkap untuk update."));
        }
    }

    private function deletePaket($data) {
        if (!empty($data->id)) {
            $this->paket->id = $data->id;
            
            if ($this->paket->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Paket berhasil dihapus."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Gagal menghapus paket. Paket mungkin masih digunakan."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID tidak diberikan."));
        }
    }
}
?>

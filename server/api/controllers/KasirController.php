<?php
class KasirController {
    private $db;
    private $kapasitas;
    private $paket;

    public function __construct() {
        include_once 'config/database.php';
        include_once 'models/Kapasitas.php';
        include_once 'models/Paket.php';

        $database = new Database();
        $this->db = $database->getConnection();
        $this->kapasitas = new Kapasitas($this->db);
        $this->paket = new Paket($this->db);
    }

    public function beliPaket() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->no_erm) && !empty($data->id_paket)) {
            
            // 1. Dapatkan detail paket untuk sisa dan expired
            $this->paket->id = $data->id_paket;
            if($this->paket->readOne()) {
                $this->kapasitas->no_erm = $data->no_erm;
                $this->kapasitas->id_paket = $data->id_paket;
                
                // Generate nomor register otomatis (contoh sederhana)
                $this->kapasitas->nomor_register = "REG-" . date("Ymd-His");
                
                $this->kapasitas->sisa = $this->paket->total_sesi;
                $this->kapasitas->status = 'AKTIF';
                
                $tanggal_beli = date("Y-m-d H:i:s");
                $this->kapasitas->tanggal_beli = $tanggal_beli;
                
                // Hitung expired
                $masa_berlaku = $this->paket->masa_berlaku_hari;
                $this->kapasitas->tanggal_expired = date('Y-m-d H:i:s', strtotime($tanggal_beli . ' + ' . $masa_berlaku . ' days'));

                if($this->kapasitas->create()) {
                    http_response_code(201);
                    echo json_encode(array("message" => "Paket berhasil dibeli dan aktif."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Gagal membeli paket. Kesalahan server."));
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Master paket tidak ditemukan."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data tidak lengkap."));
        }
    }
}
?>

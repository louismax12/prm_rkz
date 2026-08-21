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
}
?>

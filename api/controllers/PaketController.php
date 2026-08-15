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
        $stmt = $this->paket->read();
        $num = $stmt->rowCount();

        if($num > 0){
            $paket_arr = array();
            $paket_arr["records"] = array();

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

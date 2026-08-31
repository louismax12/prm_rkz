<?php
class TindakanController {
    private $db;
    private $tindakan;

    public function __construct() {
        include_once 'config/database.php';
        include_once 'models/Tindakan.php';

        $database = new Database();
        $this->db = $database->getConnection();
        $this->tindakan = new Tindakan($this->db);
    }

    public function readAll() {
        $stmt = $this->tindakan->read();
        $num = $stmt->rowCount();

        if($num > 0){
            $tindakan_arr = array();
            $tindakan_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                $tindakan_item = array(
                    "id" => $id,
                    "kode_tindakan" => $kode_tindakan,
                    "nama_tindakan" => $nama_tindakan
                );
                array_push($tindakan_arr["records"], $tindakan_item);
            }

            http_response_code(200);
            echo json_encode($tindakan_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Tidak ada tindakan yang ditemukan."));
        }
    }
}
?>

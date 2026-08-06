<?php
class Tindakan {
    private $conn;
    private $table_name = "prm_master_tindakan";

    public $id;
    public $kode_tindakan;
    public $nama_tindakan;

    public function __construct($db){
        $this->conn = $db;
    }

    // read all tindakan
    function read(){
        $query = "SELECT id, kode_tindakan, nama_tindakan FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>

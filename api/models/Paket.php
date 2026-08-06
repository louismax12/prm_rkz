<?php
class Paket {
    private $conn;
    private $table_name = "prm_master_paket";

    // object properties
    public $id;
    public $nama;
    public $tipe_paket;
    public $total_sesi;
    public $masa_berlaku_hari;

    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // read all paket
    function read(){
        $query = "SELECT id, nama, tipe_paket, total_sesi, masa_berlaku_hari FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>

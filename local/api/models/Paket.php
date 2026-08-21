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

    // read all paket with pagination
    function readPaged($offset, $limit){
        $query = "SELECT id, nama, tipe_paket, total_sesi, masa_berlaku_hari FROM " . $this->table_name . " ORDER BY id DESC LIMIT ?, ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $offset, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // count all records
    function countAll(){
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_rows'];
    }

    // read one paket by id
    function readOne(){
        $query = "SELECT id, nama, tipe_paket, total_sesi, masa_berlaku_hari FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->nama = $row['nama'];
            $this->tipe_paket = $row['tipe_paket'];
            $this->total_sesi = $row['total_sesi'];
            $this->masa_berlaku_hari = $row['masa_berlaku_hari'];
            return true;
        }
        return false;
    }

    // create paket
    function create(){
        $query = "INSERT INTO " . $this->table_name . " SET nama=:nama, tipe_paket=:tipe_paket, total_sesi=:total_sesi, masa_berlaku_hari=:masa_berlaku_hari";
        $stmt = $this->conn->prepare($query);

        $this->nama=htmlspecialchars(strip_tags($this->nama));
        $this->tipe_paket=htmlspecialchars(strip_tags($this->tipe_paket));
        $this->total_sesi=htmlspecialchars(strip_tags($this->total_sesi));
        $this->masa_berlaku_hari=htmlspecialchars(strip_tags($this->masa_berlaku_hari));

        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":tipe_paket", $this->tipe_paket);
        $stmt->bindParam(":total_sesi", $this->total_sesi);
        $stmt->bindParam(":masa_berlaku_hari", $this->masa_berlaku_hari);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // update paket
    function update(){
        $query = "UPDATE " . $this->table_name . " SET nama=:nama, tipe_paket=:tipe_paket, total_sesi=:total_sesi, masa_berlaku_hari=:masa_berlaku_hari WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->nama=htmlspecialchars(strip_tags($this->nama));
        $this->tipe_paket=htmlspecialchars(strip_tags($this->tipe_paket));
        $this->total_sesi=htmlspecialchars(strip_tags($this->total_sesi));
        $this->masa_berlaku_hari=htmlspecialchars(strip_tags($this->masa_berlaku_hari));
        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":tipe_paket", $this->tipe_paket);
        $stmt->bindParam(":total_sesi", $this->total_sesi);
        $stmt->bindParam(":masa_berlaku_hari", $this->masa_berlaku_hari);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // delete paket
    function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()){
            return true;
        }
        return false;
    }
}
?>

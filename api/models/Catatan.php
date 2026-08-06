<?php
class Catatan {
    private $conn;
    private $table_name = "prm_catatan";

    public $id;
    public $id_kapasitas;
    public $id_tindakan;
    public $no_erm;
    public $no_register_kunjungan;
    public $tanggal_paket;
    public $sesi_ke;

    public function __construct($db){
        $this->conn = $db;
    }

    // Buat catatan penggunaan paket (sesi)
    function create(){
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id_kapasitas=:id_kapasitas, id_tindakan=:id_tindakan, 
                      no_erm=:no_erm, no_register_kunjungan=:no_register_kunjungan, 
                      tanggal_paket=:tanggal_paket, sesi_ke=:sesi_ke";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->id_kapasitas = htmlspecialchars(strip_tags($this->id_kapasitas));
        if($this->id_tindakan != null) {
            $this->id_tindakan = htmlspecialchars(strip_tags($this->id_tindakan));
        }
        $this->no_erm = htmlspecialchars(strip_tags($this->no_erm));
        $this->no_register_kunjungan = htmlspecialchars(strip_tags($this->no_register_kunjungan));
        $this->sesi_ke = htmlspecialchars(strip_tags($this->sesi_ke));

        // bind values
        $stmt->bindParam(":id_kapasitas", $this->id_kapasitas);
        $stmt->bindParam(":id_tindakan", $this->id_tindakan);
        $stmt->bindParam(":no_erm", $this->no_erm);
        $stmt->bindParam(":no_register_kunjungan", $this->no_register_kunjungan);
        $stmt->bindParam(":tanggal_paket", $this->tanggal_paket);
        $stmt->bindParam(":sesi_ke", $this->sesi_ke);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Ambil histori catatan berdasarkan no_erm
    function getHistoryByErm($no_erm){
        $query = "SELECT c.*, t.nama_tindakan 
                  FROM " . $this->table_name . " c
                  LEFT JOIN prm_master_tindakan t ON c.id_tindakan = t.id
                  WHERE c.no_erm = ? ORDER BY c.tanggal_paket DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $no_erm);
        $stmt->execute();
        
        return $stmt;
    }
}
?>

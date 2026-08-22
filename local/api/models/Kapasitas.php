<?php
class Kapasitas {
    private $conn;
    private $table_name = "prm_kapasitas";

    public $id;
    public $no_erm;
    public $nomor_register;
    public $id_paket;
    public $sisa;
    public $tanggal_beli;
    public $tanggal_expired;
    public $status;

    public function __construct($db){
        $this->conn = $db;
    }

    // read active kapasitas by date (sebelumnya by visit date, diubah menjadi by tanggal_beli sesuai request)
    function readByVisitDate($date){
        $query = "SELECT k.id, k.no_erm, 
                         COALESCE(
                             (SELECT FCRNAMA FROM dbold.fisiosfjual WHERE FCRCUST = k.no_erm ORDER BY FCRDATE DESC LIMIT 1),
                             (SELECT fname FROM dbold.poliumumupcust WHERE idcust = k.no_erm ORDER BY fdate_in DESC LIMIT 1),
                             'Tidak Diketahui'
                         ) as nama_pasien, 
                         k.id_paket, k.tanggal_beli, k.sisa, k.status, CONCAT_WS(' ', NULLIF(p.kode_paket, ''), p.nama) as nama_paket, p.total_sesi 
                  FROM " . $this->table_name . " k 
                  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
                  WHERE k.status IN ('aktif', 'AKTIF', 'habis', 'HABIS') AND DATE(k.tanggal_beli) = :vdate
                  ORDER BY k.tanggal_beli DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':vdate', $date);
        $stmt->execute();
        return $stmt;
    }

    function readByVisitDatePaged($date, $offset, $limit){
        $query = "SELECT k.id, k.no_erm, 
                         COALESCE(
                             (SELECT FCRNAMA FROM dbold.fisiosfjual WHERE FCRCUST = k.no_erm ORDER BY FCRDATE DESC LIMIT 1),
                             (SELECT fname FROM dbold.poliumumupcust WHERE idcust = k.no_erm ORDER BY fdate_in DESC LIMIT 1),
                             'Tidak Diketahui'
                         ) as nama_pasien, 
                         k.id_paket, k.tanggal_beli, k.sisa, k.status, CONCAT_WS(' ', NULLIF(p.kode_paket, ''), p.nama) as nama_paket, p.total_sesi 
                  FROM " . $this->table_name . " k 
                  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
                  WHERE k.status IN ('aktif', 'AKTIF', 'habis', 'HABIS') AND DATE(k.tanggal_beli) = :vdate
                  ORDER BY k.tanggal_beli DESC LIMIT :offset, :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':vdate', $date);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    function countByVisitDate($date){
        $query = "SELECT COUNT(*) as total_rows 
                  FROM " . $this->table_name . " k 
                  WHERE k.status IN ('aktif', 'AKTIF', 'habis', 'HABIS') AND DATE(k.tanggal_beli) = :vdate";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':vdate', $date);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_rows'];
    }

    // Beli paket baru (create kapasitas)
    function create(){
        $query = "INSERT INTO " . $this->table_name . " 
                  SET no_erm=:no_erm, nomor_register=:nomor_register, id_paket=:id_paket, 
                      sisa=:sisa, tanggal_beli=:tanggal_beli, tanggal_expired=:tanggal_expired, status=:status";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->no_erm = htmlspecialchars(strip_tags($this->no_erm));
        $this->nomor_register = htmlspecialchars(strip_tags($this->nomor_register));
        $this->id_paket = htmlspecialchars(strip_tags($this->id_paket));
        $this->sisa = htmlspecialchars(strip_tags($this->sisa));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // bind values
        $stmt->bindParam(":no_erm", $this->no_erm);
        $stmt->bindParam(":nomor_register", $this->nomor_register);
        $stmt->bindParam(":id_paket", $this->id_paket);
        $stmt->bindParam(":sisa", $this->sisa);
        $stmt->bindParam(":tanggal_beli", $this->tanggal_beli);
        $stmt->bindParam(":tanggal_expired", $this->tanggal_expired);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Ambil kapasitas aktif pasien berdasarkan no_erm
    function getActiveByErm($no_erm){
        $query = "SELECT k.*, CONCAT_WS(' ', NULLIF(p.kode_paket, ''), p.nama) as nama_paket, p.total_sesi 
                  FROM " . $this->table_name . " k
                  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
                  WHERE k.no_erm = ? AND k.status = 'AKTIF' AND k.sisa > 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $no_erm);
        $stmt->execute();
        
        return $stmt;
    }

    // Ambil seluruh kapasitas (untuk Menu Pasien)
    function getAll(){
        $query = "SELECT k.*, CONCAT_WS(' ', NULLIF(p.kode_paket, ''), p.nama) as nama_paket, p.total_sesi,
                         (SELECT FCRNAMA FROM dbold.fisiosfjual WHERE FCRDOKTER = k.no_erm LIMIT 1) as nama_pasien
                  FROM " . $this->table_name . " k
                  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
                  ORDER BY k.tanggal_beli DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Kurangi sisa sesi
    function updateSisa(){
        $query = "UPDATE " . $this->table_name . " SET sisa = :sisa, status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':sisa', $this->sisa);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()){
            return true;
        }
        return false;
    }
}
?>

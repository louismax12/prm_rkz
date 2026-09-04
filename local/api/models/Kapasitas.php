<?php
class Kapasitas {
    private $conn;
    private $table_name = "prm_kapasitas";

    public $id;
    public $nik;
    public $noreg;
    public $nama;
    public $id_paket;
    public $sisa;
    public $tanggal_beli;
    public $tanggal_expired;
    public $status;
    public $last_query = "";

    public function __construct($db){
        $this->conn = $db;
    }

    // read active kapasitas by date
    function readByVisitDate($date){
        $query = "SELECT k.id, k.nik, k.noreg, 
                         k.nama as nama_pasien, 
                         k.id_paket, k.tanggal_beli, k.sisa, k.status, CONCAT_WS(' ', NULLIF(p.kode_paket, ''), p.nama) as nama_paket, p.total_sesi 
                  FROM " . $this->table_name . " k 
                  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
                  WHERE k.status IN ('aktif', 'AKTIF', 'habis', 'HABIS') 
                  AND k.tanggal_beli >= :vdate AND k.tanggal_beli < DATE_ADD(:vdate, INTERVAL 1 DAY)
                  ORDER BY k.tanggal_beli DESC";
        $this->last_query = $query;
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':vdate', $date);
        $stmt->execute();
        return $stmt;
    }

    function searchAllKapasitas($term){
        $termQuery = "%{$term}%";
        $query = "SELECT k.id, k.nik, k.noreg, 
                         k.nama as nama_pasien, 
                         k.id_paket, k.tanggal_beli, k.sisa, k.status, CONCAT_WS(' ', NULLIF(p.kode_paket, ''), p.nama) as nama_paket, p.total_sesi 
                  FROM " . $this->table_name . " k 
                  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
                  WHERE k.status IN ('aktif', 'AKTIF', 'habis', 'HABIS') 
                  AND (k.nik LIKE :term OR k.nama LIKE :term OR p.nama LIKE :term OR p.kode_paket LIKE :term)
                  ORDER BY k.tanggal_beli DESC LIMIT 100";
        $this->last_query = $query;
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':term', $termQuery);
        $stmt->execute();
        return $stmt;
    }

    function readByVisitDatePaged($date, $offset, $limit){
        $query = "SELECT k.id, k.nik, k.noreg, 
                         k.nama as nama_pasien, 
                         k.id_paket, k.tanggal_beli, k.sisa, k.status, CONCAT_WS(' ', NULLIF(p.kode_paket, ''), p.nama) as nama_paket, p.total_sesi 
                  FROM " . $this->table_name . " k 
                  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
                  WHERE k.status IN ('aktif', 'AKTIF', 'habis', 'HABIS') 
                  AND k.tanggal_beli >= :vdate AND k.tanggal_beli < DATE_ADD(:vdate, INTERVAL 1 DAY)
                  ORDER BY k.tanggal_beli DESC LIMIT :offset, :limit";
        $this->last_query = $query;
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
                  WHERE k.status IN ('aktif', 'AKTIF', 'habis', 'HABIS') 
                  AND k.tanggal_beli >= :vdate AND k.tanggal_beli < DATE_ADD(:vdate, INTERVAL 1 DAY)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':vdate', $date);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_rows'];
    }

    // Beli paket baru (create kapasitas)
    function create(){
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nik=:nik, noreg=:noreg, nama=:nama, id_paket=:id_paket, 
                      sisa=:sisa, tanggal_beli=:tanggal_beli, tanggal_expired=:tanggal_expired, status=:status";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->nik = trim(htmlspecialchars(strip_tags($this->nik)));
        $this->noreg = trim(htmlspecialchars(strip_tags($this->noreg)));
        $this->nama = trim(htmlspecialchars(strip_tags($this->nama)));
        $this->id_paket = trim(htmlspecialchars(strip_tags($this->id_paket)));
        $this->sisa = trim(htmlspecialchars(strip_tags($this->sisa)));
        $this->status = trim(htmlspecialchars(strip_tags($this->status)));

        // bind values
        $stmt->bindParam(":nik", $this->nik);
        $stmt->bindParam(":noreg", $this->noreg);
        $stmt->bindParam(":nama", $this->nama);
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

    // Ambil kapasitas aktif pasien berdasarkan nik
    function getActiveByErm($nik){
        $query = "SELECT k.*, CONCAT_WS(' ', NULLIF(p.kode_paket, ''), p.nama) as nama_paket, p.total_sesi 
                  FROM " . $this->table_name . " k
                  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
                  WHERE k.nik = ? AND k.status = 'AKTIF' AND k.sisa > 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $nik);
        $stmt->execute();
        
        return $stmt;
    }

    // Ambil seluruh kapasitas (untuk Menu Pasien)
    function getAll(){
        $query = "SELECT k.*, CONCAT_WS(' ', NULLIF(p.kode_paket, ''), p.nama) as nama_paket, p.total_sesi,
                         k.nama as nama_pasien
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

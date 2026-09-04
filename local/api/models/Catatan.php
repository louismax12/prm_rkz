<?php
class Catatan {
    private $conn;
    private $table_name = "prm_catatan";

    public $id;
    public $id_kapasitas;
    public $id_tindakan;
    public $nik;
    public $noreg;
    public $tanggal_paket;
    public $sesi_ke;
    public $last_query = "";

    public function __construct($db){
        $this->conn = $db;
    }

    // Buat catatan penggunaan paket (sesi)
    function create(){
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id_kapasitas=:id_kapasitas, id_tindakan=:id_tindakan, 
                      noreg=:nik, no_register_kunjungan=:noreg, 
                      tanggal_paket=:tanggal_paket, sesi_ke=:sesi_ke";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->id_kapasitas = trim(htmlspecialchars(strip_tags($this->id_kapasitas)));
        if($this->id_tindakan != null) {
            $this->id_tindakan = trim(htmlspecialchars(strip_tags($this->id_tindakan)));
        }
        $this->nik = trim(htmlspecialchars(strip_tags($this->nik)));
        $this->noreg = trim(htmlspecialchars(strip_tags($this->noreg)));
        $this->sesi_ke = trim(htmlspecialchars(strip_tags($this->sesi_ke)));

        // bind values
        $stmt->bindParam(":id_kapasitas", $this->id_kapasitas);
        $stmt->bindParam(":id_tindakan", $this->id_tindakan);
        $stmt->bindParam(":nik", $this->nik);
        $stmt->bindParam(":noreg", $this->noreg);
        $stmt->bindParam(":tanggal_paket", $this->tanggal_paket);
        $stmt->bindParam(":sesi_ke", $this->sesi_ke);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Ambil catatan berdasarkan ID
    function getById(){
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Hapus catatan (Batal Sesi)
    function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Ambil histori catatan berdasarkan nik
    function getHistoryByErm($nik){
        $query = "SELECT c.*, p.nama as nama_paket 
                  FROM " . $this->table_name . " c
                                    LEFT JOIN prm_kapasitas k ON c.id_kapasitas = k.id
                  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
                  WHERE c.noreg = ? ORDER BY c.tanggal_paket DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $nik);
        $stmt->execute();
        
        return $stmt;
    }

    // Ambil histori catatan berdasarkan id_kapasitas
    function getByKapasitas($id_kapasitas){
        $query = "SELECT c.*, p.nama as nama_paket 
                  FROM " . $this->table_name . " c
                                    LEFT JOIN prm_kapasitas k ON c.id_kapasitas = k.id
                  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
                  WHERE c.id_kapasitas = ? ORDER BY c.tanggal_paket DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_kapasitas);
        $stmt->execute();
        
        return $stmt;
    }

    // Ambil semua histori untuk Laporan & Audit
    function getAllHistory(){
        $query = "SELECT c.*, p.nama as nama_paket
                  FROM " . $this->table_name . " c
                                    LEFT JOIN prm_kapasitas k ON c.id_kapasitas = k.id
                  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
                  ORDER BY c.tanggal_paket DESC LIMIT 100";
        $this->last_query = $query;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    function getAllHistoryPaged($offset, $limit){
        $query = "SELECT c.*, p.nama as nama_paket
                  FROM " . $this->table_name . " c
                                    LEFT JOIN prm_kapasitas k ON c.id_kapasitas = k.id
                  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
                  ORDER BY c.tanggal_paket DESC LIMIT ?, ?";
        $this->last_query = $query;
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $offset, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    function countAllHistory(){
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total_rows'];
    }

    // Ambil statistik ringkasan
    function getSummaryStats(){
        $stats = array(
            "total_pasien_aktif" => 0,
            "sesi_hari_ini" => 0,
            "sisa_sesi_total" => 0
        );

        // Pasien Aktif (Distinct NIK yang punya kapasitas sisa > 0)
        $q1 = "SELECT COUNT(DISTINCT nik) as count FROM prm_kapasitas WHERE sisa > 0 AND status = 'AKTIF'";
        $stmt1 = $this->conn->prepare($q1);
        $stmt1->execute();
        if($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
            $stats['total_pasien_aktif'] = (int)$row['count'];
        }

        // Sesi hari ini
        $q2 = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE DATE(tanggal_paket) = CURDATE()";
        $stmt2 = $this->conn->prepare($q2);
        $stmt2->execute();
        if($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $stats['sesi_hari_ini'] = (int)$row['count'];
        }

        // Total Sisa Sesi Keseluruhan
        $q3 = "SELECT SUM(sisa) as sum_sesi FROM prm_kapasitas WHERE status = 'AKTIF'";
        $stmt3 = $this->conn->prepare($q3);
        $stmt3->execute();
        if($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
            $stats['sisa_sesi_total'] = (int)$row['sum_sesi'];
        }

        return $stats;
    }
}
?>

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

    // Ambil semua histori untuk Laporan & Audit
    function getAllHistory(){
        $query = "SELECT c.*, t.nama_tindakan, p.nama as nama_paket
                  FROM " . $this->table_name . " c
                  LEFT JOIN prm_master_tindakan t ON c.id_tindakan = t.id
                  LEFT JOIN prm_kapasitas k ON c.id_kapasitas = k.id
                  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
                  ORDER BY c.tanggal_paket DESC LIMIT 100";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Ambil statistik ringkasan
    function getSummaryStats(){
        $stats = array(
            "total_pasien_aktif" => 0,
            "sesi_hari_ini" => 0,
            "sisa_sesi_total" => 0
        );

        // Pasien Aktif (Distinct ERM yang punya kapasitas sisa_sesi > 0)
        $q1 = "SELECT COUNT(DISTINCT no_erm) as count FROM prm_kapasitas WHERE sisa_sesi > 0 AND status = 'Aktif'";
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
        $q3 = "SELECT SUM(sisa_sesi) as sum_sesi FROM prm_kapasitas WHERE status = 'Aktif'";
        $stmt3 = $this->conn->prepare($q3);
        $stmt3->execute();
        if($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
            $stats['sisa_sesi_total'] = (int)$row['sum_sesi'];
        }

        return $stats;
    }
}
?>

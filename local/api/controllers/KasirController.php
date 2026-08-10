<?php
class KasirController {
    private $db;

    public function __construct() {
        include_once 'config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getHistory() {
        $kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
        $nama = isset($_GET['nama']) ? $_GET['nama'] : '';
        $asaltabel = isset($_GET['asaltabel']) ? $_GET['asaltabel'] : '';
        $fcrtambah = isset($_GET['fcrtambah']) ? $_GET['fcrtambah'] : '';

        $query = "SELECT 
                    f.FCRCUST as no_register, 
                    f.FCRDOKTER as no_erm,
                    f.FCRNAMA as nama_pasien,
                    f.FCRDATE as tanggal_transaksi,
                    f.FCRBARANG as kode_paket,
                    t.nama as nama_paket,
                    f.FCRJUMLAH as total_biaya
                  FROM dbold.fisiosfjual f
                  JOIN dbold.m_tindakan2026 t ON f.FCRBARANG = t.kode
                  WHERE 1=1";
        
        $params = array();

        if ($fcrtambah !== '') {
            $query .= " AND f.FCRTAMBAH = :fcrtambah";
            $params[':fcrtambah'] = $fcrtambah;
        }
        if ($asaltabel !== '') {
            $query .= " AND t.asaltabel = :asaltabel";
            $params[':asaltabel'] = $asaltabel;
        }
        if ($kategori !== '') {
            $query .= " AND t.kategori LIKE :kategori";
            $params[':kategori'] = '%' . $kategori . '%';
        }
        if ($nama !== '') {
            $query .= " AND t.nama LIKE :nama";
            $params[':nama'] = '%' . $nama . '%';
        }

        $query .= " ORDER BY f.FCRDATE DESC, f.ID DESC LIMIT 100";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(array("records" => $records));
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(array("message" => "Database error: " . $e->getMessage()));
        }
    }
}
?>

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
        $namaDrop = isset($_GET['nama_drop']) ? $_GET['nama_drop'] : '';
        $namaText = isset($_GET['nama_text']) ? $_GET['nama_text'] : '';

        $query = "SELECT 
                    f.ID as id_transaksi,
                    f.FCRCUST as no_register, 
                    f.FCRDOKTER as no_erm,
                    f.FCRNAMA as nama_pasien,
                    f.FCRDATE as tanggal_transaksi,
                    f.FCRBARANG as kode_paket,
                    t.nama as nama_paket,
                    f.FCRJUMLAH as total_biaya,
                    IF(p.id_transaksi IS NOT NULL, 1, 0) as is_processed
                  FROM dbold.fisiosfjual f
                  JOIN dbold.m_tindakan2026 t ON f.FCRBARANG = t.kode
                  LEFT JOIN prm_kasir_processed p ON p.id_transaksi = f.ID
                  WHERE t.asaltabel = 'SFMASBIA' AND f.FCRTAMBAH = 'T'";
        
        $params = array();

        if ($namaDrop !== '') {
            $query .= " AND t.nama LIKE :nama_drop";
            $params[':nama_drop'] = '%' . $namaDrop . '%';
        }
        
        if ($namaText !== '') {
            $query .= " AND t.nama LIKE :nama_text";
            $params[':nama_text'] = '%' . $namaText . '%';
        }

        $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
        if ($statusFilter === '1') {
            $query .= " AND p.id_transaksi IS NOT NULL";
        } else if ($statusFilter === '0') {
            $query .= " AND p.id_transaksi IS NULL";
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

    public function markProcessed() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['ids']) || !is_array($data['ids'])) {
            http_response_code(400);
            echo json_encode(array("message" => "Data IDs tidak valid."));
            return;
        }

        $ids = $data['ids'];
        $processedBy = "Admin Kasir"; // Bisa disesuaikan dengan data session user
        $processedAt = date('Y-m-d H:i:s');
        $successCount = 0;

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("INSERT IGNORE INTO prm_kasir_processed (id_transaksi, processed_at, processed_by) VALUES (:id, :at, :by)");
            
            foreach ($ids as $id) {
                $stmt->execute([
                    ':id' => $id,
                    ':at' => $processedAt,
                    ':by' => $processedBy
                ]);
                $successCount++;
            }
            $this->db->commit();

            http_response_code(200);
            echo json_encode(array("message" => "$successCount transaksi berhasil disimpan.", "success" => true));
        } catch(PDOException $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(array("message" => "Database error: " . $e->getMessage(), "success" => false));
        }
    }
}
?>

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

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $baseSelect = "SELECT 
                    f.FCRRSP as id_transaksi,
                    f.FCRCUST as no_register, 
                    kh.FCRRMV as no_erm,
                    f.FCRNAMA as nama_pasien,
                    f.FCRDATE as tanggal_transaksi,
                    f.FCRBARANG as kode_paket,
                    t.nama as nama_paket,
                    f.FCRJUMLAH as total_biaya,
                    IF(p.id_transaksi IS NOT NULL, 1, 0) as is_processed ";
                    
        $countSelect = "SELECT COUNT(DISTINCT f.ID) as total ";

        $baseFromWhere = "FROM dbold.fisiosfjual f
                  JOIN dbold.m_tindakan2026 t ON f.FCRBARANG = t.kode
                  LEFT JOIN dbold.kasir_jual_h kh ON f.FCRRSP = kh.FCRRSP
                  LEFT JOIN prm_kasir_processed p ON p.id_transaksi = f.ID
                  WHERE t.asaltabel = 'SFMASBIA' AND f.FCRTAMBAH = 'T'";
        
        $params = array();

        if ($namaDrop !== '') {
            $baseFromWhere .= " AND t.nama LIKE :nama_drop";
            $params[':nama_drop'] = '%' . $namaDrop . '%';
        }
        
        if ($namaText !== '') {
            $baseFromWhere .= " AND t.nama LIKE :nama_text";
            $params[':nama_text'] = '%' . $namaText . '%';
        }

        $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
        if ($statusFilter === '1') {
            $baseFromWhere .= " AND p.id_transaksi IS NOT NULL";
        } else if ($statusFilter === '0') {
            $baseFromWhere .= " AND p.id_transaksi IS NULL";
        }

        $tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

        if ($tanggal !== '') {
            if (strlen($tanggal) === 7) { // YYYY-MM format
                $baseFromWhere .= " AND DATE_FORMAT(f.FCRDATE, '%Y-%m') = :tanggal";
            } else {
                $baseFromWhere .= " AND DATE(f.FCRDATE) = :tanggal";
            }
            $params[':tanggal'] = $tanggal;
        }

        $countQuery = $countSelect . $baseFromWhere;
        $query = $baseSelect . $baseFromWhere . " GROUP BY f.ID ORDER BY f.FCRDATE DESC, f.ID DESC LIMIT :limit OFFSET :offset";

        try {
            // Get Total Count
            $stmtCount = $this->db->prepare($countQuery);
            $stmtCount->execute($params);
            $totalRow = $stmtCount->fetch(PDO::FETCH_ASSOC);
            $totalRecords = $totalRow['total'];
            $totalPages = ceil($totalRecords / $limit);

            // Get Records
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(array(
                "records" => $records,
                "total_records" => $totalRecords,
                "total_pages" => $totalPages,
                "current_page" => $page
            ));
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

            $inQuery = implode(',', array_fill(0, count($ids), '?'));
            $qInfo = "SELECT 
                        f.ID as id_transaksi,
                        f.FCRID as nomor_register, 
                        f.FCRCUST as no_erm,
                        f.FCRDATE as tanggal_beli,
                        t.nama as nama_paket_kasir
                      FROM dbold.fisiosfjual f
                      JOIN dbold.m_tindakan2026 t ON f.FCRBARANG = t.kode
                      WHERE f.ID IN ($inQuery) AND t.asaltabel = 'SFMASBIA'
                      GROUP BY f.ID";
            $stmtInfo = $this->db->prepare($qInfo);
            $stmtInfo->execute($ids);
            $transaksiList = $stmtInfo->fetchAll(PDO::FETCH_ASSOC);

            // LOGGING
            file_put_contents('kasir_debug.log', "Received IDs: " . json_encode($ids) . "\nTransaksiList count: " . count($transaksiList) . "\n", FILE_APPEND);

            $stmtInsertProcessed = $this->db->prepare("INSERT IGNORE INTO prm_kasir_processed (id_transaksi, processed_at, processed_by) VALUES (:id, :at, :by)");
            $stmtInsertKapasitas = $this->db->prepare("INSERT INTO prm_kapasitas (no_erm, nomor_register, id_paket, sisa, tanggal_beli, tanggal_expired, status) VALUES (:no_erm, :nomor_register, :id_paket, :sisa, :tanggal_beli, :tanggal_expired, :status)");
            
            foreach ($transaksiList as $transaksi) {
                // Cari mapping
                $qMap = "SELECT m.id_paket_master, mp.total_sesi, mp.masa_berlaku_hari 
                         FROM prm_kasir_paket_mapping m 
                         JOIN prm_master_paket mp ON m.id_paket_master = mp.id 
                         WHERE m.nama_paket_kasir = ?";
                $stmtMap = $this->db->prepare($qMap);
                $stmtMap->execute([$transaksi['nama_paket_kasir']]);
                $mapResult = $stmtMap->fetch(PDO::FETCH_ASSOC);

                if (!$mapResult) {
                    // [AUTO-MAPPING] Otomatis buatkan Master Paket baru dengan nama asli dari Kasir
                    $stmtNewMaster = $this->db->prepare("INSERT INTO prm_master_paket (nama, tipe_paket, total_sesi, masa_berlaku_hari) VALUES (?, 'Otomatis dari Kasir', 10, 30)");
                    $stmtNewMaster->execute([$transaksi['nama_paket_kasir']]);
                    $newMasterId = $this->db->lastInsertId();

                    // Buatkan mappingnya
                    $stmtInsertMap = $this->db->prepare("INSERT IGNORE INTO prm_kasir_paket_mapping (nama_paket_kasir, id_paket_master) VALUES (?, ?)");
                    $stmtInsertMap->execute([$transaksi['nama_paket_kasir'], $newMasterId]);

                    // Ambil ulang hasil mappingnya
                    $stmtMap->execute([$transaksi['nama_paket_kasir']]);
                    $mapResult = $stmtMap->fetch(PDO::FETCH_ASSOC);

                    if (!$mapResult) {
                        throw new Exception("Paket kasir '" . $transaksi['nama_paket_kasir'] . "' gagal dipetakan secara otomatis.");
                    }
                }

                $tanggalBeli = $transaksi['tanggal_beli']; // Format: YYYY-MM-DD HH:MM:SS
                $masaBerlaku = (int)$mapResult['masa_berlaku_hari'];
                $tanggalExpired = date('Y-m-d H:i:s', strtotime($tanggalBeli . " + $masaBerlaku days"));

                // Insert into prm_kapasitas
                $stmtInsertKapasitas->execute([
                    ':no_erm' => $transaksi['no_erm'],
                    ':nomor_register' => $transaksi['nomor_register'],
                    ':id_paket' => $mapResult['id_paket_master'],
                    ':sisa' => $mapResult['total_sesi'],
                    ':tanggal_beli' => $tanggalBeli,
                    ':tanggal_expired' => $tanggalExpired,
                    ':status' => 'AKTIF'
                ]);

                // Insert into processed
                $stmtInsertProcessed->execute([
                    ':id' => $transaksi['id_transaksi'],
                    ':at' => $processedAt,
                    ':by' => $processedBy
                ]);

                $successCount++;
            }
            
            $this->db->commit();

            http_response_code(200);
            echo json_encode(array("message" => "$successCount transaksi berhasil disimpan ke Menu Pasien.", "success" => true));
        } catch(Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(array("message" => $e->getMessage(), "success" => false));
        }
    }
}
?>

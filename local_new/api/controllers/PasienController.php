<?php
class PasienController {
    private $db;
    private $kapasitas;
    private $catatan;

    public function __construct() {
        include_once 'config/database.php';
        include_once 'models/Kapasitas.php';
        include_once 'models/Catatan.php';

        $database = new Database();
        $this->db = $database->getConnection();
        $this->kapasitas = new Kapasitas($this->db);
        $this->catatan = new Catatan($this->db);
    }

    // Endpoint GET: Mendapatkan kapasitas (paket aktif) berdasarkan No. ERM
    public function getKapasitasAktif() {
        if (!isset($_GET['no_erm'])) {
            http_response_code(400);
            echo json_encode(array("message" => "Parameter no_erm tidak ditemukan."));
            return;
        }

        $no_erm = $_GET['no_erm'];
        $stmt = $this->kapasitas->getActiveByErm($no_erm);
        $num = $stmt->rowCount();

        if($num > 0){
            $kapasitas_arr = array();
            $kapasitas_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                $kapasitas_item = array(
                    "id" => $id,
                    "no_erm" => $no_erm,
                    "nomor_register" => $nomor_register,
                    "id_paket" => $id_paket,
                    "nama_paket" => isset($nama_paket) ? $nama_paket : '',
                    "total_sesi" => isset($total_sesi) ? $total_sesi : 0,
                    "sisa" => $sisa,
                    "tanggal_beli" => $tanggal_beli,
                    "tanggal_expired" => $tanggal_expired,
                    "status" => $status
                );
                array_push($kapasitas_arr["records"], $kapasitas_item);
            }

            http_response_code(200);
            echo json_encode($kapasitas_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Tidak ada paket aktif untuk pasien ini."));
        }
    }

    // Endpoint GET: Mendapatkan riwayat sesi berdasarkan No. ERM
    public function getRiwayatSesi() {
        if (!isset($_GET['no_erm'])) {
            http_response_code(400);
            echo json_encode(array("message" => "Parameter no_erm tidak ditemukan."));
            return;
        }

        $no_erm = $_GET['no_erm'];
        $stmt = $this->catatan->getHistoryByErm($no_erm);
        $num = $stmt->rowCount();

        if($num > 0){
            $riwayat_arr = array();
            $riwayat_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                $riwayat_item = array(
                    "id" => $id,
                    "id_kapasitas" => $id_kapasitas,
                    "nama_tindakan" => isset($nama_tindakan) ? $nama_tindakan : (isset($nama_paket) ? $nama_paket : 'Terapi'),
                    "no_register_kunjungan" => $no_register_kunjungan,
                    "tanggal_paket" => $tanggal_paket,
                    "sesi_ke" => $sesi_ke
                );
                array_push($riwayat_arr["records"], $riwayat_item);
            }

            http_response_code(200);
            echo json_encode($riwayat_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Tidak ada riwayat sesi."));
        }
    }

    // Endpoint POST: Mencatat penggunaan sesi paket
    public function gunakanSesi() {
        $data = json_decode(file_get_contents("php://input"));

        if(
            !empty($data->id_kapasitas) &&
            !empty($data->no_erm) &&
            !empty($data->tanggal_paket) &&
            !empty($data->sesi_ke)
        ) {
            $no_register_kunjungan = 'REG-AUTO';
            try {
                // Cari frmno dari dbold.poliumumupcust berdasarkan no_erm (bisa berupa idcust, fnoreg, frmno, atau furut)
                $sqlReg = "SELECT frmno FROM dbold.poliumumupcust 
                           WHERE idcust = :no_erm 
                              OR fnoreg = :no_erm 
                              OR frmno = :no_erm 
                              OR TRIM(furut) = TRIM(:no_erm)
                           ORDER BY fdate_in DESC, ftime_in DESC LIMIT 1";
                $stmtReg = $this->db->prepare($sqlReg);
                $stmtReg->bindParam(':no_erm', $data->no_erm);
                $stmtReg->execute();
                if($rowReg = $stmtReg->fetch(PDO::FETCH_ASSOC)) {
                    $no_register_kunjungan = $rowReg['frmno'];
                } else {
                    // Fallback ke fisiosfjual jika tidak ditemukan (misal: data test)
                    $sqlFallback = "SELECT FCRID FROM dbold.fisiosfjual WHERE FCRCUST = :no_erm ORDER BY FCRDATE DESC LIMIT 1";
                    $stmtFallback = $this->db->prepare($sqlFallback);
                    $stmtFallback->bindParam(':no_erm', $data->no_erm);
                    $stmtFallback->execute();
                    if($rowFallback = $stmtFallback->fetch(PDO::FETCH_ASSOC)) {
                        $no_register_kunjungan = $rowFallback['FCRID'];
                    }
                }
            } catch (Exception $e) {}

            // Hitung sesi ke-berapa (total_sesi - sisa_sesi_terbaru + 1)
            $stmtCek = $this->db->prepare("SELECT total_sesi, sisa FROM dbold.prm_kapasitas k JOIN dbold.prm_master_paket p ON k.id_paket = p.id WHERE k.id = :id");
            $stmtCek->bindParam(':id', $data->id_kapasitas);
            $stmtCek->execute();
            $rowCek = $stmtCek->fetch(PDO::FETCH_ASSOC);
            $sesi_ke_sebenarnya = 1;
            if ($rowCek) {
                // Sesi ke = Total Sesi - Sisa saat ini + 1 (sebelum sisa dikurangi)
                $sesi_ke_sebenarnya = ($rowCek['total_sesi'] - $rowCek['sisa']) + 1;
            }

            // Set data catatan
            $this->catatan->id_kapasitas = $data->id_kapasitas;
            $this->catatan->id_tindakan = null; // Dihapus sesuai permintaan
            $this->catatan->no_erm = $data->no_erm;
            $this->catatan->no_register_kunjungan = $no_register_kunjungan;
            $this->catatan->tanggal_paket = $data->tanggal_paket;
            $this->catatan->sesi_ke = $sesi_ke_sebenarnya;

            // Mulai transaksi untuk memastikan konsistensi
            try {
                $this->db->beginTransaction();

                // 1. Simpan catatan
                if($this->catatan->create()) {
                    // 2. Update sisa di kapasitas
                    $this->kapasitas->id = $data->id_kapasitas;
                    
                    // Gunakan nilai asli dari database yang baru dicek, bukan dari frontend
                    $sisa_terbaru = $rowCek['sisa'] - 1;
                    $this->kapasitas->sisa = $sisa_terbaru;
                    $this->kapasitas->status = ($sisa_terbaru <= 0) ? 'HABIS' : 'AKTIF';
                    
                    if($this->kapasitas->updateSisa()){
                        $this->db->commit();
                        http_response_code(201);
                        echo json_encode(array("message" => "Sesi berhasil digunakan. Catatan tersimpan."));
                        return;
                    }
                }
                
                // Jika gagal
                $this->db->rollBack();
                http_response_code(503);
                echo json_encode(array("message" => "Gagal menyimpan penggunaan sesi."));
                
            } catch (Exception $e) {
                $this->db->rollBack();
                http_response_code(500);
                echo json_encode(array("message" => "Error Server: " . $e->getMessage()));
            }

        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Data tidak lengkap. Gagal memproses sesi."));
        }
    }

    // Endpoint POST: Membatalkan sesi yang digunakan hari ini
    public function batalSesi() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->id)) {
            $this->catatan->id = $data->id;
            $rowCatatan = $this->catatan->getById();

            if($rowCatatan) {
                // Pastikan hanya bisa membatalkan sesi yang ditambahkan hari ini
                $tanggalSesi = date('Y-m-d', strtotime($rowCatatan['tanggal_paket']));
                $hariIni = date('Y-m-d');
                
                if($tanggalSesi !== $hariIni) {
                    http_response_code(403);
                    echo json_encode(array("message" => "Hanya sesi yang digunakan pada hari ini yang dapat dibatalkan."));
                    return;
                }

                $id_kapasitas = $rowCatatan['id_kapasitas'];

                try {
                    $this->db->beginTransaction();

                    // Hapus dari catatan
                    if($this->catatan->delete()) {
                        // Tambah kembali sisa sesi di kapasitas
                        $stmtCek = $this->db->prepare("SELECT k.sisa, p.total_sesi FROM dbold.prm_kapasitas k JOIN dbold.prm_master_paket p ON k.id_paket = p.id WHERE k.id = :id");
                        $stmtCek->bindParam(':id', $id_kapasitas);
                        $stmtCek->execute();
                        $rowCek = $stmtCek->fetch(PDO::FETCH_ASSOC);

                        if($rowCek) {
                            $sisa_terbaru = $rowCek['sisa'] + 1;
                            // Jangan sampai sisa melebihi total sesi
                            if($sisa_terbaru > $rowCek['total_sesi']) {
                                $sisa_terbaru = $rowCek['total_sesi'];
                            }

                            $this->kapasitas->id = $id_kapasitas;
                            $this->kapasitas->sisa = $sisa_terbaru;
                            $this->kapasitas->status = ($sisa_terbaru <= 0) ? 'HABIS' : 'AKTIF';
                            
                            if($this->kapasitas->updateSisa()) {
                                $this->db->commit();
                                http_response_code(200);
                                echo json_encode(array("message" => "Sesi berhasil dibatalkan. Sisa sesi telah dikembalikan."));
                                return;
                            }
                        }
                    }

                    $this->db->rollBack();
                    http_response_code(503);
                    echo json_encode(array("message" => "Gagal membatalkan sesi."));

                } catch (Exception $e) {
                    $this->db->rollBack();
                    http_response_code(500);
                    echo json_encode(array("message" => "Error Server: " . $e->getMessage()));
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Catatan sesi tidak ditemukan."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "ID sesi tidak valid."));
        }
    }

    // Endpoint GET: Mendapatkan seluruh kapasitas (master) untuk Menu Pasien
    public function getAllKapasitas() {
        $date = isset($_GET['date']) ? $_GET['date'] : null;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        
        if (!empty($search)) {
            $stmt = $this->kapasitas->searchAllKapasitas($search);
            $num = $stmt->rowCount();
            if($num > 0){
                $kapasitas_arr = array();
                $kapasitas_arr["records"] = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    extract($row);
                    $kapasitas_item = array(
                        "id" => $id,
                        "no_erm" => $no_erm,
                        "nama_pasien" => $nama_pasien,
                        "id_paket" => $id_paket,
                        "tanggal_beli" => $tanggal_beli,
                        "sisa" => (int)$sisa,
                        "status" => $status,
                        "nama_paket" => $nama_paket,
                        "total_sesi" => (int)$total_sesi
                    );
                    array_push($kapasitas_arr["records"], $kapasitas_item);
                }
                http_response_code(200);
                echo json_encode($kapasitas_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("records" => array(), "message" => "Tidak ada paket aktif untuk pencarian ini."));
            }
            return;
        }

        if (!$date) {
            http_response_code(200);
            echo json_encode(array("records" => array(), "message" => "Silakan pilih tanggal kunjungan terlebih dahulu."));
            return;
        }

        if(isset($_GET['all']) && $_GET['all'] == 'true') {
            $stmt = $this->kapasitas->readByVisitDate($date);
            $num = $stmt->rowCount();
            if($num > 0){
                $kapasitas_arr = array();
                $kapasitas_arr["records"] = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    extract($row);
                    $kapasitas_item = array(
                        "id" => $id,
                        "no_erm" => $no_erm,
                        "nama_pasien" => isset($nama_pasien) ? $nama_pasien : 'Tidak diketahui',
                        "nomor_register" => isset($nomor_register) ? $nomor_register : '',
                        "id_paket" => $id_paket,
                        "nama_paket" => isset($nama_paket) ? $nama_paket : '',
                        "total_sesi" => isset($total_sesi) ? $total_sesi : 0,
                        "sisa" => $sisa,
                        "tanggal_beli" => $tanggal_beli,
                        "tanggal_expired" => isset($tanggal_expired) ? $tanggal_expired : '',
                        "status" => $status
                    );
                    array_push($kapasitas_arr["records"], $kapasitas_item);
                }
                http_response_code(200);
                echo json_encode($kapasitas_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("records" => array(), "message" => "Tidak ada data paket untuk tanggal kunjungan ini."));
            }
            return;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $stmt = $this->kapasitas->readByVisitDatePaged($date, $offset, $limit);
        $num = $stmt->rowCount();

        $total_records = $this->kapasitas->countByVisitDate($date);
        $total_pages = ceil($total_records / $limit);

        if($num > 0){
            $kapasitas_arr = array();
            $kapasitas_arr["records"] = array();
            $kapasitas_arr["total_records"] = $total_records;
            $kapasitas_arr["total_pages"] = $total_pages;
            $kapasitas_arr["current_page"] = $page;

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                $kapasitas_item = array(
                    "id" => $id,
                    "no_erm" => $no_erm,
                    "nama_pasien" => isset($nama_pasien) ? $nama_pasien : 'Tidak diketahui',
                    "nomor_register" => isset($nomor_register) ? $nomor_register : '',
                    "id_paket" => $id_paket,
                    "nama_paket" => isset($nama_paket) ? $nama_paket : '',
                    "total_sesi" => isset($total_sesi) ? $total_sesi : 0,
                    "sisa" => $sisa,
                    "tanggal_beli" => $tanggal_beli,
                    "tanggal_expired" => isset($tanggal_expired) ? $tanggal_expired : '',
                    "status" => $status
                );
                array_push($kapasitas_arr["records"], $kapasitas_item);
            }

            http_response_code(200);
            echo json_encode($kapasitas_arr);
        } else {
            http_response_code(200);
            echo json_encode(array("records" => array(), "total_pages" => 0, "message" => "Tidak ada data paket untuk tanggal kunjungan ini."));
        }
    }

    // Endpoint GET: Mendapatkan riwayat catatan berdasarkan id_kapasitas
    public function getRiwayatByKapasitas() {
        if (!isset($_GET['id_kapasitas'])) {
            http_response_code(400);
            echo json_encode(array("message" => "Parameter id_kapasitas tidak ditemukan."));
            return;
        }

        $id_kapasitas = $_GET['id_kapasitas'];

        if(isset($_GET['all']) && $_GET['all'] == 'true') {
            $stmt = $this->catatan->getByKapasitas($id_kapasitas);
            $num = $stmt->rowCount();
            if($num > 0){
                $riwayat_arr = array();
                $riwayat_arr["records"] = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    extract($row);
                    $riwayat_item = array(
                        "id" => $id,
                        "id_kapasitas" => $id_kapasitas,
                        "nama_tindakan" => isset($nama_tindakan) ? $nama_tindakan : (isset($nama_paket) ? $nama_paket : 'Terapi'),
                        "no_register_kunjungan" => $no_register_kunjungan,
                        "no_erm" => $no_erm,
                        "tanggal_paket" => $tanggal_paket,
                        "sesi_ke" => $sesi_ke
                    );
                    array_push($riwayat_arr["records"], $riwayat_item);
                }
                http_response_code(200);
                echo json_encode($riwayat_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("records" => []));
            }
            return;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $stmt = $this->catatan->getByKapasitasPaged($id_kapasitas, $offset, $limit);
        $num = $stmt->rowCount();

        $total_records = $this->catatan->countByKapasitas($id_kapasitas);
        $total_pages = ceil($total_records / $limit);

        if($num > 0){
            $riwayat_arr = array();
            $riwayat_arr["records"] = array();
            $riwayat_arr["total_records"] = $total_records;
            $riwayat_arr["total_pages"] = $total_pages;
            $riwayat_arr["current_page"] = $page;

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                $riwayat_item = array(
                    "id" => $id,
                    "id_kapasitas" => $id_kapasitas,
                    "nama_tindakan" => isset($nama_tindakan) ? $nama_tindakan : (isset($nama_paket) ? $nama_paket : 'Terapi'),
                    "no_register_kunjungan" => $no_register_kunjungan,
                    "no_erm" => $no_erm,
                    "tanggal_paket" => $tanggal_paket,
                    "sesi_ke" => $sesi_ke
                );
                array_push($riwayat_arr["records"], $riwayat_item);
            }

            http_response_code(200);
            echo json_encode($riwayat_arr);
        } else {
            http_response_code(200);
            echo json_encode(array("records" => [], "total_pages" => 0));
        }
    }
}
?>

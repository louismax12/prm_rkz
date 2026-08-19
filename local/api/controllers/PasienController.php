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
            !empty($data->no_register_kunjungan) &&
            !empty($data->tanggal_paket) &&
            !empty($data->sesi_ke)
        ) {
            // Set data catatan
            $this->catatan->id_kapasitas = $data->id_kapasitas;
            $this->catatan->id_tindakan = isset($data->id_tindakan) ? $data->id_tindakan : null;
            $this->catatan->no_erm = $data->no_erm;
            $this->catatan->no_register_kunjungan = $data->no_register_kunjungan;
            $this->catatan->tanggal_paket = $data->tanggal_paket;
            $this->catatan->sesi_ke = $data->sesi_ke;

            // Mulai transaksi untuk memastikan konsistensi
            try {
                $this->db->beginTransaction();

                // 1. Simpan catatan
                if($this->catatan->create()) {
                    // 2. Update sisa di kapasitas
                    $this->kapasitas->id = $data->id_kapasitas;
                    // Ambil sisa saat ini (harus diquery ulang di real case, untuk saat ini asumsikan sisa yg dikirim dikurangi 1)
                    $this->kapasitas->sisa = $data->sisa_saat_ini - 1;
                    $this->kapasitas->status = ($this->kapasitas->sisa <= 0) ? 'HABIS' : 'AKTIF';
                    
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

    // Endpoint GET: Mendapatkan seluruh kapasitas (master) untuk Menu Pasien
    public function getAllKapasitas() {
        $date = isset($_GET['date']) ? $_GET['date'] : null;
        
        if ($date) {
            $stmt = $this->kapasitas->readByVisitDate($date);
        } else {
            // Return empty if no date is selected
            http_response_code(200);
            echo json_encode(array("records" => array(), "message" => "Silakan pilih tanggal kunjungan terlebih dahulu."));
            return;
        }

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
    }

    // Endpoint GET: Mendapatkan riwayat catatan berdasarkan id_kapasitas
    public function getRiwayatByKapasitas() {
        if (!isset($_GET['id_kapasitas'])) {
            http_response_code(400);
            echo json_encode(array("message" => "Parameter id_kapasitas tidak ditemukan."));
            return;
        }

        $id_kapasitas = $_GET['id_kapasitas'];
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
    }
}
?>

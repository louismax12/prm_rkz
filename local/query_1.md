-- menu kasir buat pilih transaksi berdasarkan tanggal
SELECT 
      f.ID as id_transaksi,
    f.FCRID as no_register, 
    f.FCRCUST as no_erm,
    f.FCRNAMA as nama_pasien,
    f.FCRDATE as tanggal_transaksi,
    f.FCRBARANG as kode_paket,
    t.nama as nama_paket,
    f.FCRJUMLAH as total_biaya,
    IF(p.id_transaksi IS NOT NULL, 1, 0) as is_processed FROM dbold.fisiosfjual f
  JOIN dbold.m_tindakan2026 t ON f.FCRBARANG = t.kode
  LEFT JOIN prm_kasir_processed p ON p.id_transaksi = f.ID
  WHERE t.asaltabel = 'SFMASBIA' AND f.FCRTAMBAH = 'T' AND f.FCRDATE >= :tanggal_awal AND f.FCRDATE <= :tanggal_akhir GROUP BY f.ID ORDER BY f.FCRDATE DESC, f.ID DESC LIMIT :limit OFFSET :offset



-- menu master data, setelah update data  dari sebuah paket

SELECT id, CONCAT_WS(' ', NULLIF(kode_paket, ''), nama) as nama, tipe_paket, total_sesi, masa_berlaku_hari FROM prm_master_paket ORDER BY id DESC LIMIT ?, ?

-- menu data paket setelah pilih tanggal, lalu riwayat pkaet pada tanggal tsb muncul

SELECT k.id, k.nik, k.noreg, 
         k.nama as nama_pasien, 
         k.id_paket, k.tanggal_beli, k.sisa, k.status, CONCAT_WS(' ', NULLIF(p.kode_paket, ''), p.nama) as nama_paket, p.total_sesi 
  FROM prm_kapasitas k 
  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
  WHERE k.status IN ('aktif', 'AKTIF', 'habis', 'HABIS') 
  AND k.tanggal_beli >= :vdate AND k.tanggal_beli < DATE_ADD(:vdate, INTERVAL 1 DAY)
  ORDER BY k.tanggal_beli DESC LIMIT :offset, :limit


-- menu data paket stelah gunakan sesi

SELECT k.id, k.nik, k.noreg, 
         k.nama as nama_pasien, 
         k.id_paket, k.tanggal_beli, k.sisa, k.status, CONCAT_WS(' ', NULLIF(p.kode_paket, ''), p.nama) as nama_paket, p.total_sesi 
  FROM prm_kapasitas k 
  LEFT JOIN prm_master_paket p ON k.id_paket = p.id
  WHERE k.status IN ('aktif', 'AKTIF', 'habis', 'HABIS') 
  AND k.tanggal_beli >= :vdate AND k.tanggal_beli < DATE_ADD(:vdate, INTERVAL 1 DAY)
  ORDER BY k.tanggal_beli DESC LIMIT :offset, :limit


-- upgrade tabel prm_kapasitas

ALTER TABLE prm_kapasitas
CHANGE COLUMN no_erm nik VARCHAR(50) NOT NULL,
CHANGE COLUMN nomor_register noreg VARCHAR(50) NOT NULL,
ADD COLUMN nama VARCHAR(150) NULL AFTER nik,
ADD INDEX idx_tanggal_status (tanggal_beli, status),
ADD INDEX idx_nik (nik);


-- upgrade tabel prm_catatan

ALTER TABLE prm_catatan
CHANGE COLUMN no_erm noreg VARCHAR(50) NOT NULL,
ADD INDEX idx_tanggal_paket (tanggal_paket),
ADD INDEX idx_noreg (noreg);

--pembersihan spasi kosong
UPDATE prm_master_paket SET kode_paket = TRIM(kode_paket);



-- ==========================================
-- DUMMY DATA UNTUK SISTEM PRM RKZ
-- ==========================================

-- 1. Tabel prm_master_paket (10 Data)
INSERT INTO `prm_master_paket` (`nama`, `tipe_paket`, `total_sesi`, `masa_berlaku_hari`) VALUES
('Paket Fisioterapi Reguler 5x', 'MULTI', 5, 30),
('Paket Fisioterapi Intensif 10x', 'MULTI', 10, 45),
('Paket Terapi Wicara Dewasa 8x', 'MULTI', 8, 30),
('Paket Terapi Okupasi Anak 10x', 'MULTI', 10, 60),
('Paket Rehab Pasca Stroke 12x', 'MULTI', 12, 90),
('Paket Terapi Nyeri Punggung 6x', 'MULTI', 6, 30),
('Paket Tumbuh Kembang 15x', 'MULTI', 15, 90),
('Paket Konsultasi Psikologi Anak 3x', 'MULTI', 3, 30),
('Tindakan Fisioterapi Satuan', 'SINGLE', 1, 7),
('Tindakan Terapi Wicara Satuan', 'SINGLE', 1, 7);

-- 2. Tabel prm_master_tindakan (10 Data)
INSERT INTO `prm_master_tindakan` (`kode_tindakan`, `nama_tindakan`) VALUES
('TND-001', 'Infrared Therapy'),
('TND-002', 'Ultrasound Therapy'),
('TND-003', 'TENS (Transcutaneous Electrical Nerve Stimulation)'),
('TND-004', 'Latihan Fisik & Motorik Kasar'),
('TND-005', 'Latihan Motorik Halus'),
('TND-006', 'Terapi Relaksasi Otot'),
('TND-007', 'Terapi Menelan (Dysphagia)'),
('TND-008', 'Stimulasi Wicara (Aphasia)'),
('TND-009', 'Evaluasi Tumbuh Kembang'),
('TND-010', 'Konseling Keluarga (Rehabilitasi)');

-- 3. Tabel prm_kapasitas (10 Data)
-- (Kita buat 5 ERM001 dan 5 ERM002 agar bisa dites pencarian)
INSERT INTO `prm_kapasitas` (`no_erm`, `nomor_register`, `id_paket`, `sisa`, `tanggal_beli`, `tanggal_expired`, `status`) VALUES
('ERM001', 'REG-2026-1001', 1, 5, '2026-08-01 10:00:00', '2026-08-31 23:59:59', 'AKTIF'),
('ERM001', 'REG-2026-1002', 2, 8, '2026-08-02 09:30:00', '2026-09-15 23:59:59', 'AKTIF'),
('ERM001', 'REG-2026-1003', 3, 0, '2026-06-01 14:20:00', '2026-07-01 23:59:59', 'HABIS'),
('ERM001', 'REG-2026-1004', 9, 1, '2026-08-05 11:15:00', '2026-08-12 23:59:59', 'AKTIF'),
('ERM001', 'REG-2026-1005', 5, 12, '2026-08-06 08:00:00', '2026-11-06 23:59:59', 'AKTIF'),
('ERM002', 'REG-2026-1006', 4, 10, '2026-08-01 12:00:00', '2026-09-30 23:59:59', 'AKTIF'),
('ERM002', 'REG-2026-1007', 6, 2, '2026-07-20 10:45:00', '2026-08-20 23:59:59', 'AKTIF'),
('ERM002', 'REG-2026-1008', 7, 0, '2025-12-01 09:00:00', '2026-03-01 23:59:59', 'EXPIRED'),
('ERM002', 'REG-2026-1009', 10, 0, '2026-08-01 14:00:00', '2026-08-01 23:59:59', 'HABIS'),
('ERM003', 'REG-2026-1010', 8, 3, '2026-08-05 16:30:00', '2026-09-05 23:59:59', 'AKTIF');

-- 4. Tabel prm_catatan (10 Data histori penggunaan)
INSERT INTO `prm_catatan` (`id_kapasitas`, `id_tindakan`, `no_erm`, `no_register_kunjungan`, `tanggal_paket`, `sesi_ke`) VALUES
(2, 1, 'ERM001', 'VISIT-001', '2026-08-03 10:00:00', 1),
(2, 2, 'ERM001', 'VISIT-002', '2026-08-05 09:00:00', 2),
(3, 7, 'ERM001', 'VISIT-003', '2026-06-05 11:00:00', 8),
(7, 4, 'ERM002', 'VISIT-004', '2026-07-22 08:30:00', 1),
(7, 5, 'ERM002', 'VISIT-005', '2026-07-25 14:00:00', 2),
(7, 4, 'ERM002', 'VISIT-006', '2026-07-29 10:15:00', 3),
(7, 5, 'ERM002', 'VISIT-007', '2026-08-02 09:45:00', 4),
(9, 8, 'ERM002', 'VISIT-008', '2026-08-01 14:15:00', 1),
(3, 8, 'ERM001', 'VISIT-009', '2026-06-10 10:00:00', 7),
(3, 7, 'ERM001', 'VISIT-010', '2026-06-15 08:45:00', 6);

-- 5. Tabel prm_users (Menambah 6 User agar genap 10, hash = md5 dari rkz123)
-- Pastikan prm_users minimal sudah ada 4 user (admin, kasir1, manajemen1, perawat1)
INSERT INTO `prm_users` (`username`, `password_hash`, `nama_lengkap`, `role`) VALUES
('kasir2', '64b7f86a98b2661ca6d698ebae1e6484', 'Kasir Tambahan', 'kasir'),
('kasir3', '64b7f86a98b2661ca6d698ebae1e6484', 'Kasir Poli Reguler', 'kasir'),
('perawat2', '64b7f86a98b2661ca6d698ebae1e6484', 'Perawat Rehab Fisioterapi', 'erm'),
('perawat3', '64b7f86a98b2661ca6d698ebae1e6484', 'Perawat Terapi Wicara', 'erm'),
('manajemen2', '64b7f86a98b2661ca6d698ebae1e6484', 'Kepala Instalasi Rehab', 'manajemen'),
('direktur', '64b7f86a98b2661ca6d698ebae1e6484', 'Direktur RS', 'manajemen');

-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.33 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.21.0.7344
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table dbold.prm_catatan: 0 rows

-- Dumping data for table dbold.prm_kapasitas: 7 rows
INSERT INTO `prm_kapasitas` (`id`, `noreg`, `nik`, `id_paket`, `sisa`, `tanggal_beli`, `tanggal_expired`, `status`) VALUES
	(1,'2603011','03413', 20, 5,'2026-08-16 00:00:00','2026-09-15 00:00:00','AKTIF'),
	(2,'260805007','02838', 21, 0,'2026-08-13 00:00:00','2026-09-12 00:00:00','HABIS'),
	(3,'260804165','02838', 22, 0,'2026-08-11 00:00:00','2026-09-10 00:00:00','HABIS'),
	(4,'260802623','03413', 23, 0,'2026-08-07 00:00:00','2026-09-06 00:00:00','HABIS'),
	(5,'260804897','02838', 24, 0,'2026-08-13 00:00:00','2026-09-12 00:00:00','HABIS'),
	(6,'260804859','03413', 23, 6,'2026-08-13 00:00:00','2026-09-12 00:00:00','AKTIF'),
	(7,'260805446','02838', 25, 9,'2026-08-14 00:00:00','2026-09-13 00:00:00','AKTIF');

-- Dumping data for table dbold.prm_kasir_paket_mapping: 12 rows
INSERT INTO `prm_kasir_paket_mapping` (`id`, `nama_paket_kasir`, `id_paket_master`) VALUES
	(1,'EM - LATIHAN MANUAL', 20),
	(2,'TENSMYO2 - MYOMED', 21),
	(3,'EXCB - LATIHAN BERAT', 22),
	(4,'TENSMYO3 - MYOMED', 23),
	(5,'U11-BTL 4710', 24),
	(6,'U9-SONOPLUS 490', 25),
	(7,'S11-CURAPULS 970', 26),
	(8,'EXCR - LATIHAN RINGAN', 27),
	(9,'TENSMYO1 - MYOMED', 28),
	(10,'TEN14 - TENSMED 931', 29),
	(11,'PT - TRANTIZER TC100', 30),
	(12,'OROFACIAL/OF', 31);

-- Dumping data for table dbold.prm_kasir_processed: 7 rows
INSERT INTO `prm_kasir_processed` (`id_transaksi`, `processed_at`, `processed_by`) VALUES
	(1466151,'2026-08-27 10:45:54','Admin Kasir'),
	(1466184,'2026-08-26 07:10:57','Admin Kasir'),
	(1466419,'2026-08-24 08:23:31','Admin Kasir'),
	(1466421,'2026-08-22 09:59:32','Admin Kasir'),
	(1466423,'2026-08-22 09:59:32','Admin Kasir'),
	(1466776,'2026-08-22 09:59:52','Admin Kasir'),
	(1467152,'2026-08-21 13:27:32','Admin Kasir');

-- Dumping data for table dbold.prm_master_paket: ~8 rows (approximately)
INSERT INTO `prm_master_paket` (`id`, `kode_paket`, `nama`, `tipe_paket`, `total_sesi`, `masa_berlaku_hari`) VALUES
	(1,'','PAKET MLDV (9 - FREE 1)','Paket Fisioterapi', 10, 30),
	(2,'','PAKET TERAPI WICARA (9 - FREE 1)','Paket Hidroterapi', 5, 15),
	(3,'','PAKET REHABILITASI (9 - FREE 1)','MULTI', 5, 30),
	(4,'345','PAKET ELEKTROTERAPI 5','MULTI', 10, 45),
	(5,'344','PAKET ELEKTROTERAPI 4','MULTI', 8, 30),
	(6,'336','PAKET HIDROTERAPI (7 - FREE 1)','MULTI', 10, 60),
	(7,'307','307 PAKET SWD','Paket Rehabilitasi (Multi Service)', 13, 90),
	(8,'','COBA','MULTI', 10, 30);

-- Dumping data for table dbold.prm_master_tindakan: 10 rows
INSERT INTO `prm_master_tindakan` (`id`, `kode_tindakan`, `nama_tindakan`) VALUES
	(1,'TND-001','Infrared Therapy'),
	(2,'TND-002','Ultrasound Therapy'),
	(3,'TND-003','TENS (Transcutaneous Electrical Nerve Stimulation)'),
	(4,'TND-004','Latihan Fisik & Motorik Kasar'),
	(5,'TND-005','Latihan Motorik Halus'),
	(6,'TND-006','Terapi Relaksasi Otot'),
	(7,'TND-007','Terapi Menelan (Dysphagia)'),
	(8,'TND-008','Stimulasi Wicara (Aphasia)'),
	(9,'TND-009','Evaluasi Tumbuh Kembang'),
	(10,'TND-010','Konseling Keluarga (Rehabilitasi)');

-- Dumping data for table dbold.prm_catatan: ~107 rows (approximately)
INSERT INTO `prm_catatan` (`id`, `id_kapasitas`, `id_tindakan`, `noreg`, `no_register_kunjungan`, `tanggal_paket`, `sesi_ke`) VALUES
	(31, 1, NULL,'2603011','03413','2026-08-21 13:26:05', 1),
	(32, 1, NULL,'2603011','03413','2026-08-21 13:26:07', 2),
	(33, 1, NULL,'2603011','03413','2026-08-21 13:26:10', 3),
	(34, 1, NULL,'2603011','03413','2026-08-21 13:26:13', 4),
	(35, 1, NULL,'2603011','03413','2026-08-21 13:26:15', 5),
	(36, 2, NULL,'260805007','J0260457','2026-08-21 13:26:35', 1),
	(37, 2, NULL,'260805007','J0260457','2026-08-21 13:26:38', 2),
	(38, 2, NULL,'260805007','J0260457','2026-08-21 13:26:40', 3),
	(39, 2, NULL,'260805007','J0260457','2026-08-21 13:26:43', 4),
	(40, 3, NULL,'260804165','J0260457','2026-08-21 13:27:41', 1),
	(156, 9, NULL,'260800948','J0088948','2026-08-27 14:09:48', 10);


-- Dumping data for table dbold.fisiosfjual: 3,624 rows
INSERT INTO `fisiosfjual` (`ID`, `FCRFH`, `FCRID`, `FCRDATE`, `FCRTANGGAL`, `FCRSALDO`, `FCRCUST`, `FCRSTAT`, `FCRPAV`, `FCRURUT`, `FCRBARANG`, `FCRQTY`, `FCRHARJU`, `FCRTAMBAH`, `FCRJUMLAH`, `FCRNETTO`, `FCRHARPO`, `FCRTUNAI`, `FCRDC`, `FCRCC`, `FCRUM`, `FCRKREDIT`, `FCRDISC`, `FCRCARD`, `FCRCHARGE`, `FTA`, `FCRRSP`, `FCRDOKTER`, `FCRNAMA`, `FCRURUT2`, `FCRJAM`, `FCRPOST`, `FCRNOKWIT`, `FCRATAS`, `FCRRETUR`, `FCRACC`, `FCRPAJAK`, `FCRTRAN`, `FCRDARI`, `FCRTLP`, `FCRMEDIKA`, `FCRMEDDC`, `FCRMEDCC`, `RMPOLI`, `TGLPERIKSA`, `FCRDATEACC`, `FCRDATEACCOPR`, `FCRDATEADM`, `fCRALASAN`, `FCRSRACC`, `FCRALASAN2`, `FCRSRACC2`, `FCRDROP`, `FCRHRDOKTER`, `FDISCITEM`, `harjusoe`, `idsoe`) VALUES
	(1465974, NULL,'03413','2026-08-01','2026-08-01', 100000,'260800043','1','', 1, 359, 1.5, 90000,'T', 135000, 135000, 0, 0, 135000, 0, 0, 0, 0,'', 0,'','12095734','J029-26-59','NY LISTIA NOVITA-P','F','07:51:24','', NULL, NULL, NULL,'', NULL,'','dr. HASAN WIJAYA Sp.KFR','', 0, 0, 0,'J0292659', NULL,'0000-00-00 00:00:00','','0000-00-00 00:00:00','','','','','', 0, 0, 0, 0),
	(1465975, NULL,'03413','2026-08-01','2026-08-01', 100000,'260800023','1','', 2, 188, 1, 116000,'T', 341000, 341000, 0, 0, 0, 341000, 0, 0, 0,'BCA', 0,'','12095748','J019-23-50','TN ALBERT SOEGIANTORO-L','F','08:03:30','', NULL, NULL, NULL,'', NULL,'','---','', 0, 0, 0,'J0192350', NULL,'0000-00-00 00:00:00','','0000-00-00 00:00:00','','','','','', 0, 0, 0, 0),
	(1465976, NULL,'03413','2026-08-01','2026-08-01', 100000,'260800023','1','', 2, 359, 1.5, 90000,'T', 341000, 341000, 0, 0, 0, 341000, 0, 0, 0,'BCA', 0,'','12095748','J019-23-50','TN ALBERT SOEGIANTORO-L','F','08:03:30','', NULL, NULL, NULL,'', NULL,'','---','', 0, 0, 0,'J0192350', NULL,'0000-00-00 00:00:00','','0000-00-00 00:00:00','','','','','', 0, 0, 0, 0),
	(1465977, NULL,'03413','2026-08-01','2026-08-01', 100000,'260800023','1','', 2, 341, 1, 90000,'T', 341000, 341000, 0, 0, 0, 341000, 0, 0, 0,'BCA', 0,'','12095748','J019-23-50','TN ALBERT SOEGIANTORO-L','F','08:03:30','', NULL, NULL, NULL,'', NULL,'','---','', 0, 0, 0,'J0192350', NULL,'0000-00-00 00:00:00','','0000-00-00 00:00:00','','','','','', 0, 0, 0, 0),
	(1465978, NULL,'03413','2026-08-01','2026-08-01', 100000,'260800021','4','KT', 3, 189, 1, 240000,'T', 330000, 330000, 0, 0, 0, 0, 0, 0, 0,'', 0,'A','12095753','J016-03-41','TN MUSA ZEBAOTH TORAH BUWONO-L','F','08:06:56','F', NULL, NULL, NULL,'', NULL,'','dr. GLEN PURNOMO Sp.OT., Subsp.,P.L.(K)','A106', 0, 0, 0,'J0160341', NULL,'0000-00-00 00:00:00','','0000-00-00 00:00:00','','','','','', 0, 0, 0, 0),
	(1465979, NULL,'03413','2026-08-01','2026-08-01', 100000,'260800021','4','KT', 3, 330, 1, 90000,'T', 330000, 330000, 0, 0, 0, 0, 0, 0, 0,'', 0,'A','12095753','J016-03-41','TN MUSA ZEBAOTH TORAH BUWONO-L','F','08:06:56','F', NULL, NULL, NULL,'', NULL,'','dr. GLEN PURNOMO Sp.OT., Subsp.,P.L.(K)','A106', 0, 0, 0,'J0160341', NULL,'0000-00-00 00:00:00','','0000-00-00 00:00:00','','','','','', 0, 0, 0, 0),
	(1465980, NULL,'03413','2026-08-01','2026-08-01', 100000,'260800025','4','KT', 4, 330, 1, 90000,'T', 206000, 206000, 0, 0, 0, 0, 0, 0, 0,'', 0,'A','12095754','J054-84-97','TN AGUNG SETYO BUDHI-L','F','08:07:21','', NULL, NULL, NULL,'', NULL,'','Prof.Dr.dr.HERI SUROTO Sp.OT., Subsp.T.L.B.M. (K)','A032', 0, 0, 0,'J0548497', NULL,'0000-00-00 00:00:00','','0000-00-00 00:00:00','','','','','', 0, 0, 90000, 0);


-- Dumping data for table dbold.admpacust: 92 rows
INSERT INTO `admpacust` (`REGNO_IN`, `REGIBU`, `PS`, `CODENAME`, `NAME`, `PAV`, `PIUTANG`, `BAYAR`, `FBY`, `CLASS`, `ROOM`, `NOBED`, `AGE`, `RMNO`, `DATE_IN`, `TIME_IN`, `DATE_OUT`, `TIME_OUT`, `DEATH_TIME`, `DEATH_DATE`, `BOOK_CLASS`, `DIAG_IN`, `DIAG_OUT`, `KDR1`, `KDOK1`, `DR1`, `PHONE1`, `KDR2`, `KDOK2`, `DR2`, `PHONE2`, `KDR3`, `KDOK3`, `DR3`, `PHONE3`, `KDR4`, `KDOK4`, `DR4`, `PHONE4`, `EMC_NAME`, `EMC_ADDR`, `EMC_PHONE`, `TERM_PAY`, `KDPT`, `KDKYW`, `PAID_BY`, `RELATION`, `ASALPX`, `JENISKASUS`, `NOPESERTA`, `NOJAMINAN`, `PISAASKES`, `BUASKES`, `FASKES`, `TGL_PULANG`, `DIAGOUTASKES`, `BATALRM`, `KDOK5`, `DR5`, `BIDAN`, `POS`, `KETPOS`, `NORESERVASI`, `ID`, `nikuser`, `EMC_NAME2`, `EMC_ADDR2`, `EMC_PHONE2`, `RELATION2`) VALUES
	('2600790','', NULL,'TN','AVEN SUPANGAT','3', 0, 0,'','UT','23','',' 75 110','35-11-72','2026-02-23','18:30:35','0000-00-00','','','0000-00-00','UT','POST CVDSI (LVO M1 KANAN ) + AF RVR','', NULL,'130037','CINDY CECILIA', NULL, NULL,'','', NULL, NULL,'','', NULL, NULL,'','', NULL,'TN.ANTHONY PANGESTU SUPANGAT','PLUIT KARANG CANTIK 1 P-3.B/45 JAK','6597640204','SENDIRI','','','','KELUARGA','POLI','SARAF','','', NULL,'', NULL, NULL, NULL,'','','','','','', 49953, 000000410665,'03819','TN.RICHARD PRAYOGA SUPANGAT','PLUIT KARANG CANTIK 1 P-3.B/45 JAK','6587505254','KELUARGA'),
	('2601030','', NULL,'NN','MARIA MAGDALENA SSPS,SR','15', 0, 0,'*','UT-B','211','',' 611019','34-71-48','2026-03-12','14:58:57','0000-00-00','','','0000-00-00','UT-B','CA ENDOMETRIOMA RESIDIF METASTASE','', NULL,'080053','TROY FONDA', NULL, NULL,'210042','IDAJANI MARJADI', NULL, NULL,'160013','SANTOSO', NULL, NULL,'','', NULL,'SR. IMMACULATA','JAMBI 20, SURABAYA','081357632010','INSTANSI','S040','','SUSTERAN SSPS PROPINSI JA','KELUARGA','DR PRAKTEK LUAR','DALAM','','', NULL,'', NULL, NULL, NULL,'','','','','','', 50311, 000000410905,'04018','','','',''),
	('2601259','', NULL,'NN','ROSA INDRAWIKAN RINA T, S.SP.S.,SR','15', 0, 0,'*','UT-B','209','',' 55 6 7','14-75-66','2026-03-28','15:00:18','2026-04-21','11:00:00','','0000-00-00','UT-B','LIVER AVM DD LIVER MASS + SYOK KARDIOGENIK DD NEUROGENIK + PNEUMONIA DD METASTASE PARU','', NULL,'010038','IWAN KRISTIAN', NULL, NULL,'160013','SANTOSO', NULL, NULL,'','', NULL, NULL,'','', NULL,'SR. IMMACULATA','JAMBI 20, SURABAYA','081357632010','INSTANSI','S040','','SUSTERAN SSPS PROPINSI JA','KELUARGA','IGD','BEDAH','','', NULL,'', NULL, NULL, NULL,'','','','','','', 50609, 000000411134,'G0752','','','',''),
	('2601310','', NULL,'TN','HARITS SYUHADA','4', 0, 0,'*','II','30','',' 38 930','35-14-03','2026-03-31','20:39:18','2026-04-02','11:00:00','','0000-00-00','II','COMMINUTED FRACTURE DISTAL RADIUS + DISPLACEMENT FRAGMENT DISTAL + CF OS STYLOIDEUS DISTAL ULNAR','', NULL,'020019','STEPHANUS HENDRATA DARMADI', NULL, NULL,'','', NULL, NULL,'','', NULL, NULL,'','', NULL,'NY. DEWINTA','BANDULAN 8 METRO RESIDENCE E-11','085733233994','INSTANSI','A032','','BPJS KETENAGAKERJAAN','ISTRI','IGD','BEDAH TULANG','25175365276','', NULL,'', NULL, NULL, NULL,'','','','','','', 50677, 000000411185,'03245','TN. VITO','KUPANG KRAJAN 1, SURABAYA','081232107967','TEMAN'),
	('2601376','', NULL,'NY','ELSYAWATI','1', 0, 0,'','UT-A','01','',' 851016','35-13-13','2026-04-04','11:46:48','0000-00-00','','','0000-00-00','UT-A','OBS ANEMIA + KONSTIPASI + DEMENTIA','', NULL,'080034','MARKUS TJAHJONO', NULL, NULL,'','', NULL, NULL,'','', NULL, NULL,'','', NULL,'NY. TJANDRAWATI','NUSA INDAH 35 PROBOLINGGO','08121712675','SENDIRI','','','','ANAK','IGD','DALAM','','', NULL,'', NULL, NULL, NULL,'','','','','','', 50757, 000000411251,'03920','NN. JOCELYN','NUSA INDAH 35 PROBOLINGGO','0895600468229','KELUARGA'),
	('2601387','', NULL,'TN','MOCHAMMAD WAHYUDHY','YO7', 0, 0,'*','II','Y707','',' 26 4 9','34-95-85','2026-04-05','15:47:01','2026-04-09','11:00:00','','0000-00-00','II','BPI C5-TH1 D, POST NERVE TRANSFER','', NULL,'020014','HERI SUROTO', NULL, NULL,'','', NULL, NULL,'','', NULL, NULL,'','', NULL,'NY. NUZULUL KHOIRUNNISA','DUSUN BITING I, LUMAJANG','085692054159','ASURANSI','A032','','BPJS KETENAGAKERJAAN','ISTRI','DR PRAKTEK DLM','BEDAH TULANG','22012260000','', NULL,'BPJS KETENAGAKERJAAN', NULL, NULL, NULL,'','','','','','', 50755, 000000411262,'03920','','','',''),
	('2601405','', NULL,'TN','AHMAD MAULUDDIN','YO7', 0, 0,'*','II','Y707','',' 29 8 6','35-14-51','2026-04-06','16:28:52','2026-04-09','11:00:00','','0000-00-00','II','CF VL 1','', NULL,'020019','STEPHANUS HENDRATA DARMADI', NULL, NULL,'','', NULL, NULL,'','', NULL, NULL,'','', NULL,'NY. SUPARSIH','SUCI RT.002 RW. 005 GRESIK','089601403801','ASURANSI','A032','','BPJS KETENAGAKERJAAN','ORANG TUA','IGD','BEDAH TULANG','3525103107970004','', NULL,'', NULL, NULL, NULL,'','','','','','', 50792, 000000411280,'02827','NN. ERIZKA','KEDURUS 3-B/4 SURABAYA','0895632348482','TEMAN'),
	('2601410','', NULL,'NY','IDA SURJANI','ICU', 0, 0,'','ICUR1','REG1','',' 87 3 0','34-78-56','2026-04-06','21:23:00','0000-00-00','','','0000-00-00','ICUR1','PNEUMONIA + DM','', NULL,'160016','ELIZABETH VANIA PALILINGAN', NULL, NULL,'080032','SUDJITO', NULL, NULL,'','', NULL, NULL,'','', NULL,'NY. SUSAN','MOJOPAHIRT 14, SURABAYA','08123502577','SENDIRI','','','','ANAK','IGD','PARU','','', NULL,'', NULL, NULL, NULL,'','','','','','', 50797, 000000411285,'02047','NY.RUBY','MANYAR JAYA 12/127-A','081336709959','ANAK');


-- Dumping data for table dbold.poliumumupcust: 1,001 rows
INSERT INTO `poliumumupcust` (`idcust`, `fnoreg`, `fcodename`, `fname`, `fage`, `frmno`, `fdate_in`, `ftime_in`, `furut`, `fdiagnosa1`, `fdiagnosa2`, `fkdok1`, `fdr1`, `fkdok2`, `fdr2`, `fasal`, `fkunjung`, `flayan`, `fakhir`, `fcarabayar`, `fkodept`, `fnopeg`, `fkarcis`, `fkdkarcis`, `fterapi1`, `fterapi2`, `fuser`, `fnik`, `flokasi`, `fnmprpeg`, `fjk`, `status_antri`, `alasanr007`, `noantri`, `fperiksa`, `fkdok3`, `fdr3`, `fkdok4`, `fdr4`, `fdate_out`) VALUES
	(3390789,'260800020','TN','RIO FIRMAN HASITUNGAN PURBA','0440808','J0570988','2026-08-01','07:00:06','FI0001','','','1295','dr. TIO ANDREW SANTOSO Sp.N','','', 1, 1, 36, 1, 1,'','', 0, 0,'','','03413','','FIS','','', 0,'', 1,'','','','','','1900-01-01 00:00:00'),
	(3390790,'260800021','TN','MUSA ZEBAOTH TORAH BUWONO','0171015','J0160341','2026-08-01','07:02:30','FI0002','','','1263','dr. GLEN PURNOMO Sp.OT., Subsp.,P.L.(K)','','', 1, 2, 36, 1, 2,'A106','-', 0, 0,'','','03413','','FIS','ADMEDIKA - AIA INDIVIDU (AIA FINANCIAL, PT)','', 0,'-', 1,'','','','','','2026-08-01 00:00:00'),
	(3390791,'260800022','TN','PANDU CIPTA MANDIRI','0740803','J0365498','2026-08-01','07:02:43','FI0003','','','1121','dr. HASAN WIJAYA Sp.KFR','','', 1, 2, 36, 1, 1,'','', 0, 0,'','','03413','','FIS','','', 0,'', 1,'','','','','','1900-01-01 00:00:00'),
	(3390792,'260800023','TN','ALBERT SOEGIANTORO','0280909','J0192350','2026-08-01','07:02:48','FI0004','','','316','---','','', 1, 2, 36, 1, 1,'','', 0, 0,'','','02616','','FIS','','', 0,'', 1,'','','','','','1900-01-01 00:00:00'),
	(3390793,'260800024','TN','KIANG HIAN SIANG','0740800','J0538461','2026-08-01','07:02:59','FI0005','','','25','Prof. Dr. dBAMBANG PRIJAMBODO Sp.OT, Subsp. O.T.B.','','', 1, 2, 36, 1, 1,'','', 0, 0,'','','03413','','FIS','','', 0,'', 1,'','','','','','1900-01-01 00:00:00'),
	(3390794,'260800025','TN','AGUNG SETYO BUDHI','0280427','J0548497','2026-08-01','07:03:13','FI0006','','','310','Prof.Dr.dr.HERI SUROTO Sp.OT., Subsp.T.L.B.M. (K)','','', 1, 2, 36, 1, 2,'A032','', 0, 0,'','','03413','','FIS','BPJS KETENAGAKERJAAN','', 0,'', 1,'','','','','','2026-08-01 00:00:00'),
	(3390799,'260800030','TN','AFTON ILMAN GHAZALI','0410620','J0194644','2026-08-01','07:04:49','FI0007','','','310','Prof.Dr.dr.HERI SUROTO Sp.OT., Subsp.T.L.B.M. (K)','','', 4, 2, 36, 1, 2,'A032','', 0, 0,'','','02616','','FIS','BPJS KETENAGAKERJAAN','', 0,'', 2,'','','','','','2026-08-01 00:00:00'),
	(3390814,'260800043','NY','LISTIA NOVITA','0430822','J0292659','2026-08-01','07:17:46','FI0008','','','1121','dr. HASAN WIJAYA Sp.KFR','','', 4, 2, 36, 1, 1,'','', 0, 0,'','','02616','','FIS','','', 0,'', 2,'','','','','','1900-01-01 00:00:00');

-- Dumping data for table dbold.m_tarif_permohonan: 31 rows
INSERT INTO `m_tarif_permohonan` (`id_mohon`, `tipe_permohonan`, `id_master_edit`, `nama_paket`, `nama_brosur`, `deskripsi`, `hargarj`, `hargarj_acc`, `file_lampiran`, `nik_pemohon`, `tgl_mohon`, `status_acc`, `catatan_direktur`, `nik_acc`, `tgl_acc`, `is_posted`) VALUES
	(14,'edit', 14257,'PAKET SKRINING GANGGUAN BAK','TESTING','coba', 900000, NULL,'','03690','2026-06-20 02:00:20', 1,'','03690','2026-06-20 02:00:30', 1),
	(15,'edit', 16484,'PAKET VAKSIN','TESTING','cobacoba', 50800, NULL,'','03690','2026-06-20 02:01:30', 1,'','03690','2026-06-20 02:01:38', 1),
	(16,'edit', 16484,'PAKET VAKSIN','HALO','TESCOBA', 10000, NULL,'','03690','2026-06-20 02:17:51', 1,'','03690','2026-06-20 02:18:14', 0),
	(17,'edit', 16030,'PAKET VAKSIN IBU HAMIL','coba','coba', 4000000, NULL,'','03690','2026-06-20 02:39:36', 1,'','03690','2026-06-20 02:40:08', 1),
	(18,'edit', 16430,'PAKET DETEKSI CA.PARU','aaaaaaaa','bbbbbb', 1000000, NULL,'','03690','2026-06-20 05:31:54', 1,'','03690','2026-06-20 05:32:21', 1),
	(19,'edit', 14831,'PAKET MCU STUDY ABROAD','PAKET MCU STUDY ABROAD','asfsr5dehgdtrjtyd', 1000000, NULL,'','03690','2026-06-20 05:48:52', 1,'','03690','2026-06-20 05:49:09', 1),
	(20,'edit', 16484,'PAKET VAKSIN','','', 20000, NULL,'','03690','2026-06-22 01:36:25', 1,'','03690','2026-06-22 01:36:46', 0),
	(21,'baru', NULL,'TESTING DELEGASI', NULL, NULL, 100000, NULL,'','03690','2026-06-23 00:51:58', 1,'TESTING DELEGASI','22222','2026-06-23 01:04:06', 0);
	

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE,'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE,'') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

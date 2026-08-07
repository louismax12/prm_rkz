-- --------------------------------------------------------
-- Host:                         192.168.2.12
-- Server version:               10.11.14-MariaDB-0ubuntu0.24.04.1 - Ubuntu 24.04
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             12.19.0.7314
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for hrd
CREATE DATABASE IF NOT EXISTS `hrd` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `hrd`;

-- Dumping structure for table hrd.datadasar
CREATE TABLE IF NOT EXISTS `datadasar` (
  `NIP` varchar(5) NOT NULL,
  `Nik_atas` varchar(5) NOT NULL,
  `NoRM` varchar(9) NOT NULL,
  `Nama` varchar(100) NOT NULL,
  `NamaBaptis` varchar(100) NOT NULL,
  `NamaPanggilan` varchar(20) NOT NULL,
  `NamaKTP` varchar(100) NOT NULL,
  `NamaIjasah` varchar(100) NOT NULL,
  `NamaAkte` varchar(100) NOT NULL,
  `AlamatKTP` varchar(100) NOT NULL,
  `NoKTP` varchar(20) NOT NULL,
  `NamaHubungan` varchar(35) NOT NULL,
  `AlamatHubungan` varchar(100) NOT NULL,
  `TelpHubungan` varchar(20) NOT NULL,
  `Hubungan` varchar(25) NOT NULL,
  `AlamatAsal` varchar(100) NOT NULL,
  `KotaKTP` varchar(30) NOT NULL,
  `PropinsiKTP` varchar(25) NOT NULL,
  `TelpAreaAsal` varchar(4) NOT NULL,
  `TelpAsal` varchar(15) NOT NULL,
  `AlamatSekarang` varchar(100) NOT NULL,
  `KotaSekarang` varchar(30) NOT NULL,
  `PropinsiSekarang` varchar(30) NOT NULL,
  `TelpAreaSekarang` varchar(4) NOT NULL,
  `TelpSekarang` varchar(15) NOT NULL,
  `TelpAreaLain` varchar(4) NOT NULL,
  `TelpLain` varchar(15) NOT NULL,
  `Hp` varchar(15) NOT NULL,
  `JenisKelamin` varchar(1) NOT NULL,
  `Agama` varchar(15) NOT NULL,
  `Permandian` date NOT NULL,
  `KotaLahir` varchar(30) NOT NULL,
  `TglLahir` date NOT NULL,
  `KotaLahirIjasah` varchar(30) NOT NULL,
  `TglLahirIjasah` date NOT NULL,
  `KotaLahirKTP` varchar(30) NOT NULL,
  `TglLahirKTP` date NOT NULL,
  `SukuBangsa` varchar(50) NOT NULL,
  `TglWNI` datetime NOT NULL,
  `Status` varchar(11) NOT NULL,
  `MasukRSTgl` date NOT NULL,
  `Asrama` varchar(25) NOT NULL,
  `Bagian` varchar(50) NOT NULL,
  `Foto` enum('True','False') NOT NULL,
  `Email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  `Hidup` varchar(1) NOT NULL,
  `UserNIK` varchar(5) NOT NULL,
  `TimeDate` datetime NOT NULL,
  `Flag` varchar(1) NOT NULL,
  `KepalaKeluarga` varchar(1) NOT NULL,
  `Penilaian` varchar(1) NOT NULL,
  `Keaktifan` varchar(15) NOT NULL,
  `UserNIKEdit` varchar(5) NOT NULL,
  `TimeDateEdit` datetime NOT NULL,
  `TglKeluar` date NOT NULL,
  `AktifYa` varchar(25) NOT NULL,
  `AktifTidak` varchar(25) NOT NULL,
  `rmvirtual` varchar(10) NOT NULL,
  `tgldiangkat` date NOT NULL,
  `kredensial` varchar(2) NOT NULL,
  `level` int(11) NOT NULL,
  `golgaji` varchar(10) NOT NULL,
  `kodegaji` varchar(10) NOT NULL,
  `encrypt_pass` text NOT NULL,
  `nokpj` varchar(20) NOT NULL,
  `tglkpj` date NOT NULL,
  `nobpjs` varchar(20) NOT NULL,
  `ppkkarah` varchar(1) NOT NULL,
  `idketenagaan` int(11) NOT NULL,
  `jeniskyw` varchar(15) NOT NULL COMMENT 'CAKAR, SUSTER,DOKTER,KYW',
  `idfinger` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`NIP`) USING BTREE,
  KEY `namanipalamat` (`Nama`,`NIP`,`AlamatKTP`) USING BTREE,
  KEY `NIPpassword` (`NIP`,`password`) USING BTREE,
  KEY `namapanggilan` (`NamaPanggilan`) USING BTREE,
  KEY `namaktp` (`NamaKTP`) USING BTREE,
  KEY `agama` (`Agama`) USING BTREE,
  KEY `NoRM` (`NoRM`) USING BTREE,
  KEY `AktifYa` (`AktifYa`) USING BTREE,
  KEY `AktifTidak` (`AktifTidak`) USING BTREE,
  KEY `rmvirtual` (`rmvirtual`) USING BTREE,
  KEY `TglKeluar` (`TglKeluar`) USING BTREE,
  KEY `NoKTP` (`NoKTP`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table hrd.datadasar: 9 rows
INSERT INTO `datadasar` (`NIP`, `Nik_atas`, `NoRM`, `Nama`, `NamaBaptis`, `NamaPanggilan`, `NamaKTP`, `NamaIjasah`, `NamaAkte`, `AlamatKTP`, `NoKTP`, `NamaHubungan`, `AlamatHubungan`, `TelpHubungan`, `Hubungan`, `AlamatAsal`, `KotaKTP`, `PropinsiKTP`, `TelpAreaAsal`, `TelpAsal`, `AlamatSekarang`, `KotaSekarang`, `PropinsiSekarang`, `TelpAreaSekarang`, `TelpSekarang`, `TelpAreaLain`, `TelpLain`, `Hp`, `JenisKelamin`, `Agama`, `Permandian`, `KotaLahir`, `TglLahir`, `KotaLahirIjasah`, `TglLahirIjasah`, `KotaLahirKTP`, `TglLahirKTP`, `SukuBangsa`, `TglWNI`, `Status`, `MasukRSTgl`, `Asrama`, `Bagian`, `Foto`, `Email`, `password`, `Hidup`, `UserNIK`, `TimeDate`, `Flag`, `KepalaKeluarga`, `Penilaian`, `Keaktifan`, `UserNIKEdit`, `TimeDateEdit`, `TglKeluar`, `AktifYa`, `AktifTidak`, `rmvirtual`, `tgldiangkat`, `kredensial`, `level`, `golgaji`, `kodegaji`, `encrypt_pass`, `nokpj`, `tglkpj`, `nobpjs`, `ppkkarah`, `idketenagaan`, `jeniskyw`, `idfinger`) VALUES
	('01820', '', '22222', 'Bu Niken', '', 'Agung', 'dr.Agung Kurniawan Saputra,MARS', 'dr.Agung Kurniawan Saputra,MARS', 'dr.Agung Kurniawan Saputra,MARS', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'L', 'ISLAM', '0000-00-00', '', '1980-01-01', '', '1980-01-01', '', '1980-01-01', '', '0000-00-00 00:00:00', 'Tetap', '2024-01-01', 'TIDAK', 'DIREKSI', 'False', '', '222222', 'T', 'admin', '2026-04-16 04:07:34', '', 'Y', '', 'Ya', 'admin', '2026-04-16 04:07:34', '0000-00-00', 'Tetap', '', '', '2024-01-01', '', 0, '', '', 'd73b3e0f25da194bfe23640581c1c417', '', '0000-00-00', '', '', 0, 'KARYAWAN', 0),
	('02212', '', '', 'Sr. Augusta, SSpS', '', 'Augusta', 'Sr.Ir.Augusta Surijah,SSpS.,MM', 'Sr.Ir.Augusta Surijah,SSpS.,MM', 'Sr.Ir.Augusta Surijah,SSpS.,MM', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'P', 'KATOLIK', '0000-00-00', '', '1970-01-01', '', '1970-01-01', '', '1970-01-01', '', '0000-00-00 00:00:00', 'Tetap', '2024-01-01', 'TIDAK', 'KEUANGAN', 'False', '', '333333', 'Y', 'admin', '2026-04-16 04:07:34', '', 'Y', '', 'Ya', 'admin', '2026-04-16 04:07:34', '0000-00-00', 'Tetap', '', '', '2024-01-01', '', 0, '', '', 'b3f17be0a25d99ef6da8317fe59fe4c8', '', '0000-00-00', '', '', 0, 'KARYAWAN', 0),
	('02240', '', '', 'FANINO', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '0000-00-00', '', '0000-00-00', '', '0000-00-00', '', '0000-00-00', '', '0000-00-00 00:00:00', '', '0000-00-00', '', '', 'False', '', '123', 'T', '', '2026-04-16 04:07:34', '', 'Y', '', 'Ya', '', '2026-04-16 04:07:34', '0000-00-00', 'Tetap', '', '', '0000-00-00', '', 0, '', '', '202cb962ac59075b964b07152d234b70', '', '0000-00-00', '', '', 0, '', 0),
	('03263', '', '', 'Vidya', '', 'Augusta', 'Sr.Ir.Augusta Surijah,SSpS.,MM', 'Sr.Ir.Augusta Surijah,SSpS.,MM', 'Sr.Ir.Augusta Surijah,SSpS.,MM', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'P', 'KATOLIK', '0000-00-00', '', '1970-01-01', '', '1970-01-01', '', '1970-01-01', '', '0000-00-00 00:00:00', 'Tetap', '2024-01-01', 'TIDAK', 'KEUANGAN', 'False', '', '333333', 'T', 'admin', '2026-04-16 04:07:34', '', 'Y', '', 'Ya', 'admin', '2026-04-16 04:07:34', '0000-00-00', 'Tetap', '', '', '2024-01-01', '', 0, '', '', 'b3f17be0a25d99ef6da8317fe59fe4c8', '', '0000-00-00', '', '', 0, 'KARYAWAN', 0),
	('03690', '', '810341', 'ANUGRAH YULI WIBOWO', '', 'AAN', 'ANUGRAH YULI WIBOWO', 'ANUGRAH YULI WIBOWO', 'ANUGRAH YULI WIBOWO', 'LR. DISTRIK NO 11 ', '3316052207950001', 'YOHANA WIDHA', 'PETEMON 1 / 56', '085655106302', '', '', 'BLORA', '', '', '', 'PONDOK WAGE INDAH II BLOK SS NO. 16, RT 006, RW 012, WAGE, TAMAN, SIDOARJO', 'SIDOARJO', 'JAWA TIMUR', '', '-', '', '082134349708', '-', 'L', 'KATOLIK', '0000-00-00', 'BLORA', '1995-07-22', 'BLORA', '1995-07-22', 'BLORA', '1995-07-22', 'JAWA', '0000-00-00 00:00:00', 'KAWIN', '2017-04-01', 'TIDAK', '', '', 'anowibowo69@gmail.com', '895623', 'T', '01385', '2016-04-01 11:29:01', '', 'Y', '', 'Ya', '03904', '2023-12-02 20:11:28', '0000-00-00', 'Tetap', '-', '810341', '2017-04-01', '', 7, '7.A.2', '7.A.2022', '88932a2f1917d534364bef5a9884dca6', '17019822117', '0000-00-00', '0001170579532', '*', 203, 'KARYAWAN', 0),
	('03830', '-', '-', 'HENI', '-', 'HENI', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '1900-01-01', '-', '1990-01-01', '-', '1900-01-01', '-', '1900-01-01', '-', '1900-01-01 00:00:00', 'Tetap', '2026-03-28', '-', 'UMUM', 'False', '-', '000000', 'Y', '', '2026-03-28 08:00:00', '1', '1', '1', 'Ya', '', '2026-03-28 08:00:00', '1900-01-01', 'Tetap', 'T', '-', '1900-01-01', '-', 7, '-', '-', 'c33367701511b4f6020ec61ded352059', '-', '1900-01-01', '-', '-', 205, '-', 0),
	('03858', '', '', 'Sr.Paulana,SSpS', '', 'Paulana', 'Sr.Paulana,SSpS', 'Sr.Paulana,SSpS', 'Sr.Paulana,SSpS', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'P', 'KATOLIK', '0000-00-00', '', '1970-01-01', '', '1970-01-01', '', '1970-01-01', '', '0000-00-00 00:00:00', 'Tetap', '2024-01-01', 'TIDAK', 'KEUANGAN', 'False', '', '111111', 'T', 'admin', '2026-04-16 04:07:33', '', 'Y', '', 'Ya', 'admin', '2026-04-16 04:07:33', '0000-00-00', 'Tetap', '', '', '2024-01-01', '', 0, '', '', '20f63cf421f1abc601814286878ba628', '', '0000-00-00', '', '', 0, 'KARYAWAN', 0),
	('0762', '-', '-', 'LOUIS', '-', 'LOUIS', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '1900-01-01', '-', '1990-01-01', '-', '1900-01-01', '-', '1900-01-01', '-', '1900-01-01 00:00:00', 'Tetap', '2026-03-28', '-', 'UMUM', 'False', '-', '151219', 'Y', '', '2026-03-28 08:00:00', '1', '1', '1', 'Ya', '', '2026-03-28 08:00:00', '1900-01-01', 'Tetap', 'T', '-', '1900-01-01', '-', 7, '-', '-', 'c33367701511b4f6020ec61ded352059', '-', '1900-01-01', '-', '-', 205, '-', 0),
	('54321', '-', '-', 'JONO', '-', 'JONO', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '1900-01-01', '-', '1990-01-01', '-', '1900-01-01', '-', '1900-01-01', '-', '1900-01-01 00:00:00', 'Tetap', '2026-03-28', '-', 'UMUM', 'False', '-', '654321', 'Y', '', '2026-03-28 08:00:00', '1', '1', '1', 'Ya', '', '2026-03-28 08:00:00', '1900-01-01', 'Tetap', 'T', '-', '1900-01-01', '-', 7, '-', '-', 'c33367701511b4f6020ec61ded352059', '-', '1900-01-01', '-', '-', 205, '-', 0);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

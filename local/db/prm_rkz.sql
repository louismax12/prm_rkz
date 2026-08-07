-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: prm_rkz
-- ------------------------------------------------------
-- Server version	5.7.44-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `prm_catatan`
--

DROP TABLE IF EXISTS `prm_catatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prm_catatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_kapasitas` int(11) NOT NULL,
  `id_tindakan` int(11) DEFAULT NULL,
  `no_erm` varchar(50) NOT NULL,
  `no_register_kunjungan` varchar(50) NOT NULL,
  `tanggal_paket` datetime NOT NULL,
  `sesi_ke` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_catatan_kapasitas` (`id_kapasitas`),
  KEY `fk_catatan_tindakan` (`id_tindakan`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prm_catatan`
--

LOCK TABLES `prm_catatan` WRITE;
/*!40000 ALTER TABLE `prm_catatan` DISABLE KEYS */;
/*!40000 ALTER TABLE `prm_catatan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prm_kapasitas`
--

DROP TABLE IF EXISTS `prm_kapasitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prm_kapasitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_erm` varchar(50) NOT NULL,
  `nomor_register` varchar(50) NOT NULL,
  `id_paket` int(11) NOT NULL,
  `sisa` int(11) NOT NULL,
  `tanggal_beli` datetime NOT NULL,
  `tanggal_expired` datetime NOT NULL,
  `status` enum('AKTIF','HABIS','EXPIRED') NOT NULL DEFAULT 'AKTIF',
  PRIMARY KEY (`id`),
  KEY `fk_kapasitas_paket` (`id_paket`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prm_kapasitas`
--

LOCK TABLES `prm_kapasitas` WRITE;
/*!40000 ALTER TABLE `prm_kapasitas` DISABLE KEYS */;
/*!40000 ALTER TABLE `prm_kapasitas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prm_master_paket`
--

DROP TABLE IF EXISTS `prm_master_paket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prm_master_paket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `tipe_paket` enum('SINGLE','MULTI') NOT NULL DEFAULT 'SINGLE',
  `total_sesi` int(11) NOT NULL,
  `masa_berlaku_hari` int(11) NOT NULL DEFAULT '30',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prm_master_paket`
--

LOCK TABLES `prm_master_paket` WRITE;
/*!40000 ALTER TABLE `prm_master_paket` DISABLE KEYS */;
/*!40000 ALTER TABLE `prm_master_paket` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prm_master_tindakan`
--

DROP TABLE IF EXISTS `prm_master_tindakan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prm_master_tindakan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_tindakan` varchar(20) NOT NULL,
  `nama_tindakan` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prm_master_tindakan`
--

LOCK TABLES `prm_master_tindakan` WRITE;
/*!40000 ALTER TABLE `prm_master_tindakan` DISABLE KEYS */;
/*!40000 ALTER TABLE `prm_master_tindakan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'prm_rkz'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-06 11:50:38

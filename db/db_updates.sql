-- Tabel untuk Users
CREATE TABLE IF NOT EXISTS `prm_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin', 'kasir', 'manajemen', 'erm') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Insert Default Users (Password untuk semuanya adalah: 'rkz123')
-- Menggunakan fungsi MD5() karena PHP 5.4.2 mungkin tidak memiliki password_hash() secara default, 
-- namun praktek terbaik adalah bcrypt. Kita gunakan md5(password) untuk kompatibilitas jika bcrypt tidak ada, 
-- namun di PHP 5.5+ password_hash() disarankan. Kita akan memakai md5() sederhana demi keamanan legacy, 
-- ATAU SHA256. Kita akan asumsikan di PHP kita pakai md5.
-- Hash MD5 dari 'rkz123' adalah 'f671dbdd76a91d17d5fb6d3330cb1056'

INSERT IGNORE INTO `prm_users` (`username`, `password_hash`, `nama_lengkap`, `role`) VALUES
('admin', 'f671dbdd76a91d17d5fb6d3330cb1056', 'Administrator', 'admin'),
('kasir1', 'f671dbdd76a91d17d5fb6d3330cb1056', 'Kasir Utama', 'kasir'),
('manajemen1', 'f671dbdd76a91d17d5fb6d3330cb1056', 'Manajer Operasional', 'manajemen'),
('perawat1', 'f671dbdd76a91d17d5fb6d3330cb1056', 'Perawat Rehab Medis', 'erm');
